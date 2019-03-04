<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Traits\BaseFieldsTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_themes")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserThemesRepository")
 */
class UserThemes implements BaseFieldsI
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
     * @var \AppBundle\Entity\Theme
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Theme")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="theme_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     */
    private $theme;

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
     * @return UserThemes
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
     * Set theme
     *
     * @param Theme $theme
     *
     * @return UserThemes
     */
    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }
}
