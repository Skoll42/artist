<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Service\ServiceI;
use AppBundle\Traits\BaseFieldsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="This email is already in use"
 * )
 */
class User extends BaseUser implements BaseFieldsI
{
    use BaseFieldsTrait {
        BaseFieldsTrait::__construct as private __bfConstruct;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false, options={"default":false})
     */
    protected $banned;

    /**
     * @var string
     * @Assert\NotBlank(message = "Email can not be empty")
     */
    protected $email;

    /**
     * @ORM\OneToMany(targetEntity="UserMedia", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $images;

    /**
     * @var string
     *
     * @ORM\Column(name="youtube_video", type="string", nullable=true)
     */
    private $youTubeVideo;

    private $imageCount = 0;

    public function __construct()
    {
        parent::__construct();
        $this->__bfConstruct();
        $this->roles = array('ROLE_USER');
        $this->banned = false;
        $this->images = new ArrayCollection();
    }

    /**
     * Set banned
     *
     * @param bool $banned
     * @return $this
     */
    public function setBanned(bool $banned)
    {
        $this->banned = $banned;

        return $this;
    }

    /**
     * Get banned
     * @return bool
     */
    public function getBanned()
    {
        return $this->banned;
    }

    /**
     * Set translate.
     *
     * @param string $name
     *
     * @return User
     */
    public function setTranslate(string $name)
    {
        if (!is_null($name)) {
            $this->translate = $this->getService()->getTranslate($name);
        }

        return $this;
    }

    /**
     * Get translate.
     *
     * @return string
     */
    public function getTranslate()
    {
        return $this->translate;
    }

    /**
     * @return object
     */
    function getService()
    {
        global $kernel;

        return $kernel->getContainer()->get(ServiceI::TEXT_MANIPULATION);
    }

    /**
     * Set images
     *
     * @param $images
     * @return $this
     */
    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Get images
     *
     * @return ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    public function getUploadImages()
    {
        return null;
    }

    public function setUploadImages(array $files=array())
    {
        if (!$files) return [];
        global $kernel;

        $count = $kernel->getContainer()->getParameter('maxUploadFile') - $this->getImageCount();

        for ($i=0; ($i < count($files) && $i < $count); $i++) {
            if (!$files[$i]) return [];
            $this->uploadImage($files[$i]);
        }

        return [];
    }

    public function uploadImage(UploadedFile $file=null)
    {
        if (!$file) {
            return;
        }
        global $kernel;

        $image = new UserMedia();
        $image->setFile($file);
        $image->setUser($this);

        $validator = $kernel->getContainer()->get('validator');
        $errors = $validator->validate($image);

        if(count($errors) == 0 ) {
            $this->addImage($image);
        }
    }

    /**
     * Add image
     *
     * @param UserMedia $image
     *
     * @return User
     */
    public function addImage(UserMedia $image)
    {
        $this->images->add($image);
        $this->increaseImageCount();

        return $this;
    }

    /**
     * Remove image
     *
     * @param UserMedia $image
     */
    public function removeImage(UserMedia $image)
    {
        $image->setUser(null);
        $this->images->removeElement($image);
    }


    /**
     * Set imageCount
     *
     * @param integer $imageCount
     *
     * @return User
     */
    public function setImageCount(int $imageCount)
    {
        $this->imageCount = $imageCount;

        return $this;
    }

    /**
     * Get imageCount
     *
     * @return integer
     */
    public function getImageCount()
    {
        return $this->imageCount;
    }

    /**
     * Increase Image Count
     *
     * @return User
     */
    public function increaseImageCount()
    {
        $this->setImageCount($this->getImageCount() + 1);

        return $this;
    }

    /**
     * Decrease Image Count
     *
     * @return User
     */
    public function decreaseWeight()
    {
        $this->setImageCount($this->getImageCount() - 1);

        return $this;
    }

    /**
     * Set youTubeVideo
     *
     * @param string $youTubeVideo
     *
     * @return User
     */
    public function setYouTubeVideo($youTubeVideo)
    {
        $this->youTubeVideo = $youTubeVideo;

        return $this;
    }

    /**
     * Get youTubeVideo
     *
     * @return string
     */
    public function getYouTubeVideo()
    {
        return $this->youTubeVideo;
    }
}
