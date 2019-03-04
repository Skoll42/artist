<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 05.09.2018
 * Time: 16:43
 */
declare(strict_types=1);

namespace AppBundle\Controller\ArtistProfile;

use AppBundle\Entity\ArtistData;
use AppBundle\Entity\Category;
use AppBundle\Entity\UserCategories;
use AppBundle\Entity\UserRequirements;
use AppBundle\Entity\UserTags;
use AppBundle\Entity\UserThemes;
use AppBundle\Form\ArtistType;
use AppBundle\Traits\ControllerSupport;
use Doctrine\ORM\EntityManager;
use libphonenumber\PhoneNumberUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

/**
 * @Route("/profile/artist")
 * @Security("has_role('ROLE_ARTIST')")
 */
class EditController extends Controller
{
    use ControllerSupport;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UserCategories
     */
    private $userCategories;

    /**
     * @var array
     */
    private $userThemes;

    /**
     * @var array
     */
    private $userTags;

    /**
     * @var array
     */
    private $userRequirements;

    /**
     * @var string
     */
    private $userOtherRequirements;

    /**
     * @Route("/{id}/edit", name="artist_edit")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     */
    public function editAction(Request $r, $id)
    {
        $locale = $r->getLocale();
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        // Get artist category mapped
        $artist = $this->getUserCategory($artist);

        // Get artist theme mapped
        $artist = $this->getArtistThemes($artist, $locale);

        // Get artist tag mapped
        $artist = $this->getArtistTags($artist);

        // Get artist requirement mapped
        $artist = $this->getArtistRequirements($artist, $locale);

        $editForm = $this->createForm(ArtistType::class, $artist, [
            'locale' => $locale
        ]);
        $editForm->handleRequest($r);

        if ($editForm->isSubmitted()) {
            $editForm = $this->formCustomValidate($artist, $editForm);

            if ($editForm->isValid()) {
                $this->em = $this->getDoctrine()->getManager();

                $this->setArtistCategory($artist, $editForm->getData()->getCategory());
                $this->setArtistThemes($artist, $editForm->getData()->getThemes());
                $this->setArtistTags($artist, $editForm->getData()->getTags());
                $this->setArtistRequirements(
                    $artist,
                    $editForm->getData()->getRequirements(),
                    $editForm->getData()->getOtherRequirements()
                );

                $this->em->flush();
                return new JsonResponse($this->prepareJsonArr(
                    true,
                    'Profile saved'
                ));
            } else {
                $errors = $this->getErrorMessages($editForm);
                return new JsonResponse($this->prepareJsonArr(
                    false,
                    'Validation Error',
                    array('errors' => $errors)
                ));
            }
        }

        $editProfile = (!empty($artist->getFirstName())
            && !empty($artist->getLastName())
            && !empty($artist->getLocation())
            && !empty($artist->getPhone()))
            ? 'true' : 'false';
        $stripeStatus = ($artist->getStripeId()) ? 'true' : 'false';

        return $this->render('@App/user/artist/edit.html.twig', array(
            'artist' => $artist,
            'form' => $editForm->createView(),
            'stripe_status' => $stripeStatus,
            'edit_profile' => $editProfile,
        ));
    }

    /**
     * Get artist category mapped
     *
     * @param ArtistData $artist
     * @return ArtistData
     */
    public function getUserCategory(ArtistData $artist) : ArtistData
    {
        $this->userCategories = $this->getRepo("UserCategories")->findOneBy(['user' => $artist->getUser()]);

        if (!is_null($this->userCategories)) {
            $artist->setCategory($this->userCategories->getCategory());
        }

        return $artist;
    }

    /**
     * Get artist theme mapped
     *
     * @param ArtistData $artist
     * @param null|string $locale
     * @return ArtistData
     */
    public function getArtistThemes(ArtistData $artist, ?string $locale) : ArtistData
    {
        $this->userThemes = $this->getRepo("UserThemes")->getArtistThemes($artist->getUser(), $locale);

        if (!is_null($this->userThemes)) {
            $artist->setThemes($this->userThemes);
        }

        return $artist;
    }

