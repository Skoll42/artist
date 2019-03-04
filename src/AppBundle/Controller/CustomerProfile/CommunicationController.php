<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 11.09.2018
 * Time: 10:42
 */
declare(strict_types=1);

namespace AppBundle\Controller\CustomerProfile;

use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/profile/customer")
 * @Security("has_role('ROLE_CUSTOMER')")
 */
class CommunicationController extends Controller
{
    use ControllerSupport;

    /**
     * @param Request $r
     * @param $id
     *
     * @Route("/{id}/edit/communication", name="customer_edit_communication")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function communicationAction(Request $r, $id)
    {
        $customer = $this->getRepo("UserData")->findOneBy(['user' => $id]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        $chats = $this->get('doctrine_mongodb')->getRepository('ChatBundle:Messages')->getAllChatsByUser($id);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $chats,
            $r->query->get('page', 1)/*page number*/,
            20/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/customer/communication/list.html.twig', array(
            'customer' => $customer,
            'chats' => $pagination
        ));
    }

    /**
     * @param Request $r
     * @param $id
     * @param $id_with
     *
     * @Route("/{id}/edit/communication/{id_with}/chat", name="customer_communication_chat")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function communicationChatAction(Request $r, $id, $id_with)
    {
        $customer = $this->getRepo("UserData")->findOneBy(['user' => $id]);
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id_with]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        return $this->render('@App/user/customer/communication/chat.html.twig', array(
            'customer' => $customer,
            'artist' => $artist
        ));
    }

    /**
     * @param Request $r
     * @param $id
     *
     * @Route("/{id}/edit/communication/unread", name="customer_edit_communication_unread")
     * @Method({"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function unreadCommunicationAction(Request $r, $id)
    {
        $auth_checker = $this->get('security.authorization_checker');

        if ($auth_checker->isGranted('ROLE_USER') == false) {
            //throw new NotFoundHttpException();
            return $this->render('@App/Exception/error.html.twig');
        }

        $customer = $this->getRepo("UserData")->findOneBy(['user' => $id]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $chats = $this->get('doctrine_mongodb')->getRepository('ChatBundle:Messages')->getUnreadByUser($id);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $chats,
            $r->query->get('page', 1)/*page number*/,
            20/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/customer/communication/list.html.twig', array(
            'customer' => $customer,
            'chats' => $pagination
        ));
    }
}
