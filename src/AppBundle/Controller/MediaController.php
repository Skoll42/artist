<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/media")
 */
class MediaController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/delete", name="udelete_media")
     * @Method({"GET", "POST"})
     */
    public function deleteMediaAction(Request $r, $id)
    {
        $media = $this->getRepo("UserMedia")->find($id);

        if (!$media) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Media entity.'));
        }

        $media->setDeleted(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($media);
        $em->flush();

        return new JsonResponse($this->prepareJsonArr(true, 'Image deleted'));
    }
}