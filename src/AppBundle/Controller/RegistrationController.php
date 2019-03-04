<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ArtistData;
use AppBundle\Entity\UserData;
use FOS\UserBundle\Controller\ResettingController;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\Form\Form;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{
    const SIGN_UP_COUNT_KEY = 'signup_count';
    const SIGN_UP_TIMESTAMP = 'signup_timestamp';

    /**
     * @Route("/register/", name="registration_register")
     *
     */
    public function registerAction(Request $request, Session $session)
    {
        $clientTimestamp = $request->get('clientTime');
        $needCaptchaFlag = false;
        $needCaptchaAjaxFlag = false;
        $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
        $timestamp_utc = $date_utc->getTimestamp();

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $this->addUserName($request);

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        //show the captcha after the number of attempts and less than amount of minutes
        $numberOfAttempts = $this->getParameter('captcha_attempts_before_show');
        $captchaTimeoutMinutes = $this->getParameter('captcha_timeout_minutes');

        if ($session->has(self::SIGN_UP_COUNT_KEY) && $session->has(self::SIGN_UP_TIMESTAMP)) {
            $signupCount = $session->get(self::SIGN_UP_COUNT_KEY);
            $latestSignUpAttemptTimestamp = $session->get(self::SIGN_UP_TIMESTAMP);
            $latestSignUpAttemptTimestampOffset = $captchaTimeoutMinutes * 60;

            if (($timestamp_utc - $latestSignUpAttemptTimestamp) > $latestSignUpAttemptTimestampOffset ) {
                $needCaptchaFlag = false;
            } else {
                $needCaptchaFlag = ($signupCount > $numberOfAttempts) ? true : false;
            }
        } else {
            $latestSignUpAttemptTimestampOffset = $timestamp_utc + $captchaTimeoutMinutes * 60;
            $signupCount = 1;
            $session->set(self::SIGN_UP_COUNT_KEY, $signupCount);
            $session->set(self::SIGN_UP_TIMESTAMP, $timestamp_utc);
        }

        if ($form->isSubmitted()) {
            if ($session->has(self::SIGN_UP_COUNT_KEY) && $session->has(self::SIGN_UP_TIMESTAMP)) {
                $signupCount = $session->get(self::SIGN_UP_COUNT_KEY);
                $latestSignUpAttemptTimestamp = $session->get(self::SIGN_UP_TIMESTAMP);
                $latestSignUpAttemptTimestampOffset = $captchaTimeoutMinutes * 60;

                if (($timestamp_utc - $latestSignUpAttemptTimestamp) > $latestSignUpAttemptTimestampOffset )
                {
                    $signupCount = 1;
                    $session->set(self::SIGN_UP_TIMESTAMP, $timestamp_utc);
                    $session->set(self::SIGN_UP_COUNT_KEY, $signupCount);
                }

                //for ajax response
                $needCaptchaAjaxFlag = ($signupCount >= $numberOfAttempts) ? true : false;
                $session->set(self::SIGN_UP_COUNT_KEY, ++$signupCount);
            }

            if ($needCaptchaFlag) {
                $secretKey = $this->container->getParameter('recaptcha_secret_key');
                $recaptcha = new ReCaptcha($secretKey);
                $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
            }

            if ($needCaptchaFlag && !$resp->isSuccess()) {
                // Do something if the submit wasn't valid ! Use the message to show something
                $errors['captcha'] = "The reCAPTCHA wasn't entered correctly. Go back and try it again.";
            } else {
                $artistFlag = false;

                if (!empty($request->request->get('user_type')) && $request->request->get('user_type') == 'artist') {
                    $user->setRoles(['ROLE_ARTIST']);
                    $artistFlag = true;
                } else {
                    $user->setRoles(['ROLE_CUSTOMER']);
                }

                if ($form->isValid()) {
                    $event = new FormEvent($form, $request);
                    $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                    $userManager->updateUser($user);

                    $em = $this->getDoctrine()->getManager();

                    if ($artistFlag) {
                        $artistData = new ArtistData();
                        $artistData->setUser($user);
                        $em->persist($artistData);
                    } else {
                        $userData = new UserData();
                        $userData->setUser($user);
                        $em->persist($userData);
                    }

                    $em->flush();

                    if (null === $response = $event->getResponse()) {
                        $url = $this->generateUrl('fos_user_registration_confirmed');
                        $response = new RedirectResponse($url);
                    }

                    $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                    return new JsonResponse([
                        "success" => true,
                        "redirect" => $this->generateUrl('index'),
                        'need_captcha' => $needCaptchaFlag,
                        'sign_up_time' => $latestSignUpAttemptTimestampOffset,
                        'client_time' => $clientTimestamp,
                        'attempt_number' => $signupCount,
                        'need' => $needCaptchaFlag,
                    ]);

                }

                $event = new FormEvent($form, $request);

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

                if (null !== $response = $event->getResponse()) {
                    return $response;
                }

                $errors = $this->getErrorMessages($form);
            }

            return new JsonResponse([
                "success" => false,
                "errors" => $errors,
                'need_captcha' => $needCaptchaAjaxFlag
            ]);
        }



        return $this->render('@App/Registration/register_content.html.twig', array(
            'form' => $form->createView(),
            'recaptcha_site_key' => $this->container->getParameter('recaptcha_site_key'),
            'show_captcha_flag' => $needCaptchaFlag,
        ));
    }

    /**
     * Tell the user to check his email provider.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function checkEmailAction(Request $request)
    {
        $username = $request->query->get('username');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(['email'=>$username]);

        if (empty($username) || empty($user)) {
            // the user does not come from the sendEmail action or user does not exist in db
            return new JsonResponse([
                'success' => false,
                'error' => 'Email was not found',
                ]);
        }

        return new JsonResponse(['success' => true,]);
    }

    /**
     * @Route("/resetting/confirmfp", name="resetting_confirm_forgot_password")
     *
     */
    public function confirmForgotPassAction(Request $request)
    {
        return $this->render('@App/Resetting/confirm_forgot_password.html.twig', []);
    }

    /**
     * @Route("/register/resend", name="register_resend_email")
     * @Method({"POST"})
     */
    public function resendEmailAction(Request $request)
    {
        $content = $request->getContent();

        $responseData = ['success' => false];

        $userData = '';

        if ($content) {
            $userData = json_decode($content, true);
        }

        if (!empty($userData['email'])) {

            /** @var $userManager UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $user = $userManager->findUserBy(['email' => $userData['email']]);

            $responseData['success'] = (bool)$this->sendActivationMessage($user);
        }

        return new JsonResponse($responseData);
    }

    /**
     * @Route("/register/confirmrg", name="register_confirm_registration")
     *
     */
    public function confirmRegistrationSuccessAction(Request $request)
    {
        return $this->render('@App/Registration/confirm_success_registration.html.twig', []);
    }

    /**
     * Request reset user password: show form.
     */
    public function requestAction()
    {
        return $this->render('@App/Resetting/request_content.html.twig');
    }

    /**
     * @param UserInterface $user
     * @return int
     */
    private function sendActivationMessage(UserInterface $user)
    {
        if (empty($user->getConfirmationToken())) {
            return false;
        }

        $fromEmail = array($this->getParameter($this->getEnv() .'_mailer_address') => $this->getParameter($this->getEnv() .'_mailer_sender_name'));
        $subject = 'Welcome to Artist NextDoor!';
        $toEmail = $user->getEmail();

        $body = $this->renderView('@App/Registration/resend_registration_email.txt.twig', ['user' => $user]);

        $message = (new \Swift_Message($subject))
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody(
                $body,
                'text/html'
            );

        return $this->get('mailer')->send($message);
    }

    private function getEnv()
    {
        return $this->container->get( 'kernel' )->getEnvironment();
    }

    /**
     * Add username to request
     *
     * @param Request $request
     */
    private function addUserName(Request $request)
    {
        /** @var  $userRegistrationData array Data of user registration form */
        $userRegistrationData = $request->get('fos_user_registration_form');

        if (!empty($userRegistrationData['email'])) {
            $username = $this->getUserNameFromEmail($userRegistrationData['email']);
            $user = $this->checkUserNameExistance($username);

            if (is_object($user)) {
                $userRegistrationData['username'] = $this->generateUserNameUnique($user->getUsername());
            }
            else {
                $userRegistrationData['username'] = $username;
            }

            $request->request->set('fos_user_registration_form', $userRegistrationData);
        }
    }

    /**
     * @param $userName
     * @return string
     */
    private function generateUserNameUnique($userName) {
        return $userName . '_' . time();
    }

    /**
     * Check if username already exists in db
     * if return true than user doesn't exists
     * else return the existing user
     * @param $userName
     * @return \AppBundle\Entity\User|bool|object
     */
    private function checkUserNameExistance($userName) {
        $user = $this->getDoctrine()->getRepository("AppBundle:User")->findOneBy(['username' => $userName]);
        return (is_null($user)) ? true : $user;
    }

    /**
     * Get user name from user email
     *
     * @param string $email
     * @return string
     */
    private function getUserNameFromEmail(string $email):string
    {
        $dataArray = explode('@', $email);
        return array_shift($dataArray);
    }

    /**
     * @param Form $form
     * @return array
     */
    private function getErrorMessages(Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
