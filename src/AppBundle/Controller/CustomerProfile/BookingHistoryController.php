<?php
declare(strict_types=1);

namespace AppBundle\Controller\CustomerProfile;

use AppBundle\DBAL\BookingStatusEnum;
use AppBundle\DBAL\ChargingStatusEnum;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/profile/customer")
 * @Security("has_role('ROLE_CUSTOMER')")
 */
class BookingHistoryController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/edit/booking/all", name="customer_edit_booking_all")
     * @Security("user.getID() == id")
     */
    public function editBookingAction(Request $r, $id)
    {
        $customer = $this->getRepo("UserData")->findOneBy(['user' => $id]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        $bookings = $this->getRepo('Booking')->getAllByCustomer($customer->getUser());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $bookings,
            $r->query->get('page', 1)/*page number*/,
            $this->getParameter('pagination_booking_count')/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/customer/booking_history/all_booking.html.twig', [
            'bookings' => $pagination,
            'customer' => $customer
        ]);
    }

    /**
     * @Route("/{id}/edit/booking/accepted", name="customer_edit_booking_accepted")
     */
    public function editBookingAcceptedAction(Request $r, $id)
    {
        $customer = $this->getRepo("UserData")->findOneBy(['user' => $id]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        $bookings = $this->getRepo('Booking')->getAllAcceptedByCustomer($customer->getUser());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $bookings,
            $r->query->get('page', 1)/*page number*/,
            $this->getParameter('pagination_booking_count')/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/customer/booking_history/accepted_booking.html.twig', [
            'bookings' => $pagination,
            'customer' => $customer
        ]);
    }

    /**
     * @Route("/{id}/edit/booking/archived", name="customer_edit_booking_archived")
     */
    public function editBookingArchivedAction(Request $r, $id)
    {
        $customer = $this->getRepo("UserData")->findOneBy(['user' => $id]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        $bookings = $this->getRepo('Booking')->getArchivedByCustomer($customer->getUser());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $bookings,
            $r->query->get('page', 1)/*page number*/,
            $this->getParameter('pagination_booking_count')/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/customer/booking_history/archived_booking.html.twig', [
            'bookings' => $pagination,
            'customer' => $customer
        ]);
    }

    /**
     * @Route("/{id}/cancel/booking/modal", name="customer_cancel_booking_modal")
     */
    public function cancelBookingModalAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $date = $booking->getEventDate()->getTimeStamp();
        $html = '';

        $diffDate = (date_diff((new \DateTime()), $booking->getEventDate()))->days;
        if ($diffDate > 30) {
            $html = $this->renderView('@App/modals/cancel_booking.html.twig', [
                'id' => $id,
                'text' => 'Are you sure you want to cancel this event?',
                'role' => 'customer'
            ]);
            return new JsonResponse($this->prepareJsonArr(true, 'Confirm cancel !', ['html' => $html]));
        }

        if ($diffDate <= 7) {
            $html = $this->renderView('@App/modals/cancel_booking.html.twig', [
                'id' => $id,
                'text' => 'Are you sure you want to cancel the booking? 100% of the price will be charged from your card in this case.',
                'role' => 'customer'
            ]);
        } elseif ($diffDate <= 15) {
            $html = $this->renderView('@App/modals/cancel_booking.html.twig', [
                'id' => $id,
                'text' => 'Are you sure you want to cancel the booking? 50% of the price will be charged from your card in this case.',
                'role' => 'customer'
            ]);
        } else {
            $html = $this->renderView('@App/modals/cancel_booking.html.twig', [
                'id' => $id,
                'text' => 'Are you sure you want to cancel the booking? 25% of the price will be charged from your card in this case.',
                'role' => 'customer'
            ]);
        }

        return new JsonResponse($this->prepareJsonArr(true, 'Confirm cancel !', ['html' => $html]));
    }

    /**
     * @Route("/{id}/cancel/booking", name="customer_cancel_booking")
     */
    public function cancelBookingAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $diffDate = (date_diff((new \DateTime()), $booking->getEventDate()))->days;
        $em = $this->getDoctrine()->getManager();

        if ($diffDate > 30) {
            $booking->setBookingStatus(BookingStatusEnum::VALUE_CANCELED);
            $em->persist($booking);
            $em->flush();
            return new JsonResponse($this->prepareJsonArr(true, 'Booking canceled !'));
        }

        $artist = $this->getRepo('ArtistData')->findOneBy([
            'user' => $booking->getPerformer()
        ]);
        $price = $artist->getPrice() * 100 * (1 + $this->getParameter('platform_fee'));
        $artistPrice = $artist->getPrice() * 100 * (1 - $this->getParameter('platform_fee'));

        $customer = $this->getRepo('UserData')->findOneBy([
            'user' => $booking->getCustomer()
        ]);
        $customerStripe = $this->get('stripe.customer');
        $customer = $customerStripe->retrieve($customer->getStripeId());

        if ($diffDate <= 7) {
            if ($booking->getChargeId()) {
                $stripeCharge = $this->get('stripe.charge');
                $charge = $stripeCharge->retrieve($booking->getChargeId());
                $charge->capture();
            } else {
                $this->createChargeWithCapture(
                    $booking,
                    $price,
                    $customer,
                    $artistPrice,
                    $artist->getStripeId()
                );
            }
        } elseif ($diffDate <= 15) {
            $price = $price * 0.5;
            $artistPrice = $artistPrice * 0.5;
            $this->createChargeWithCapture(
                $booking,
                $price,
                $customer,
                $artistPrice,
                $artist->getStripeId()
            );
        } else {
            $price = $price * 0.25;
            $artistPrice = $artistPrice * 0.25;
            $this->createChargeWithCapture(
                $booking,
                $price,
                $customer,
                $artistPrice,
                $artist->getStripeId()
            );
        }

        $booking->setBookingStatus(BookingStatusEnum::VALUE_CANCELED);
        $em->persist($booking);
        $em->flush();
        return new JsonResponse($this->prepareJsonArr(true, 'Booking canceled !'));
    }

    private function createChargeWithCapture($booking, $price, $customer, $artistPrice, $stripeId, $currency = 'nok')
    {
        $description = 'Charge ' . $this->getUser()->getEmail() . ' payment card after canceled';
        $stripeCharge = $this->get('stripe.charge');

        $charge = $stripeCharge->create($price, $customer, $artistPrice, $stripeId, $description, $currency, true);
        $booking->setChargeStatus(ChargingStatusEnum::VALUE_PENDING);

        if ($charge['success']) {
            $em = $this->getDoctrine()->getManager();
            $booking->setChargeId($charge['data']->id);
            $em->persist($booking);
        }

        return $charge;
    }
}
