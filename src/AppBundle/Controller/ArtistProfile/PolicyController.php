<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 12.09.2018
 * Time: 16:50
 */
declare(strict_types=1);

namespace AppBundle\Controller\ArtistProfile;

use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("has_role('ROLE_ARTIST')")
 */
class PolicyController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/edit/policy", name="artist_edit_policy")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     */
    public function editPolicyAction(Request $r, $id)
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

        if ($r->get('policy')) {
            $artist->setPolicy($r->get('policy'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($artist);
            $em->flush();
            return new JsonResponse($this->prepareJsonArr(true, 'success'));
        }

        return $this->render('@App/user/artist/policy.html.twig', [
            'policy' => (int)$artist->getPolicy(),
            'artist' => $artist
        ]);
    }
}
