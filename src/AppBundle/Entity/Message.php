<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Traits\BaseFieldsTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="message")
 */
class Message implements BaseFieldsI
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
     *   @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     * })
     */
    private $senderId;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="receiver_id", referencedColumnName="id")
     * })
     */
    private $receiverId;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", length=65535, nullable=false)
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="message_status_enum", nullable=true)
     */
    private $status;

    public function __construct()
    {
        $this->__bfConstruct();
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
     * Set body
     *
     * @param string $body
     *
     * @return Message
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set status
     *
     * @param message_status_enum $status
     *
     * @return Message
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return message_status_enum
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set senderId
     *
     * @param \AppBundle\Entity\User $senderId
     *
     * @return Message
     */
    public function setSenderId(\AppBundle\Entity\User $senderId = null)
    {
        $this->senderId = $senderId;

        return $this;
    }

    /**
     * Get senderId
     *
     * @return \AppBundle\Entity\User
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * Set receiverId
     *
     * @param \AppBundle\Entity\User $receiverId
     *
     * @return Message
     */
    public function setReceiverId(\AppBundle\Entity\User $receiverId = null)
    {
        $this->receiverId = $receiverId;

        return $this;
    }

    /**
     * Get receiverId
     *
     * @return \AppBundle\Entity\User
     */
    public function getReceiverId()
    {
        return $this->receiverId;
    }
}
