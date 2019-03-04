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
 * @ORM\Table(name="theme")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ThemeRepository")
 */
class Theme implements BaseFieldsI
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

    /**
     * @Assert\File(
     *     maxSize = "10000k",
     *     mimeTypes = {"image/jpeg"},
     *     mimeTypesMessage = "Please, upload valid *.jpg"
     * )
     * @Vich\UploadableField(mapping="theme_image", fileNameProperty="image")
     */
    private $imageFile;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

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
     * @return Theme
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

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Theme
     */
    public function setImage(string $image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return mixed
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Sets file.
     *
     * @param File|UploadedFile $imageFile
     *
     * @return Theme
     */
    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if ($imageFile) {
            $this->updatedDate = new \DateTime();
        }

        return $this;
    }

    public function getDisplayName($locale)
    {
        global $kernel;
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        return $entityManager->getRepository('AppBundle:ThemeTransliteration')->findTransliterationByLanguageCode($this, $locale);
    }
}
