<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 18.09.2018
 * Time: 15:54
 */
declare(strict_types=1);

namespace ChatBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Messages
 *
 * @MongoDB\HasLifecycleCallbacks()
 * @MongoDB\Document(
 *      collection="messages",
 *      repositoryClass="ChatBundle\Document\Repository\MessagesRepository"
 * )
 */
class Messages
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $chatName;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $senderId;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $senderName;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $senderImage;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $targetId;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $targetName;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $targetImage;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $body;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $status;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $created_at;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $updated_at;

    /**
     * @MongoDB\Field(type="boolean")
     */
    protected $isSend;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set chatName
     *
     * @param string $chatName
     * @return $this
     */
    public function setChatName($chatName)
    {
        $this->chatName = $chatName;
        return $this;
    }

    /**
     * Get chatName
     *
     * @return string $chatName
     */
    public function getChatName()
    {
        return $this->chatName;
    }

    /**
     * Set senderId
     *
     * @param string $senderId
     * @return $this
     */
    public function setSenderId($senderId)
    {
        $this->senderId = $senderId;
        return $this;
    }

    /**
     * Get senderId
     *
     * @return string $senderId
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * Set senderName
     *
     * @param string $senderName
     * @return $this
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
        return $this;
    }

    /**
     * Get senderName
     *
     * @return string $senderName
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * Set senderImage
     *
     * @param string $senderImage
     * @return $this
     */
    public function setSenderImage($senderImage)
    {
        $this->senderImage = $senderImage;
        return $this;
    }

    /**
     * Get senderImage
     *
     * @return string $senderImage
     */
    public function getSenderImage()
    {
        return $this->senderImage;
    }

    /**
     * Set targetId
     *
     * @param string $targetId
     * @return $this
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * Get targetId
     *
     * @return string $targetId
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * Set targetName
     *
     * @param string $targetName
     * @return $this
     */
    public function setTargetName($targetName)
    {
        $this->targetName = $targetName;
        return $this;
    }

    /**
     * Get targetName
     *
     * @return string $targetName
     */
    public function getTargetName()
    {
        return $this->targetName;
    }

    /**
     * Set targetImage
     *
     * @param string $targetImage
     * @return $this
     */
    public function setTargetImage($targetImage)
    {
        $this->targetImage = $targetImage;
        return $this;
    }

    /**
     * Get targetImage
     *
     * @return string $targetImage
     */
    public function getTargetImage()
    {
        return $this->targetImage;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get body
     *
     * @return string $body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return date $createdAt
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updatedAt
     *
     * @param date $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return date $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set isSend
     *
     * @param boolean $isSend
     * @return $this
     */
    public function setIsSend($isSend)
    {
        $this->isSend = $isSend;
        return $this;
    }

    /**
     * Get isSend
     *
     * @return boolean isSend
     */
    public function getIsSend()
    {
        return $this->isSend;
    }
}
