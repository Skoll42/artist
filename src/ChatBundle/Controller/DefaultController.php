<?php
declare(strict_types=1);

namespace ChatBundle\Controller;

use AppBundle\Traits\ControllerSupport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    use ControllerSupport;

    public function indexAction(Request $r, $sender, $target, $room)
    {
        if ($this->isGranted('ROLE_CUSTOMER')) {
            $sender = $this->getRepo("UserData")->findOneBy([
                'user' => $sender,
                'deleted' => false
            ]);
        } else {
            $sender = $this->getRepo("ArtistData")->findOneBy([
                'user' => $sender,
                'deleted' => false
            ]);
        }

        return $this->render('@Chat/index.html.twig', [
            'sender' => $sender,
            'target' => $target,
            'chatRoomId' => $room
        ]);
    }
}
