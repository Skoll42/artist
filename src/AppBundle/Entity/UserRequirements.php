<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Traits\BaseFieldsTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_requirements")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRequirementsRepository")
 */
class UserRequirements implements BaseFieldsI
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
     * @var \AppBundle\Entity\Requirement
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Requirement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="requirement_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     */
    private $requirement;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

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
     * Set user
     *
     * @param User $user
     *
     * @return UserRequirements
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set requirement
     *
     * @param Requirement $requirement
     *
     * @return UserRequirements
     */
    public function setRequirement(Requirement $requirement)
    {
        $this->requirement = $requirement;

        return $this;
    }

    /**
     * Get requirement
     *
     * @return Requirement
     */
    public function getRequirement()
    {
        return $this->requirement;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return UserRequirements
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
