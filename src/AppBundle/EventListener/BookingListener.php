<?php
declare(strict_types=1);

namespace AppBundle\EventListener;

use AppBundle\DBAL\BookingStatusEnum;
use AppBundle\Entity\ArtistData;
use AppBundle\Entity\Booking;
use AppBundle\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BookingListener
{
    private $container;
    private $em = null;
    private $uow = null;
    private $entity;
    private $tokenStorage ;

    public function __construct(ContainerInterface $container, TokenStorage $tokenStorage)
    {
        $this->container = $container;
        $this->tokenStorage  = $tokenStorage;
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Booking) {
            $this->setPermanentData($entity);
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $args->getEntityManager();
        $eventManager = $this->em->getEventManager();
        $this->uow = $this->em->getUnitOfWork();
        $eventManager->removeEventListener('onFlush', $this);
        $user = null;
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
        }

        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Booking) {
                $this->entity = $entity;

                $this->changeBookingStatus(
                    'pending',
                    $entity->getPerformer(),
                    $entity->getCustomer(),
                    $user ? $user->hasRole("ROLE_CUSTOMER") : false
                );
            }
        }

        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Booking) {
                $this->entity = $entity;

                $changes = $this->uow->getEntityChangeSet($entity);

                $status = $changes['bookingStatus'][1] ?? null;
                $chargeStatus = $changes['chargeStatus'][1] ?? null;

                if (!is_null($status)) {
                    $this->changeBookingStatus(
                        $status,
                        $entity->getPerformer(),
                        $entity->getCustomer(),
                        $user ? $user->hasRole("ROLE_CUSTOMER") : false
                    );
                }

                if (!is_null($chargeStatus)) {
                    $this->changeChargeStatus(
                        $chargeStatus,
                        $entity->getPerformer(),
                        $entity->getCustomer()
                    );
                }
            }
        }

        $eventManager->addEventListener('onFlush', $this);
    }

    /**
     * Increase or decrease artist booking count when status changed or new booking created
     *
     * @param string $status
     * @param User $user
     * @param User $customer
     * @param boolean $fromCustomer
     */
    private function changeBookingStatus(string $status, User $user, User $customer, $fromCustomer = false)
    {
        $artist = $this->em->getRepository('AppBundle:ArtistData')->findOneBy([
            'user' => $user,
            'deleted' => false
        ]);
        $customer = $this->em->getRepository('AppBundle:UserData')->findOneBy([
            'user' => $customer,
            'deleted' => false
        ]);
        $sender = $this->container->get('email_sender');

        switch ($status) {
            case "pending":
                $this->increaseArtistBookingCount($artist);
                $sender->sendBookingRequest(
                    $artist,
                    $customer,
                    $this->entity,
                    $this->container->getParameter('kernel.environment')
                );
                break;
            case "confirmed":
                $this->increaseArtistBookingAcceptedCount($artist);
                $this->decreaseArtistBookingCount($artist);
                $sender->sendBookingConfirmed(
                    $artist,
                    $customer,
                    $this->entity,
                    $this->container->getParameter('kernel.environment')
                );
                break;
            case "rejected":
                $this->decreaseArtistBookingCount($artist);
                if (!$this->entity->getAutoReject()) {
                    $sender->sendBookingRejected(
                        $artist,
                        $customer,
                        $this->entity,
                        $this->container->getParameter('kernel.environment')
                    );
                }
                break;
            case "canceled":
                $this->decreaseArtistBookingAcceptedCount($artist);

                if ($this->entity->getChargeTry() == 0) {
                    if ($fromCustomer) {
                        $sender->sendBookingCanceledFromCustomer(
                            $artist,
                            $customer,
                            $this->entity,
                            $this->container->getParameter('kernel.environment')
                        );
                    } else {
                        $sender->sendBookingCanceledFromArtist(
                            $artist,
                            $customer,
                            $this->entity,
                            $this->container->getParameter('kernel.environment')
                        );
                    }
                }
                break;
            default:
                break;
        }

        $this->em->persist($artist);
        $this->em->flush();
    }

    /**
     * @param string $status
     * @param User $user
     * @param User $customer
     */
    private function changeChargeStatus(string $status, User $user, User $customer)
    {
        $artist = $this->em->getRepository('AppBundle:ArtistData')->findOneBy([
            'user' => $user,
            'deleted' => false
        ]);
        $customer = $this->em->getRepository('AppBundle:UserData')->findOneBy([
            'user' => $customer,
            'deleted' => false
        ]);
        $sender = $this->container->get('email_sender');

        switch ($status) {
            case "pending":
                if ($this->entity->getChargeTry() == 0 && $this->entity->getBookingStatus() != BookingStatusEnum::VALUE_CANCELED) {
                    $sender->sendChargePendingArtist(
                        $artist,
                        $customer,
                        $this->entity,
                        $this->container->getParameter('kernel.environment')
                    );
                    $sender->sendChargePendingCustomer(
                        $artist,
                        $customer,
                        $this->entity,
                        $this->container->getParameter('kernel.environment')
                    );
                }
                break;
            default:
                break;
        }
    }

    /**
     * Increase artist booking count when created new booking by Customer
     *
     * @param ArtistData $artist
     */
    private function increaseArtistBookingCount(ArtistData $artist)
    {
        $artist->setBookings($artist->getBookings() + 1);
    }

    /**
     * Decrease artist booking count in pending status when status changes to another
     *
     * @param ArtistData $artist
     */
    private function decreaseArtistBookingCount(ArtistData $artist)
    {
        $artist->setBookings($artist->getBookings() - 1);
    }

    /**
     * Increase artist accepted booking count when artist accepted it
     *
     * @param ArtistData $artist
     */
    private function increaseArtistBookingAcceptedCount(ArtistData $artist)
    {
        $artist->setAcceptedBookings($artist->getAcceptedBookings() + 1);
    }

    /**
     * Decrease artist accepted booking count when booking status changes to canceled
     *
     * @param ArtistData $artist
     */
    private function decreaseArtistBookingAcceptedCount(ArtistData $artist)
    {
        $artist->setAcceptedBookings($artist->getAcceptedBookings() - 1);
    }

    /**
     * Update date when entity some updated
     *
     * @param $entity
     */
    private function setPermanentData($entity)
    {
        $entity->setUpdatedDate(new \DateTime());
    }
}
