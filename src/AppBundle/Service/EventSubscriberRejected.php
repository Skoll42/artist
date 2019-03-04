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
 * @DI\Service(ServiceI::EVENT_SUBSCRIBER_REJECTED)
 */
class EventSubscriberRejected
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
     * @return bool
     */
    public function run()
    {
        $this->io = new SymfonyStyle($this->input, $this->output);

        // Output start message into console
        $this->io->title('Start...');

        $events = $this->em->getRepository('AppBundle:Booking')->getPendingEvents();

        if (empty($events)) {
            $this->io->success('No pending bookings!');
            return false;
        }

        $iteratorArr = new \ArrayIterator($events);

        // Start progress
        $this->io->createProgressBar(iterator_count($iteratorArr));
        $this->io->progressStart(iterator_count($iteratorArr));
        foreach ($events as $event) {
            try {
                $eventEndDate = date_diff($event->getCreatedDate(), $event->getEventDate());
                $dDiff = date_diff((new \DateTime()), $event->getCreatedDate());

                if ($eventEndDate->days > 30) {
                    if ($dDiff->h >= 12) {
                        $event->setBookingStatus(BookingStatusEnum::VALUE_REJECTED);
                        $event->setAutoReject(true);
                        $this->em->persist($event);
                        $this->em->flush();

                        $this->sendNotification($event);
                    }
                } elseif ($eventEndDate->days < 30) {
                    if ($dDiff->h  >= 3) {
                        $event->setBookingStatus(BookingStatusEnum::VALUE_REJECTED);
                        $event->setAutoReject(true);
                        $this->em->persist($event);
                        $this->em->flush();

                        $this->sendNotification($event);
                    }
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
        $customerName = $customer->getFirstName() && $customer->getLastName() ? $customer->getFirstName() . ' ' . $customer->getLastName() : $customer->getUser()->getUsername();

        try {
            $subject = 'The Event is automatically rejected';
            $emailFrom =  $this->container->getParameter(
                $this->container->getParameter('kernel.environment') . '_mailer_address'
            );
            $emailTo = $customer->getUser()->getEmail();

            $sender = $this->container->get('email_sender');
            $sender->sendEmail(
                $emailFrom,
                $emailTo,
                $subject,
                '@App/emails/event_auto_reject_customer.html.twig',
                [
                    'customerName' => $customerName,
                    'artistName' => $artist->getName(),
                    'date' => $event->getEventDate()->format('Y-m-d H:i')
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
            $subject = 'The Event is automatically rejected';
            $emailFrom =  $this->container->getParameter(
                $this->container->getParameter('kernel.environment') . '_mailer_address'
            );
            $emailTo = $artist->getUser()->getEmail();

            $sender = $this->container->get('email_sender');
            $sender->sendEmail(
                $emailFrom,
                $emailTo,
                $subject,
                '@App/emails/event_auto_reject_artist.html.twig',
                [
                    'customerName' => $customerName,
                    'artistName' => $artist->getName(),
                    'date' => $event->getEventDate()->format('Y-m-d H:i')
                ]
            );
        } catch (\Exception $e) {
            $this->io->error($e->getTraceAsString());
        }
    }
}
