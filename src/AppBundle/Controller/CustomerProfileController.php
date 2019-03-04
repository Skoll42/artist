<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UserData;
use AppBundle\Form\CustomerType;
use AppBundle\Form\UserSettingsType;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/profile/customer")
 */
class CustomerProfileController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/view", name="customer_profile_public")
     */
    public function viewProfilePublicAction(Request $r, $id)
    {
        $customer = $this->getDoctrine()->getRepository("AppBundle:UserData")->findOneBy(['user' => $id]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        $user = $customer->getUser();

        if ($user->getDeleted()) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        return $this->render('@App/user/customer/public_profile.html.twig', array(
            'customer' => $customer,
        ));
    }
}