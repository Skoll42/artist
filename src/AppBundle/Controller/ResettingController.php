<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 16.10.2018
 * Time: 17:01
 */
declare(strict_types=1);

namespace AppBundle\Controller;

use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ResettingController extends Controller
{
    public function sendEmailAction(Request $request)
    {
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $username = $request->request->get('username');
        $user = $userManager->findUserByUsernameOrEmail($username);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'User was not found',
            ]);
        }

        if (!$user->isEnabled()) {
            if ($user->getDeleted()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Sorry, your account is inactive. Please contact support.',
                ]);
            }

            return new JsonResponse([
                'success' => false,
                'error' => 'User is not verified',
            ]);
        }

        $event = new GetResponseNullableUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if (null !== $user && !$user->isPasswordRequestNonExpired(30)) {
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());
            }

            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $userManager->updateUser($user);

            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
        }

        return new RedirectResponse($this->generateUrl('fos_user_resetting_check_email', array('username' => $username)));
    }
}
