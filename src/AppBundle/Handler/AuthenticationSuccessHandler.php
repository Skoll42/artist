<?php

namespace AppBundle\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $cookie = new \Symfony\Component\HttpFoundation\Cookie(
            'login',
            1,
            time() + (3600 * 24 * 365),
            '/',
            null,
            false,
            false
        );

        $response = parent::onAuthenticationSuccess($request, $token);
        $response->headers->setCookie($cookie);

        return $response;
    }
}