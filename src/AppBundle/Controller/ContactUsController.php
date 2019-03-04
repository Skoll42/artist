<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 10/18/2018
 * Time: 7:30 PM
 */

namespace AppBundle\Controller;

use AppBundle\Form\ContactUsType;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class ContactUsController
 * @package AppBundle\Controller
 *
 */
class ContactUsController extends Controller
{
    use ControllerSupport;

    const CONTACT_US_COUNT_KEY = 'contact_us_count';
    const CONTACT_US_TIMESTAMP = 'contact_us_timestamp';

    /**
     * @Route("/contact-us", name="contact-us")
     * @Method({"GET", "POST"})
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function contactAction(Request $r, Session $session)
    {
        $locale = $r->getLocale();

        $contactUsForm = $this->createForm(ContactUsType::class, null, [
            'locale' => $locale
        ]);

        $contactUsForm->handleRequest($r);

        //show the captcha after the number of attempts and less than amount of minutes
        $numberOfAttempts = $this->getParameter('captcha_attempts_before_show');
        $captchaTimeoutMinutes = $this->getParameter('captcha_timeout_minutes');

        if ($contactUsForm->isSubmitted()) {
            $clientTimestamp = $r->get('clientTime');
            $needCaptchaAjaxFlag = false;

            if ($contactUsForm->isValid()) {
                if ($session->has(self::CONTACT_US_COUNT_KEY) && $session->has(self::CONTACT_US_TIMESTAMP)) {
                    $contactUsCount = $session->get(self::CONTACT_US_COUNT_KEY);
                    $latestSignUpAttemptTimestamp = $session->get(self::CONTACT_US_TIMESTAMP);
                    $latestSignUpAttemptTimestampOffset = $latestSignUpAttemptTimestamp + $captchaTimeoutMinutes * 60;
                    $session->set(self::CONTACT_US_COUNT_KEY, ++$contactUsCount);

                    if($latestSignUpAttemptTimestampOffset < intval($clientTimestamp))
                    {
                        $contactUsCount = 1;
                        $session->set(self::CONTACT_US_TIMESTAMP, time());
                    }

                    $needCaptchaAjaxFlag = ($contactUsCount >= $numberOfAttempts) ? true : false;

                } else {
                    $session->set(self::CONTACT_US_COUNT_KEY, 1);
                    $session->set(self::CONTACT_US_TIMESTAMP, time());
                }

                $data = $contactUsForm->getData();

                try {
                    $email = $this->container->getParameter(
                        $this->container->getParameter('kernel.environment') . '_mailer_address'
                    );
                    $subject = 'You\'ve received feedback from ' . $data['name'];

                    $sender = $this->container->get('email_sender');
                    $sender->sendEmail(
                        $email,
                        $email,
                        $subject,
                        '@App/emails/contact_us.html.twig',
                        [
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'message' => $data['feedback'],
                        ]
                    );
                } catch (\Exception $e) {
                    $errors[] = $e->getTraceAsString();
                    return new JsonResponse([
                            $this->prepareJsonArr(
                                false,
                                'Error',
                                array('errors' => $errors)
                            ),
                            'contact_us_need_captcha' => $needCaptchaAjaxFlag
                        ]
                    );
                }

                return new JsonResponse([
                    $this->prepareJsonArr(
                            true,
                            'Message Sent',
                            array('modal' => $this->renderView('@App/modals/feedback_sent.html.twig'))
                        ),
                        'contact_us_need_captcha' => $needCaptchaAjaxFlag
                    ]
                );
            } else {
                $errors[] = $this->getErrorMessages($contactUsForm);
                return new JsonResponse([
                        $this->prepareJsonArr(
                        false,
                        'Validation Error',
                        array('errors' => $errors)
                    ),
                    'contact_us_need_captcha' => $needCaptchaAjaxFlag
                ]);
            }
        }

        return $this->render('@App/Contact/contact-us.html.twig', array(
            'form' => $contactUsForm->createView(),
            'recaptcha_site_key' => $this->container->getParameter('recaptcha_site_key'),
            'contact_us_show_captcha_flag' => $session->has(self::CONTACT_US_COUNT_KEY) && ($session->get(self::CONTACT_US_COUNT_KEY) > $numberOfAttempts),
        ));
    }

    /**
     * Get form errors and return array
     *
     * @param $form
     * @return array
     */
    private function getErrorMessages($form) : array
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
