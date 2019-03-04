<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 12/4/2018
 * Time: 4:33 PM
 */

declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Traits\BaseFieldsTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_busydates")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserBusyDatesRepository")
 */
class UserBusyDates implements BaseFieldsI
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
     * @var \Date
     *
     * @ORM\Column(name="busy_date", type="date", nullable=false)
     */
    private $busyDate;

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
     * @return UserBusyDates
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
     * @param $busyDate
     *
     * @return UserBusyDates
     */
    public function setBusyDate($busyDate)
    {
        $this->busyDate = $busyDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBusyDate()
    {
        return $this->busyDate;
    }
}
