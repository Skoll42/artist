<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\DBAL\EnumType;
use AppBundle\DBAL\UserGenderEnum;
use AppBundle\Traits\BaseFieldsTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use JMS\Serializer\Annotation as Serializer;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_data")
 * @Vich\Uploadable
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserDataRepository")
 */
class UserData implements BaseFieldsI
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
     * @var string
     * @Assert\NotBlank(message = "This value should not be blank.")
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     * @Assert\NotBlank(message = "This value should not be blank.")
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_code", type="string", length=255, nullable=true)
     */
    private $phoneCode;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="age", type="datetime", nullable=true)
     */
    private $age;

    /**
     * @Assert\File(
     *     maxSize = "10485760",
     *     mimeTypes = {
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg"
     *     },
     *     mimeTypesMessage = "Please, upload valid *.jpg or *.png"
     * )
     * @Vich\UploadableField(mapping="user_photo", fileNameProperty="image")
     */
    private $imageFile;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;


    /**
     * @var string
     *
     * @ORM\Column(name="stripe_id", type="string", length=255, nullable=true)
     */
    private $stripeId;

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
     * Set phoneCode
     *
     * @param null|string $phoneCode
     *
     * @return UserData
     */
    public function setPhoneCode(?string $phoneCode)
    {
        $this->phoneCode = $phoneCode;

        return $this;
    }

    /**
     * Get phoneCode
     *
     * @return string
     */
    public function getPhoneCode()
    {
        return $this->phoneCode;
    }

    /**
     * Set phone
     *
     * @param null|string $phone
     *
     * @return UserData
     */
    public function setPhone(?string $phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set description
     *
     * @param null|string $description
     *
     * @return UserData
     */
    public function setDescription(?string $description)
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
     * Set user
     *
     * @param User $user
     *
     * @return UserData
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
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * Set age
     *
     * @param \DateTime $age
     *
     * @return UserData
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return \DateTime
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return UserData
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
     * @return UserData
     */
    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if ($imageFile) {
            $this->updatedDate = new \DateTime();
        }

        return $this;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return UserData
     */
    public function setFirstName($firstName)
    {
        $this->firstName = preg_replace('/\s+/', ' ', $firstName);

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return preg_replace('/\s+/', ' ', $this->firstName);
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return UserData
     */
    public function setLastName($lastName)
    {
        $this->lastName = preg_replace('/\s+/', ' ', $lastName);

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return preg_replace('/\s+/', ' ', $this->lastName);
    }

    /**
     * Set stripeId
     *
     * @param string $stripeId
     *
     * @return UserData
     */
    public function setStripeId($stripeId)
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

}