    /**
     * Get artist tag mapped
     *
     * @param ArtistData $artist
     * @return ArtistData
     */
    public function getArtistTags(ArtistData $artist) : ArtistData
    {
        $this->userTags = $this->getRepo("UserTags")->getArtistTags($artist->getUser());

        if (!is_null($this->userTags)) {
            $artist->setTags($this->userTags);
        }

        return $artist;
    }

    /**
     * Get artist requirement mapped
     *
     * @param ArtistData $artist
     * @param null|string $locale
     * @return ArtistData
     */
    public function getArtistRequirements(ArtistData $artist, ?string $locale) : ArtistData
    {
        $this->userRequirements = $this->getRepo("UserRequirements")
            ->getArtistRequirements($artist->getUser(), $locale);

        if (!is_null($this->userRequirements)) {
            $artist->setRequirements($this->userRequirements);

            $artistOtherRequirements = $this->getArtistOtherRequirements($artist, $locale);
            $artist->setOtherRequirements($artistOtherRequirements);
        }

        return $artist;
    }

    /**
     * Get artist requirement mapped
     *
     * @param ArtistData $artist
     * @param null|string $locale
     * @return mixed
     */
    public function getArtistOtherRequirements(ArtistData $artist, ?string $locale)
    {
        $this->userOtherRequirements = $this->getRepo("UserRequirements")
            ->getArtistOtherRequirements($artist->getUser(), $locale);

        return $this->userOtherRequirements;
    }

    /**
     * Update Artist Category
     *
     * @param ArtistData $artist
     * @param Category $category
     *
     * @return ArtistData
     */
    public function setArtistCategory(
        ArtistData $artist,
        Category $category
    ) : ArtistData {
        if (!is_null($this->userCategories)) {
            $this->userCategories->setCategory($category);
            $artist->setCategory($category);
            $this->em->persist($this->userCategories);

            return $artist;
        }

        $this->userCategories = (new UserCategories())
            ->setCategory($category)
            ->setUser($artist->getUser());

        $this->em->persist($this->userCategories);

        return $artist;
    }

    /**
     * Update Artist Themes
     *
     * @param ArtistData $artist
     * @param array|null $themes
     */
    public function setArtistThemes(ArtistData $artist, ?array $themes)
    {
        $user = $artist->getUser();

        foreach ($this->userThemes as $k => $theme) {
            $flag = true;
            foreach ($themes as $j => $item) {
                if ($flag) {
                    if ($theme->getId() == $item->getId()) {
                        unset($themes[$j]);
                        $flag = false;
                    }
                } else {
                    break;
                }
            }

            if ($flag) {
                $item = $this->getRepo('UserThemes')->findOneBy([
                    'theme' => $this->userThemes[$k],
                    'user' => $user
                ]);
                $item->setDeleted(true);
                $this->em->persist($item);
                unset($this->userThemes[$k]);
            }
        }

        foreach ($themes as $theme) {
            $item = $this->getRepo('UserThemes')->findOneBy([
                'theme' => $theme,
                'user' => $user
            ]);

            if ($item) {
                $item->setDeleted(false);
            } else {
                $item = (new UserThemes())->setTheme($theme)->setUser($user);
            }

            $this->em->persist($item);
        }
    }

    /**
     * Update Artist Tags
     *
     * @param ArtistData $artist
     * @param array|null $artistTags
     */
    public function setArtistTags(ArtistData $artist, ?array $artistTags)
    {
        $user = $artist->getUser();

        foreach ($this->userTags as $k => $tag) {
            $flag = true;
            foreach ($artistTags as $j => $item) {
                if ($flag) {
                    if ($tag->getId() == $item->getId()) {
                        unset($artistTags[$j]);
                        $flag = false;
                    }
                } else {
                    break;
                }
            }

            if ($flag) {
                $item = $this->getRepo('UserTags')->findOneBy([
                    'tag' => $this->userTags[$k],
                    'user' => $user
                ]);
                $item->setDeleted(true);
                $this->em->persist($item);
                unset($this->userTags[$k]);
            }
        }

        foreach ($artistTags as $tag) {
            $item = $this->getRepo('UserTags')->findOneBy([
                'tag' => $tag,
                'user' => $user
            ]);

            if ($item) {
                $item->setDeleted(false);
            } else {
                $item = (new UserTags())->setTag($tag)->setUser($user);
            }

            $this->em->persist($item);
        }
    }

