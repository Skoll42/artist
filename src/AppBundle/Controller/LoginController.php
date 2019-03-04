<?php
/**
 * Created by PhpStorm.
 * User: opetravchuk
 * Date: 04.07.2018
 * Time: 18:26
 */

namespace AppBundle\Controller;

use Doctrine\ORM\Query\Expr\From;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\UserBundle\Controller\SecurityController as FOSController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginController extends FOSController
{
    /**
     * @Route("/login", name="registration_login")
     *
     */
    public function loginAction(Request $request)
    {
        $tokenManager = $this->get('security.csrf.token_manager');

        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        $csrfToken = $tokenManager
            ? $tokenManager->getToken('authenticate')->getValue()
            : null;

        if (!empty($error)) {

            /** @var $userManager UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $user = $userManager->findUserBy(['email' => $lastUsername]);

            if (!$user) {
                return new JsonResponse($this->loginResponse(
                    $lastUsername,
                    $error ? $error->getMessage() : $error,
                    $csrfToken
                ));
            }

            if (!$user->isEnabled()) {
                if ($user->getDeleted()) {
                    return new JsonResponse($this->loginResponse(
                        $lastUsername,
                        $this->renderView('AppBundle:Registration:inactive_content.html.twig'),
                        $csrfToken
                    ));
                }

                return new JsonResponse($this->loginResponse(
                    $lastUsername,
                    $this->renderView('AppBundle:Registration:not_verified_content.html.twig', ['email' => $lastUsername]),
                    $csrfToken
                ));
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($this->loginResponse(
                $lastUsername,
                $error ? $error->getMessage() : $error,
                $csrfToken
            ));
        }

        $router = $this->container->get('router');

        if ($request->getPathInfo() == $router->generate('fos_user_security_login')) {
            return new RedirectResponse($router->generate('index'));
        }

        return $this->render('@App/Security/login_content.html.twig', $this->loginResponse(
            $lastUsername,
            $error ? $error->getMessage() : $error,
            $csrfToken
        ));
    }

    private function loginResponse($username, $errors, $token)
    {
        return [
            'last_username' => $username,
            'error' => $errors,
            'csrf_token' => $token,
        ];
    }

    /**
     * @param $form
     * @return array
     */
    private function getErrorMessages(From $form) : array
    {
        $errors = array();

        foreach ($form as $child) {
            foreach ($child->getErrors(true) as $error) {
                $errors[$child->getName()] = $error->getMessage();
            }
        }

        return $errors;
    }
}
