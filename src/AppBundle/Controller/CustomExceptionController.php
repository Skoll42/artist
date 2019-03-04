<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 10/1/2018
 * Time: 4:37 PM
 */

namespace AppBundle\Controller;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class CustomExceptionController extends ExceptionController
{
    protected $twig;
    private $message;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();
        $message = sprintf(
            'Error: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($message);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // sends the modified response object to the event
        $event->setResponse($response);
        $this->message = $event->getResponse()->getContent();
    }

    /**
     * @return mixed
     */
    public function showException($exception)
    {
        return $this->twig->render('@App/Exception/error.html.twig', array(
            'message' => $this->message));
    }
}
