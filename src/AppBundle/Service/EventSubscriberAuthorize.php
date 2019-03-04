<?php

declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\DBAL\BookingStatusEnum;
use AppBundle\DBAL\ChargingStatusEnum;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(ServiceI::EVENT_SUBSCRIBER_AUTHORIZE)
 */
class EventSubscriberAuthorize
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $input;

    /**
     * @var string
     */
    private $output;

    /**
     * @var SymfonyStyle
     */
    private $io;

    private $artist;

    private $customer;

    private $stripeCharge;

    /**
     * @param $output
     *
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }
    /**
     * @param $input
     *
     * @return $this
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * EventSubscriberCharge constructor.
     *
     * @param EntityManager $em
     * @param ContainerInterface $container
     *
     * @DI\InjectParams({
     *     "em"    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "container"    = @DI\Inject("service_container")
     * })
     */
    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $em;
    }

    /**
     * @param $stripeCharge
     * @param $customerStripe
     * @return bool
     */
    public function run($stripeCharge, $customerStripe)
    {
        $this->stripeCharge = $stripeCharge;
        $this->io = new SymfonyStyle($this->input, $this->output);

        // Output start message into console
        $this->io->title('Start...');

        $events = $this->em->getRepository('AppBundle:Booking')->getConfirmedEvents();

        if (empty($events)) {
            $this->io->success('No accepted bookings!');
            return false;
        }

        $iteratorArr = new \ArrayIterator($events);

        // Start progress
        $this->io->createProgressBar(iterator_count($iteratorArr));
        $this->io->progressStart(iterator_count($iteratorArr));
        foreach ($events as $event) {
            try {
                $diffDate = (date_diff((new \DateTime()), $event->getEventDate()))->days;

                if ($diffDate <= 7) {
                    $this->artist = $this->em->getRepository("AppBundle:ArtistData")->findOneBy([
                        'user' => $event->getPerformer(),
                        'deleted' => false
                    ]);
                    $this->customer = $this->em->getRepository("AppBundle:UserData")->findOneBy([
                        'user' => $event->getCustomer(),
                        'deleted' => false
                    ]);

                    $customerStripe = $customerStripe->retrieve($this->customer->getStripeId());
                    $price = $this->artist->getPrice() * 100 * (1 + $this->container->getParameter('platform_fee'));
                    $artistPrice = $this->artist->getPrice() * 100 * (1 - $this->container->getParameter('platform_fee'));

                    $this->createChargeWithoutCapture(
                        $event,
                        $price,
                        $customerStripe,
                        $artistPrice,
                        $this->artist->getStripeId()
                    );
                }
            } catch (\Exception $e) {
                $this->io->error($e->getTraceAsString());
            }

            // Progress iteration
            $this->io->progressAdvance();
        }

        // End progress
        $this->io->progressFinish();

        // Success message
        $this->io->success('Everything went well!');

        return true;
    }

    private function createChargeWithoutCapture($booking, $price, $customer, $artistPrice, $stripeId, $currency = 'nok')
    {
        $description = 'Authorize ' . $this->customer->getUser()->getEmail() . ' payment card before event';

        $charge = $this->stripeCharge->create(
            $price,
            $customer,
            $artistPrice,
            $stripeId,
            $description,
            $currency,
            false
        );

        if ($charge['success']) {
            $booking->setChargeTry(0);
            $booking->setChargeStatus(ChargingStatusEnum::VALUE_PENDING);
            $booking->setChargeId($charge['data']->id);
            $this->em->persist($booking);
            $this->em->flush();
        } else {
            if ($booking->getChargeTry() < 2) {
                $booking->setChargeTry($booking->getChargeTry() + 1);
                $this->sendNotificationToCustomer($booking, $price);
                $this->em->persist($booking);
                $this->em->flush();
            } else {
                $booking->setChargeTry($booking->getChargeTry() + 1);
                $booking->setBookingStatus(BookingStatusEnum::VALUE_CANCELED);
                $this->sendNotificationCanceledToPerformer($booking);
                $this->sendNotificationCanceledToCustomer($booking, $price);
                $this->em->persist($booking);
                $this->em->flush();
            }
        }

        return $charge;
    }

    private function sendNotificationToCustomer($event, $price)
    {
        $customerName = $this->customer->getFirstName() && $this->customer->getLastName() ?
            $this->customer->getFirstName() . ' ' . $this->customer->getLastName() :
            $this->customer->getUser()->getUsername();
        try {
            $subject = 'Your card balance is not enough';
            $emailFrom =  $this->container->getParameter(
                $this->container->getParameter('kernel.environment') . '_mailer_address'
            );
            $emailTo = $event->getCustomer()->getEmail();

            $sender = $this->container->get('email_sender');
            $sender->sendEmail(
                $emailFrom,
                $emailTo,
                $subject,
                '@App/emails/event_reminder_money_not_enough.html.twig',
                [
                    'artistName' => $this->artist->getName(),
                    'date' => $event->getEventDate()->format('Y-m-d H:i'),
                    'price' => $price,
                    'customerName' => $customerName
                ]
            );
        } catch (\Exception $e) {
            $this->io->error($e->getTraceAsString());
        }
    }

    private function sendNotificationCanceledToCustomer($event, $price)
    {
        $customerName = $this->customer->getFirstName() && $this->customer->getLastName() ?
            $this->customer->getFirstName() . ' ' . $this->customer->getLastName() :
            $this->customer->getUser()->getUsername();
        $date = $event->getEventDate()->format('Y-m-d H:i');
        try {
            $subject = 'Your event with ' . $this->artist->getName() . ' on ' . $date . ' is cancelled, your card balance was not enough';
            $emailFrom =  $this->container->getParameter(
                $this->container->getParameter('kernel.environment') . '_mailer_address'
            );
            $emailTo = $event->getCustomer()->getEmail();

            $sender = $this->container->get('email_sender');
            $sender->sendEmail(
                $emailFrom,
                $emailTo,
                $subject,
                '@App/emails/event_reminder_money_not_enough_canceled.html.twig',
                [
                    'artistName' => $this->artist->getName(),
                    'date' => $date,
                    'price' => $price,
                    'customerName' => $customerName
                ]
            );
        } catch (\Exception $e) {
            $this->io->error($e->getTraceAsString());
        }
    }

    private function sendNotificationCanceledToPerformer($event)
    {
        $customerName = $this->customer->getFirstName() && $this->customer->getLastName() ?
            $this->customer->getFirstName() . ' ' . $this->customer->getLastName() :
            $this->customer->getUser()->getUsername();
        $date = $event->getEventDate()->format('Y-m-d H:i');

        try {
            $subject = 'The Event with ' . $customerName . ' on ' . $date . ' is cancelled';
            $emailFrom =  $this->container->getParameter(
                $this->container->getParameter('kernel.environment') . '_mailer_address'
            );
            $emailTo = $event->getPerformer()->getEmail();

            $sender = $this->container->get('email_sender');
            $sender->sendEmail(
                $emailFrom,
                $emailTo,
                $subject,
                '@App/emails/booking_cancelled_by_customer.html.twig',
                [
                    'date' => $event->getEventDate()->format("Y-m-d H:i"),
                    'artistName' => $this->artist->getName(),
                    'customerName' => $customerName,
                ]
            );
        } catch (\Exception $e) {
            $this->io->error($e->getTraceAsString());
        }
    }
}
