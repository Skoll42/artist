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
 * @DI\Service(ServiceI::EVENT_SUBSCRIBER_CHARGE)
 */
class EventSubscriberCharge
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
     * @return bool
     */
    public function run($stripeCharge)
    {
        $this->io = new SymfonyStyle($this->input, $this->output);

        // Output start message into console
        $this->io->title('Start...');

        $events = $this->em->getRepository('AppBundle:Booking')->getCompletedEvents();

        if (empty($events)) {
            $this->io->success('No accepted bookings!');
            return false;
        }

        $iteratorArr = new \ArrayIterator($events);

        // Start progress
        $this->io->createProgressBar(iterator_count($iteratorArr));
        $this->io->progressStart(iterator_count($iteratorArr));
        foreach ($events as $event) {
            $charge = $stripeCharge->retrieve($event->getChargeId());

            if (!$charge) {
                // Progress iteration
                $this->io->progressAdvance();
                continue;
            }

            try {
                if ($event->getBookingStatus() !== BookingStatusEnum::VALUE_PENDING) {
                    $charge->capture();
                    $event->setChargeStatus(ChargingStatusEnum::VALUE_PENDING);
                    $this->sendNotification($event);
                } else {
                    $refund = $this->container->get('stripe.refund');
                    $refund->create($event->getChargeId());
                    $event->setChargeStatus(ChargingStatusEnum::VALUE_REFUNDED);
                }

                $event->setBookingStatus(BookingStatusEnum::VALUE_ARCHIVED);

                $this->em->persist($event);
                $this->em->flush();
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

    private function sendNotification($event)
    {
        $artist = $this->em->getRepository("AppBundle:ArtistData")->findOneBy([
            'user' => $event->getPerformer(),
            'deleted' => false
        ]);
        $customer = $this->em->getRepository("AppBundle:UserData")->findOneBy([
            'user' => $event->getCustomer(),
            'deleted' => false
        ]);

        $this->sendNotificationToCustomer($event, $artist, $customer);
        $this->sendNotificationToPerformer($event, $artist, $customer);
    }

    private function sendNotificationToCustomer($event, $artist, $customer)
    {
        try {
            $subject = 'How was your event with ' . $artist->getName() . ' on ' . $event->getEventDate()->format('Y-m-d H:i') . '?';
            $emailFrom =  $this->container->getParameter(
                $this->container->getParameter('kernel.environment') . '_mailer_address'
            );
            $emailTo = $event->getCustomer()->getEmail();

            $sender = $this->container->get('email_sender');
            $sender->sendEmail(
                $emailFrom,
                $emailTo,
                $subject,
                '@App/emails/event_end_customer.html.twig',
                [
                    'artistName' => $artist->getName(),
                    'customerName' => $customer->getFirstName() && $customer->getLastName() ?
                        $customer->getFirstName() . ' ' . $customer->getLastName() :
                        $customer->getUser()->getUsername()
                ]
            );
        } catch (\Exception $e) {
            $this->io->error($e->getTraceAsString());
        }
    }

    private function sendNotificationToPerformer($event, $artist, $customer)
    {
        $customerName = $customer->getFirstName() && $customer->getLastName() ? $customer->getFirstName() . ' ' . $customer->getLastName() : $customer->getUser()->getUsername();

        try {
            $subject = 'Your performance for ' . $customerName . ' Event on ' . $event->getEventDate()->format('Y-m-d H:i') . ' is done';
            $emailFrom =  $this->container->getParameter(
                $this->container->getParameter('kernel.environment') . '_mailer_address'
            );
            $emailTo = $event->getPerformer()->getEmail();

            $sender = $this->container->get('email_sender');
            $sender->sendEmail(
                $emailFrom,
                $emailTo,
                $subject,
                '@App/emails/event_end_artist.html.twig',
                [
                    'date' => $event->getEventDate(),
                    'artistName' => $artist->getName(),
                    'customerName' => $customerName,
                ]
            );
        } catch (\Exception $e) {
            $this->io->error($e->getTraceAsString());
        }
    }
}
