<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Traits\BaseFieldsTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @ORM\Table(name="requirement")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\RequirementRepository")
 */
class Requirement implements BaseFieldsI
{
    private $em;

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
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer", nullable=true)
     */
    private $weight;

    public function __construct($em)
    {
        $this->__bfConstruct();
        $this->weight = 0;
        $this->em = $em;
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
     * Set weight
     *
     * @param integer $weight
     *
     * @return Requirement
     */
    public function setWeight($weight)
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

    public function getDisplayName($locale)
    {
        global $kernel;
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        return $entityManager->getRepository('AppBundle:RequirementTransliteration')->findTransliterationByLanguageCode($this, $locale);
    }
}
