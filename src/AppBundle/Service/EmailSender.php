<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 18.10.2018
 * Time: 11:16
 */
declare(strict_types=1);

namespace AppBundle\Service;

use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmailSender
{
    protected $mailer;

    protected $engine;

    protected $container;

    public function __construct(\Swift_Mailer $mailer, TwigEngine $engine, ContainerInterface $container)
    {
        $this->mailer = $mailer;
        $this->engine = $engine;
        $this->container = $container;
    }

    public function sendEmail($emailFrom, $emailTo, $subject, $templateName, $templateData)
    {
        $template = $this->getTemplate($templateName, $templateData);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($emailFrom, $this->container->getParameter('site_name'))
            ->setTo($emailTo)
            ->setBody($template, 'text/html');

        return $this->mailer->send($message);
    }

    private function getTemplate($templateName, $templateData)
    {
        return $this->engine->render($templateName, $templateData);
    }

    public function sendBookingRequest($artist, $customer, $booking, $env)
    {
        $this->sendEmail(
            $this->container->getParameter($env.'_mailer_address'),
            $artist->getUser()->getEmail(),
            'New Booking Request',
            '@App/emails/booking_request.html.twig',
            [
                'artistName' => $artist->getName(),
                'customerName' => $customer->getFirstName() && $customer->getLastName() ?
                    $customer->getFirstName() . ' ' . $customer->getLastName() :
                    $customer->getUser()->getUsername()
            ]
        );
    }

    public function sendBookingConfirmed($artist, $customer, $booking, $env)
    {
        $this->sendEmail(
            $this->container->getParameter($env.'_mailer_address'),
            $customer->getUser()->getEmail(),
            'Booking Confirmed',
            '@App/emails/booking_confirmed.html.twig',
            [
                'date' => $booking->getEventDate()->format("Y-m-d H:i"),
                'customerName' => $customer->getFirstName() && $customer->getLastName() ?
                    $customer->getFirstName() . ' ' . $customer->getLastName() :
                    $customer->getUser()->getUsername(),
            ]
        );
    }

    public function sendBookingRejected($artist, $customer, $booking, $env)
    {
        $this->sendEmail(
            $this->container->getParameter($env.'_mailer_address'),
            $customer->getUser()->getEmail(),
            'Booking Rejected',
            '@App/emails/booking_rejected.html.twig',
            [
                'date' => $booking->getEventDate()->format("Y-m-d H:i"),
                'artistName' => $artist->getName(),
                'customerName' => $customer->getFirstName() && $customer->getLastName() ?
                    $customer->getFirstName() . ' ' . $customer->getLastName() :
                    $customer->getUser()->getUsername(),
            ]
        );
    }

    public function sendBookingCanceledFromArtist($artist, $customer, $booking, $env)
    {
        $this->sendEmail(
            $this->container->getParameter($env.'_mailer_address'),
            $customer->getUser()->getEmail(),
            'Booking Cancelled',
            '@App/emails/booking_cancelled.html.twig',
            [
                'date' => $booking->getEventDate()->format("Y-m-d H:i"),
                'artistName' => $artist->getName(),
                'customerName' => $customer->getFirstName() && $customer->getLastName() ?
                    $customer->getFirstName() . ' ' . $customer->getLastName() :
                    $customer->getUser()->getUsername(),
            ]
        );
    }

    public function sendBookingCanceledFromCustomer($artist, $customer, $booking, $env)
    {
        $customerName = $customer->getFirstName() && $customer->getLastName() ?
            $customer->getFirstName() . ' ' . $customer->getLastName() :
            $customer->getUser()->getUsername();

        $this->sendEmail(
            $this->container->getParameter($env.'_mailer_address'),
            $artist->getUser()->getEmail(),
            'The Event with ' . $customerName . ' on ' . $booking->getEventDate()->format("Y-m-d H:i") . ' is cancelled',
            '@App/emails/booking_cancelled_by_customer.html.twig',
            [
                'date' => $booking->getEventDate()->format("Y-m-d H:i"),
                'artistName' => $artist->getName(),
                'customerName' => $customer->getFirstName() && $customer->getLastName() ?
                    $customer->getFirstName() . ' ' . $customer->getLastName() :
                    $customer->getUser()->getUsername(),
            ]
        );
    }

    public function sendChargePendingArtist($artist, $customer, $booking, $env)
    {
        $customerName = $customer->getFirstName() && $customer->getLastName() ?
            $customer->getFirstName() . ' ' . $customer->getLastName() :
            $customer->getUser()->getUsername();
        $date = $booking->getEventDate()->format("Y-m-d H:i");

        $this->sendEmail(
            $this->container->getParameter($env.'_mailer_address'),
            $artist->getUser()->getEmail(),
            'Reminder about the event with ' . $customerName . ' on ' . $date,
            '@App/emails/event_reminder_artist.html.twig',
            [
                'date' => $date,
                'artistName' => $artist->getName(),
                'customerName' => $customerName,
            ]
        );
    }

    public function sendChargePendingCustomer($artist, $customer, $booking, $env)
    {
        $customerName = $customer->getFirstName() && $customer->getLastName() ?
            $customer->getFirstName() . ' ' . $customer->getLastName() :
            $customer->getUser()->getUsername();
        $date = $booking->getEventDate()->format("Y-m-d H:i");

        $this->sendEmail(
            $this->container->getParameter($env.'_mailer_address'),
            $customer->getUser()->getEmail(),
            'Pre-authorization is successful.',
            '@App/emails/event_reminder_customer.html.twig',
            [
                'date' => $date,
                'artistName' => $artist->getName(),
                'customerName' => $customerName,
            ]
        );
    }
}
