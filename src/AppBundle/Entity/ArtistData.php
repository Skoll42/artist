<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Traits\BaseFieldsTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @ORM\Table(name="artist_data")
 * @Vich\Uploadable
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ArtistDataRepository")
 */
class ArtistData implements BaseFieldsI
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
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

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
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="age", type="integer", nullable=true)
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="user_gender_enum", nullable=true)
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=12, scale=2,  nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="time", type="string", nullable=true)
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(name="policy", type="string", nullable=true)
     */
    private $policy;

    /**
     * @var \AppBundle\Entity\Location
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Location")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_id", type="string", nullable=true)
     */
    private $stripeId;

    /**
     * @var integer
     *
     * @ORM\Column(name="bookings", type="integer", nullable=false)
     */
    private $bookings;

    /**
     * @var integer
     *
     * @ORM\Column(name="accepted_booking", type="integer", nullable=false)
     */
    private $acceptedBookings;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_visible", type="boolean", nullable=false, options={"default" : false})
     */
    private $isVisible;

    public function __construct()
    {
        $this->__bfConstruct();
        $this->price = 0;
        $this->bookings = 0;
        $this->acceptedBookings = 0;
        $this->isVisible = 0;
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return ArtistData
     */
    public function setFirstName(string $firstName)
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
     * @return ArtistData
     */
    public function setLastName(string $lastName)
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
     * Set phoneCode
     *
     * @param string $phoneCode
     *
     * @return ArtistData
     */
    public function setPhoneCode($phoneCode)
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
     * @param string $phone
     *
     * @return ArtistData
     */
    public function setPhone(string $phone)
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
     * Set image
     *
     * @param string $image
     *
     * @return ArtistData
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
     * @return ArtistData
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
     * Set description
     *
     * @param string $description
     *
     * @return ArtistData
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
     * Set gender
     *
     * @param $gender
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return ArtistData
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
     * Set name.
     *
     * @param string|null $name
     *
     * @return ArtistData
     */
    public function setName($name = null)
    {
        $this->name = preg_replace('/\s+/', ' ', $name);

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return preg_replace('/\s+/', ' ', $this->name);
    }

    /**
     * Set age.
     *
     * @param int|null $age
     *
     * @return ArtistData
     */
    public function setAge($age = null)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age.
     *
     * @return int|null
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set time
     *
     * @param string $time
     *
     * @return ArtistData
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return ArtistData
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set policy
     *
     * @param string $policy
     *
     * @return ArtistData
     */
    public function setPolicy($policy)
    {
        $this->policy = $policy;

        return $this;
    }

    /**
     * Get policy
     *
     * @return string
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * Set stripeId
     *
     * @param string $stripeId
     *
     * @return ArtistData
     */
    public function setStripeId($stripeId)
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    /**
     * Get stripeId
     *
     * @return string
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

    /**
     * PRIVATE FIELDS
     */
    private $category;

    private $themes;

    private $tags;

    private $requirements;

    private $busyDates;

    private $otherRequirements;

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    public function getThemes()
    {
        return $this->themes;
    }

    public function setThemes($themes)
    {
        $this->themes = $themes;

        return $this;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    public function getRequirements()
    {
        return $this->requirements;
    }

    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;

        return $this;
    }

    public function getOtherRequirements()
    {
        return $this->otherRequirements;
    }

    public function setOtherRequirements($otherRequirements)
    {
        $this->otherRequirements = $otherRequirements;

        return $this;
    }

    /**
     * Set location
     *
     * @param \AppBundle\Entity\Location $location
     *
     * @return ArtistData
     */
    public function setLocation(\AppBundle\Entity\Location $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return \AppBundle\Entity\Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set bookings
     *
     * @param integer $bookings
     *
     * @return ArtistData
     */
    public function setBookings($bookings)
    {
        $this->bookings = $bookings;

        return $this;
    }

    /**
     * Get bookings
     *
     * @return integer
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * Set acceptedBookings
     *
     * @param integer $acceptedBookings
     *
     * @return ArtistData
     */
    public function setAcceptedBookings($acceptedBookings)
    {
        $this->acceptedBookings = $acceptedBookings;

        return $this;
    }

    /**
     * Get acceptedBookings
     *
     * @return integer
     */
    public function getAcceptedBookings()
    {
        return $this->acceptedBookings;
    }

    /**
     * Set IsVisible for Artist
     *
     * @param bool $isVisible
     *
     * @return ArtistData
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    /**
     * Get getIsVisible for Artist
     *
     * @return integer
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }

    /**
     * Set BusyDates for Artist
     *
     * @param string $busyDates
     *
     * @return ArtistData
     */
    public function setBusyDates($busyDates)
    {
        $this->busyDates = $busyDates;

        return $this;
    }

    /**
     * Get BusyDates for Artist
     *
     * @return string
     */
    public function getBusyDates()
    {
        return $this->busyDates;
    }
}
