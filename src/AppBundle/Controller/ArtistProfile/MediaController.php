<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 12.09.2018
 * Time: 13:55
 */
declare(strict_types=1);

namespace AppBundle\Controller\ArtistProfile;

use AppBundle\Form\UserType;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/profile/artist")
 * @Security("has_role('ROLE_ARTIST')")
 */
class MediaController extends Controller
{
    use ControllerSupport;

    /**
     * @param Request $r
     * @param $id
     *
     * @Route("/{id}/edit/media", name="artist_edit_media")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editMediaAction(Request $r, $id)
    {
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        if ($artist
            && empty($artist->getFirstName())
            && empty($artist->getLastName())
            && empty($artist->getLocation())
            && empty($artist->getPhone())) {
            throw $this->createNotFoundException('You should enter you profile details first.');
        }

        $user = $artist->getUser();
        $userMedias = $this->getRepo("UserMedia")->findBy([
            'user' => $user->getId(),
            'deleted' => false
        ]);

        $user->setImageCount(count($userMedias));

        $editForm = $this->createForm(UserType::class, $user);
        $editForm->handleRequest($r);

        if ($editForm->isSubmitted()) {
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return new JsonResponse($this->prepareJsonArr(true, 'Images Uploaded'));
            }

            $errors = $this->getErrorMessages($editForm);
            return new JsonResponse($this->prepareJsonArr(
                false,
                'Upload Error',
                array('errors' => $errors)
            ));
        }
        return $this->render('@App/user/artist/media.html.twig', array(
            'form' => $editForm->createView(),
            'images' => $userMedias,
            'artist' => $artist
        ));
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

        foreach ($form->getErrors() as $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
