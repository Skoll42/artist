<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Service\ServiceI;
use AppBundle\Traits\BaseFieldsTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\TagRepository")
 */
class Tag implements BaseFieldsI
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer", nullable=true)
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    public function __construct()
    {
        $this->__bfConstruct();
        $this->weight = 0;
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
     * Set name
     *
     * @param string $name
     *
     * @return Tag
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     *
     * @return Tag
     */
    public function setWeight(int $weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Tag
     */
    public function setDescription(string $description)
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

    /**
     * Increase Tag weight
     *
     * @return Tag
     */
    public function increaseWeight()
    {
        $this->setWeight($this->getWeight() + 1);

        return $this;
    }

    /**
     * Decrease Tag weight
     *
     * @return Tag
     */
    public function decreaseWeight()
    {
        $this->setWeight($this->getWeight() - 1);

        return $this;
    }

    /**
     * @return object
     */
    function getService()
    {
        global $kernel;

        return $kernel->getContainer()->get(ServiceI::TEXT_MANIPULATION);
    }

    public function __toString()
    {
        return $this->name;
    }
}
