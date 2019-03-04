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
 * Chat
 *
 * @MongoDB\HasLifecycleCallbacks()
 * @MongoDB\Document(
 *      collection="chat",
 *      repositoryClass="ChatBundle\Document\Repository\ChatRepository"
 * )
 */
class Chat
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $name;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $senderId;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $targetId;

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
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
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
}
