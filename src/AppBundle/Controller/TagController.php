<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/tag")
 */
class TagController extends Controller
{
    use ControllerSupport;

    /**
     * Create new tag by name
     *
     * @Route("/new/{name}", name="new_tag_ajax")
     * @Method({"GET", "POST"})
     */
    public function newTagAjaxAction(Request $r, $name)
    {
        $tag = $this->getRepo("Tag")->findOneBy(['name' => $name]);

        if ($tag) {
            return new JsonResponse($this->prepareJsonArr(
                false,
                'Tag already exist'
            ));
        }

        $tag = (new Tag())
            ->setName($name);

        $em = $this->getDoctrine()->getManager();

        $em->persist($tag);
        $em->flush();

        return new JsonResponse($this->prepareJsonArr(
            true,
            'Tag created',
            array(
                'tag' => array(
                    'id' => $tag->getId(),
                    'name' => $tag->getName()
                )
            )
        ));
    }
}