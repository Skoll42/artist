<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\DBAL\BookingStatusEnum;
use AppBundle\DBAL\ChargingStatusEnum;
use AppBundle\Entity\Booking;
use AppBundle\Entity\User;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Charge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/book")
 */
class PaymentController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/artist", name="book_artist")
     * @Method({"GET", "POST"})
     */
    public function bookAction(Request $r, $id)
    {
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);
        if (!$artist) {
            return new JsonResponse($this->prepareJsonArr(false, 'Artist not found', []));
        }

        $customer = $this->getRepo("UserData")->findOneBy(['user' => $this->getUser()]);
        if (!$customer) {
            return new JsonResponse($this->prepareJsonArr(false, 'User not found', []));
        }


        if ($r->getMethod() == "POST" && $r->request->get('stripeToken')) {
            if (empty($artist->getStripeId())) {
                return new JsonResponse($this->prepareJsonArr(false, "Artist can't accept payments", []));
            }

            $account = $this->checkArtistStripeAccount($artist);
            if (!$account) {
                return new JsonResponse($this->prepareJsonArr(false, "Artist can't accept payments", []));
            }

            $customerStripe = $this->getCustomerFromStripe($r->request->get('stripeToken'), $customer);

            if ($customerStripe['success']) {
                $em = $this->getDoctrine()->getManager();
                $customer->setStripeId($customerStripe['data']['id']);
                $em->persist($customer);
                $em->flush();
            } else {
                return new JsonResponse($this->prepareJsonArr(
                    $customerStripe['success'],
                    $customerStripe['errors']['message'],
                    ['errors' => $customerStripe['errors']]
                ));
            }

            $date = $r->request->get('artist-book-date');
            $time = $r->request->get('appbundle_artist')['time'];

            // TODO: Remove after communication implements
            $address = $r->request->get('artist-book-address');
            $comment = $r->request->get('artist-book-comment');
            $charge = [];

            $diffDate = (date_diff((new \DateTime()), (new \DateTime($date . ' ' . $time))))->days;
            if ($diffDate <= 7) {
                $price = (float)$r->request->get('price') * 100;
                $artistPrice = $artist->getPrice() * 100 * (1 - $this->getParameter('platform_fee'));
                $charge = $this->createChargeWithoutCapture(
                    $price,
                    $customerStripe['data'],
                    $artistPrice,
                    $artist->getStripeId()
                );
                if (!$charge['success']) {
                    return new JsonResponse($this->prepareJsonArr(
                        $charge['success'],
                        $charge['errors'],
                        ['errors' => $charge['errors']]
                    ));
                }
            }

            $em = $this->getDoctrine()->getManager();
            $booking = $this->createBooking(
                $this->getUser(),
                $artist->getUser(),
                '',
                $date,
                $time,
                $address,
                $comment
            );
            $em->persist($booking);
            $em->flush();

            if ($diffDate <= 7) {
                $booking->setChargeStatus(ChargingStatusEnum::VALUE_PENDING);
                $booking->setChargeTry(0);
                $booking->setChargeId($charge['data']->id);
                $em->persist($booking);
                $em->flush();
            }

            return  new JsonResponse($this->prepareJsonArr(true, 'Book successful', array('hidden' => true)));
        }

        $html = $this->renderView('@App/modals/book_now.html.twig', ['artist' => $artist]);

        return new JsonResponse($this->prepareJsonArr(true, '', array('html' => $html)));
    }

    private function checkArtistStripeAccount($artist)
    {
        $stripeAccount = $this->get('stripe.account');
        return $stripeAccount->retrieve($artist->getStripeId());
    }

    private function getCustomerFromStripe($token, $customer)
    {
        $customerStripe = $this->checkCustomerStripeAccount($customer);
        if ($customerStripe) {
            $customerStripe = $this->updateCustomerStripeAccount($customerStripe, $token);
        } else {
            $customerStripe = $this->createCustomerStripeAccount($token, $customer);
        }

        return $customerStripe;
    }

    private function checkCustomerStripeAccount($customer)
    {
        $stripeCustomer = $this->get('stripe.customer');

        return $customer->getStripeId() ? $stripeCustomer->retrieve($customer->getStripeId()) : null;
    }

    private function updateCustomerStripeAccount($customer, $token)
    {
        $stripeCustomer = $this->get('stripe.customer');

        return $stripeCustomer->update($customer, $token);
    }

    private function createCustomerStripeAccount($token, $customer)
    {
        $stripeCustomer = $this->get('stripe.customer');
        $username = $customer->getUser()->getUsername();

        if (!empty($customer->getFirstName()) && !empty($customer->getLastName())) {
            $username = $customer->getFirstName() . ' ' . $customer->getLastName();
        }

        return $stripeCustomer->create(
            $customer->getUser()->getEmail(),
            $token,
            $username,
            'Payment card',
            $customer->getUser()->getCreatedDate()
        );
    }

    private function createChargeWithoutCapture($price, $customer, $artistPrice, $stripeId, $currency = 'nok')
    {
        $description = 'Authorisation ' . $this->getUser()->getEmail() . ' payment card';
        $stripeCharge = $this->get('stripe.charge');

        return $stripeCharge->create($price, $customer, $artistPrice, $stripeId, $description, $currency, false);
    }

    /**
     * Create new booking object
     *
     * @param User $customer
     * @param User $performer
     * @param string $chargeId
     * @param $date
     * @param $time
     * @param $address
     * @param $comment
     * @return Booking
     */
    private function createBooking(
        User $customer,
        User $performer,
        string $chargeId,
        $date,
        $time,
        $address,
        $comment
    ) {
        $booking = (new Booking())
            ->setCustomer($customer)
            ->setPerformer($performer)
            ->setEventDate(new \DateTime($date . ' ' . $time))
            ->setChargeId($chargeId)
            ->setBookingStatus(BookingStatusEnum::VALUE_PENDING)
            ->setChargeStatus(ChargingStatusEnum::VALUE_RESERVED)
            ->setAddress($address)
            ->setComment($comment)
        ;

        return $booking;
    }
}
