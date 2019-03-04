<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Traits\BaseFieldsTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use AppBundle\DBAL\ChargingStatusEnum;
use AppBundle\DBAL\BookingStatusEnum;

/**
 * @ORM\Entity
 * @ORM\Table(name="booking")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\BookingRepository")
 */
class Booking implements BaseFieldsI
{
    use BaseFieldsTrait {
        BaseFieldsTrait::__construct as private __bfConstruct;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     */
    private $customer;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="performer_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     */
    private $performer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="event_date", type="datetime", nullable=false)
     */
    private $eventDate;

    /**
     * @var string
     *
     * @ORM\Column(name="charge_id", type="string", length=255, nullable=true)
     */
    private $chargeId;

    /**
     * @var string
     *
     * @ORM\Column(name="transfer_id", type="string", length=255, nullable=true)
     */
    private $transferId;

    /**
     * @var string
     *
     * @ORM\Column(name="booking_status", type="booking_status_enum", nullable=false)
     */
    private $bookingStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="charge_status", type="charging_status_enum", nullable=true)
     */
    private $chargeStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="charge_try", type="integer")
     */
    private $chargeTry;

    /**
     * @var bool
     *
     * @ORM\Column(name="auto_reject", type="boolean", nullable=true, options={"default" : false})
     */
    private $autoReject;

    public function __construct()
    {
        $this->__bfConstruct();
        $this->chargeTry = 0;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set eventDate
     *
     * @param \DateTime $eventDate
     *
     * @return Booking
     */
    public function setEventDate($eventDate)
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * Get eventDate
     *
     * @return \DateTime
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * Set chargeId
     *
     * @param string $chargeId
     *
     * @return Booking
     */
    public function setChargeId($chargeId)
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    /**
     * Get chargeId
     *
     * @return string
     */
    public function getChargeId()
    {
        return $this->chargeId;
    }

    /**
     * Set transferId
     *
     * @param string $transferId
     *
     * @return Booking
     */
    public function setTransferId($transferId)
    {
        $this->transferId = $transferId;

        return $this;
    }

    /**
     * Get transferId
     *
     * @return string
     */
    public function getTransferId()
    {
        return $this->transferId;
    }

    /**
     * Set bookingStatus
     *
     * @param $bookingStatus
     *
     * @return Booking
     */
    public function setBookingStatus($bookingStatus)
    {
        $this->bookingStatus = $bookingStatus;

        return $this;
    }

    /**
     * Get bookingStatus
     *
     * @return
     */
    public function getBookingStatus()
    {
        return $this->bookingStatus;
    }

    /**
     * Set chargeStatus
     *
     * @param $chargeStatus
     *
     * @return Booking
     */
    public function setChargeStatus($chargeStatus)
    {
        $this->chargeStatus = $chargeStatus;

        return $this;
    }

    /**
     * Get chargeStatus
     *
     * @return
     */
    public function getChargeStatus()
    {
        return $this->chargeStatus;
    }

    /**
     * Set customer
     *
     * @param \AppBundle\Entity\User $customer
     *
     * @return Booking
     */
    public function setCustomer(\AppBundle\Entity\User $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \AppBundle\Entity\User
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set performer
     *
     * @param \AppBundle\Entity\User $performer
     *
     * @return Booking
     */
    public function setPerformer(\AppBundle\Entity\User $performer)
    {
        $this->performer = $performer;

        return $this;
    }

    /**
     * Get performer
     *
     * @return \AppBundle\Entity\User
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Booking
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Booking
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set chargeTry
     *
     * @param integer $chargeTry
     *
     * @return Booking
     */
    public function setChargeTry($chargeTry)
    {
        $this->chargeTry = $chargeTry;

        return $this;
    }

    /**
     * Get chargeTry
     *
     * @return integer
     */
    public function getChargeTry()
    {
        return $this->chargeTry;
    }

    /**
     * Set autoReject
     *
     * @param boolean $autoReject
     *
     * @return Booking
     */
    public function setAutoReject($autoReject)
    {
        $this->autoReject = $autoReject;

        return $this;
    }

    /**
     * Get autoReject
     *
     * @return boolean
     */
    public function getAutoReject()
    {
        return $this->autoReject;
    }
}
