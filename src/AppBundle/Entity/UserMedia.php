<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Traits\BaseFieldsTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * UserMedia
 *
 * @ORM\Table(name="user_media")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserMediaRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class UserMedia implements BaseFieldsI
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="userMedia", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255)
     */
    private $image;

    /**
     * @Assert\File(
     *     maxSize = "10485760",
     *     mimeTypes = {
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg"
     *     },
     *     maxSizeMessage = "The file is too large ({{ size }} {{ suffix }}. Allowed maximum size is {{ limit }} {{ suffix }}",
     *     mimeTypesMessage = "Please, upload valid *.jpg or *.png",
     * )
     *
     * @Vich\UploadableField(mapping="user_photo", fileNameProperty="image")
     * @Assert\Valid()
     *
     * @var File
     */
    private $file;

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
     * @return UserMedia
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
     * Set image
     *
     * @param string $image
     *
     * @return UserMedia
     */
    public function setImage($image)
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
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return UserMedia
     */
    public function setFile(File $file = null) {
        $this->file = $file;

        if($file){
            $this->updatedDate = new \DateTime();
        }
        return $this;
    }

    /**
     * @return File|null
     */
    public function getFile() {
        return $this->file;
    }
}
