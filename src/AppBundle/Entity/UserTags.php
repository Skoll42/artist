<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Traits\BaseFieldsTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_tags")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserTagsRepository")
 */
class UserTags implements BaseFieldsI
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
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     */
    private $user;

    /**
     * @var \AppBundle\Entity\Tag
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tag")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     */
    private $tag;

    public function __construct()
    {
        $this->__bfConstruct();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return UserTags
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * Set tag.
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return UserTags
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag.
     *
     * @return Tag
     */
    public function getTag() : Tag
    {
        return $this->tag;
    }
}
