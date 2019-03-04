<?php
declare(strict_types=1);

namespace AppBundle\Controller\CustomerProfile;

use AppBundle\DBAL\BookingStatusEnum;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/profile/customer")
 * @Security("has_role('ROLE_CUSTOMER')")
 */
class DeleteController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/delete", name="customer_delete")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     */
    public function deleteAction(Request $r, $id)
    {
        $customer = $this->getRepo("UserData")->findOneBy(['user' => $id]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        $user = $customer->getUser();

        $user->setDeleted(true);
        $user->setEnabled(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $html = $this->renderView('@App/modals/delete_user.html.twig');

        return new JsonResponse($this->prepareJsonArr(true, 'Delete complete !', ['html' => $html]));
    }
}
