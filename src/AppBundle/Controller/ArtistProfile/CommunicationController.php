<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 11.09.2018
 * Time: 10:42
 */
declare(strict_types=1);

namespace AppBundle\Controller\ArtistProfile;

use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/profile/artist")
 * @Security("has_role('ROLE_ARTIST')")
 */
class CommunicationController extends Controller
{
    use ControllerSupport;

    /**
     * @param Request $r
     * @param $id
     *
     * @Route("/{id}/edit/communication", name="artist_edit_communication")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function communicationAction(Request $r, $id)
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

        $chats = $this->get('doctrine_mongodb')->getRepository('ChatBundle:Messages')->getAllChatsByUser($id);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $chats,
            $r->query->get('page', 1)/*page number*/,
            20/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/artist/communication/list.html.twig', array(
            'artist' => $artist,
            'chats' => $pagination
        ));
    }

    /**
     * @param Request $r
     * @param $id
     * @param $id_with
     *
     * @Route("/{id}/edit/communication/{id_with}/chat", name="artist_communication_chat")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function communicationChatAction(Request $r, $id, $id_with)
    {
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);
        $customer = $this->getRepo("UserData")->findOneBy(['user' => $id_with]);

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        return $this->render('@App/user/artist/communication/chat.html.twig', array(
            'artist' => $artist,
            'customer' => $customer
        ));
    }

    /**
     * @param Request $r
     * @param $id
     *
     * @Route("/{id}/edit/communication/unread", name="artist_edit_communication_unread")
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

        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        $chats = $this->get('doctrine_mongodb')->getRepository('ChatBundle:Messages')->getUnreadByUser($id);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $chats,
            $r->query->get('page', 1)/*page number*/,
            20/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/artist/communication/list.html.twig', array(
            'artist' => $artist,
            'chats' => $pagination
        ));
    }
}
