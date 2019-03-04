<?php

namespace AppBundle\Handler;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $router = new HttpUtils();
        $this->setOptions(array('failure_path' => $router->generateUri($request, '/login')));
        $response = parent::onAuthenticationFailure($request, $exception);

        return $response;
    }
}