    /**
     * Update Artist Requirements
     *
     * @param ArtistData $artist
     * @param array|null $artistRequirements
     * @param string|null $otherRequirement
     */
    public function setArtistRequirements(ArtistData $artist, ?array $artistRequirements, ?string $otherRequirement)
    {
        $user = $artist->getUser();

        foreach ($this->userRequirements as $k => $requirement) {
            $flag = true;
            foreach ($artistRequirements as $j => $item) {
                if ($flag) {
                    if ($requirement->getId() == $item->getId() && $item->getId() != 7) {
                        unset($artistRequirements[$j]);
                        $flag = false;
                    }
                } else {
                    break;
                }
            }

            if ($flag) {
                $item = $this->getRepo('UserRequirements')->findOneBy([
                    'requirement' => $this->userRequirements[$k],
                    'user' => $user
                ]);
                $item->setDeleted(true);
                $this->em->persist($item);
                unset($this->userRequirements[$k]);
            }
        }

        foreach ($artistRequirements as $requirement) {
            $item = $this->getRepo('UserRequirements')->findOneBy([
                'requirement' => $requirement,
                'user' => $user
            ]);

            if ($item) {
                $item->setDeleted(false);
            } else {
                $item = (new UserRequirements())->setRequirement($requirement)->setUser($user);
            }

            if ($item->getRequirement()->getId() == 7) {
                $item->setDescription($otherRequirement);
            }

            $this->em->persist($item);
        }
    }

    /**
     * Validate custom fields from another entity
     *
     * @param ArtistData $artist
     * @param $form
     * @return mixed
     */
    private function formCustomValidate(ArtistData $artist, $form)
    {
        if (is_null($artist->getThemes()) && ($artist->getThemes() === '' || is_null($artist->getThemes()))) {
            $form->get('themes')->addError(new FormError("Theme error"));
        }

        if (is_null($artist->getTags()) && ($artist->getTags() === '' || is_null($artist->getTags()))) {
            $form->get('tags')->addError(new FormError("Tags error"));
        }

        if (is_null($artist->getRequirements()) && ($artist->getRequirements() === ''
                || is_null($artist->getRequirements()))) {
            $form->get('requirements')->addError(new FormError("Requirements error"));
        }

        if (is_null($artist->getImageFile()) && ($artist->getImage() === '' || is_null($artist->getImage()))) {
            $form->get('imageFile')->addError(new FormError("Please, upload your photo!"));
        }

        $phoneNumberValidatorService = $this->get('libphonenumber.phone_number_util');
        $artistPhoneNumber = trim($artist->getPhoneCode()) . trim($artist->getPhone());

        $artistPhoneNumberObj = $phoneNumberValidatorService->parse($artistPhoneNumber, PhoneNumberUtil::UNKNOWN_REGION);
        $artistPhoneNumberStatus = $this->get('libphonenumber.phone_number_util')->isValidNumber($artistPhoneNumberObj);

        if($artistPhoneNumberStatus === false) {
            $form->get('phone')->addError(new FormError("Your Phone Number is not valid"));
        }

        return $form;
    }

    /**
     * Get form errors and return array
     *
     * @param $form
     * @return array
     */
    private function getErrorMessages($form) : array
    {
        $errors = array();

        foreach ($form as $child) {
            foreach ($child->getErrors(true) as $error) {
                $errors[$child->getName()] = $error->getMessage();
            }
        }

        return $errors;
    }
}
