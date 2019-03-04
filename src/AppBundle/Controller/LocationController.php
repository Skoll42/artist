<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Location;
use AppBundle\Entity\Tag;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/location")
 */
class LocationController extends Controller
{
    use ControllerSupport;

    /**
     * Create new location by name
     *
     * @Route("/new", name="new_location_ajax")
     * @Method({"GET", "POST"})
     */
    public function newLocationAjaxAction(Request $r)
    {
        $name = $r->get('longName');
        $code = $r->get('shortName');
        $location = $this->getRepo("Location")->findOneBy(['name' => $name]);

        if ($location) {
            return new JsonResponse($this->prepareJsonArr(
                false,
                'Location already exist',
                array(
                    'location' => array(
                        'id' => $location->getId(),
                        'name' => $location->getName()
                    )
                )
            ));
        }

        $location = (new Location())
            ->setName($name)
            ->setCode($code);

        $em = $this->getDoctrine()->getManager();

        $em->persist($location);
        $em->flush();

        return new JsonResponse($this->prepareJsonArr(
            true,
            'Location created',
            array(
                'location' => array(
                    'id' => $location->getId(),
                    'name' => $location->getName()
                )
            )
        ));
    }
}