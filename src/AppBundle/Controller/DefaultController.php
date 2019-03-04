<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Charge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class DefaultController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $r)
    {
        $locale = $r->getLocale();
        // reset token
        $token = $r->query->get('reset');

        $resetPassModal = '';

        if (!empty($token)) {
            $resetPassModal = $this->resetPassword($r, $token);
        }

        return $this->render('@App/index.html.twig', [
            'reset' => (bool)$r->query->get('reset'),
            'resetPasswordModal' => $resetPassModal,
            'categories' => $this->getAllCategories($locale),
            'themes' => $this->getAllThemes($locale),
            'artists' => $this->getBestArtists(12, $r->getLocale())
        ]);
    }

    /**
     * Get all categories
     *
     * @param $locale
     * @return mixed
     */
    private function getAllCategories($locale)
    {
        return $this->getRepo('CategoryTransliteration')->findCategoriesByLanguageCode($locale);
    }

    /**
     * Get all themes
     *
     * @param $locale
     * @return mixed
     */
    private function getAllThemes($locale)
    {
        return $this->getRepo('ThemeTransliteration')->findThemesByLanguageCode($locale);
    }

    /**
     * Get best artists
     *
     * @param $limit
     * @param $locale
     * @return mixed
     */
    private function getBestArtists($limit, $locale)
    {
        $artists = [];

        $result = $this->getRepo('ArtistData')->getBestArtist($limit);

        foreach ($result as $artist) {
            $artist->setCategory($this->getRepo('CategoryTransliteration')->getUserCategory($artist->getUser(), $locale));
            $artists[] = $artist;
        }

        return $artists;
    }

    /**
     * Embed action for menu
     *
     * @param Request $r
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction(Request $r)
    {
        $stripeStatusArtist = 'false';
        $editProfile = 'false';
        $artist = '';
        $customer = '';
        $user = $this->getUser();

        if ($user && $user->getId()) {
            $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $user->getId()]);
            $customer = $this->getRepo("UserData")->findOneBy(['user' => $user->getId()]);
            $stripeStatusArtist = ($artist && $artist->getStripeId()) ? 'true' : 'false';
            $editProfile = ($artist && !empty($artist->getFirstName())
                            && !empty($artist->getLastName())
                            && !empty($artist->getLocation())
                            && !empty($artist->getPhone()))
                            ? 'true' : 'false';
        }

        $locale = $r->getLocale();

        $stack = $this->get('request_stack');
        $langArr = $this->getLangUrls($stack->getMasterRequest());

        return $this->render('@App/controls/menu.html.twig', [
            "locale" => $locale,
            "langArr" => $langArr,
            "stripe_status" => $stripeStatusArtist,
            "edit_profile" => $editProfile,
            "artist" => $artist,
            "customer" => $customer
        ]);
    }

    public function getLangUrls(Request $r): array
    {
        $enLink = '';
        $noLink = '';

        $locale = $r->getLocale();
        $page = $r->attributes->get('_route');
        $routeParams = $r->attributes->get('_route_params');
        $queryParams = $r->query->all();


        if ($locale === 'en') {
            $noLink = $this->generateUrlWithParams($page, $routeParams, $queryParams, 'no');
        } elseif ($locale === 'no') {
            $enLink = $this->generateUrlWithParams($page, $routeParams, $queryParams, 'en');
        }

        return array('en' => $enLink, 'no' => $noLink);
    }

    public function generateUrlWithParams(string $page, array $routeParams, array $queryParams, string $locale)
    {
        $localeArr = array('_locale' => $locale);
        return $this->generateUrl($page, array_merge($routeParams, $localeArr, $queryParams));
    }

    /**
     * @Route("/profile/{id}", name="profile")
     */
    public function profileAction(Request $r, User $user)
    {
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if ($user->hasRole('ROLE_ARTIST')) {
            return $this->redirect($this->generateUrl('artist_edit', ['id'=> $user->getId()]), 301);
        }

        return $this->redirect($this->generateUrl('customer_edit', ['id'=> $user->getId()]), 301);
    }

    /**
     * @Route("/public/profile/{id}", name="public_profile")
     */
    public function publicProfileAction(Request $r, User $user)
    {
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if ($user->hasRole('ROLE_ARTIST')) {
            return $this->redirect($this->generateUrl('artist_profile_public', ['id'=> $user->getId()]), 301);
        }

        return $this->redirect($this->generateUrl('customer_profile_public', ['id'=> $user->getId()]), 301);
    }

    /**
     * @Route("/communication/{id}/to/{id_with}", name="communication_url")
     */
    public function communicationUrlAction(Request $r, $id, $id_with)
    {
        $user = $this->getRepo('User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if ($user->hasRole('ROLE_ARTIST')) {
            return $this->redirect($this->generateUrl('artist_communication_chat', [
                'id'=> $id,
                'id_with' => $id_with
            ]), 301);
        }

        return $this->redirect($this->generateUrl('customer_communication_chat', [
            'id'=> $id,
            'id_with' => $id_with
        ]), 301);
    }

    private function resetPassword(Request $r, $token)
    {
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');
        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            return new RedirectResponse($this->container->get('router')->generate('index'));
        }

        $event = new GetResponseUserEvent($user, $r);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($r);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $r);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
            }

            $dispatcher->dispatch(
                FOSUserEvents::RESETTING_RESET_COMPLETED,
                new FilterUserResponseEvent($user, $r, $response)
            );

            return $response;
        }

        return $this->container->get('twig')->render('@App/Resetting/reset.html.twig', array(
            'token' => $token,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/mobile", name="mobile")
     */
    public function mobileAction(Request $r)
    {
        return $this->render('@App/mobile.html.twig');
    }

    /**
     * @Route("/mobile/menu", name="mobile_menu")
     */
    public function mobileMenuAction(Request $r)
    {
        return $this->render('@App/mobile_menu.html.twig');
    }

    /**
     * @Route("/coming-soon", name="coming_soon")
     */
    //AR-512
    /*public function locationBookingModalAction(Request $r)
    {
        $html = $this->renderView('@App/modals/comming_soon.html.twig');

        return new JsonResponse($this->prepareJsonArr(true, 'Coming Soon', ['html' => $html]));
    }*/

    /**
     * @Route("/cant-book/{id}/artist", name="cant_book")
     */
    public function cantBookModalAction(Request $r, $id)
    {
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);
        if (!$artist) {
            return new JsonResponse($this->prepareJsonArr(false, 'Artist not found', []));
        }

        $html = $this->renderView('@App/modals/cant_book.html.twig',  ['artist' => $artist]);

        return new JsonResponse($this->prepareJsonArr(true, 'Can\'t Book', [
            'html' => $html,
        ]));
    }

    /**
     * @Route("/preregister/{id}/artist", name="preregister")
     */
    public function preregister(Request $r, $id)
    {
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);
        if (!$artist) {
            return new JsonResponse($this->prepareJsonArr(false, 'Artist not found', []));
        }

        $html = $this->renderView('@App/modals/preregister.html.twig',  ['artist' => $artist]);

        return new JsonResponse($this->prepareJsonArr(true, 'Preregister', [
            'html' => $html,
        ]));
    }

    /**
     * @Route("/privacy-policy", name="privacy_policy")
     */
    public function privacyPolicyAction(Request $r)
    {
        return $this->render('@App/pages/policy.html.twig');
    }

    /**
     * @Route("/terms-and-conditions", name="terms_and_conditions")
     */
    public function termAndConditionAction(Request $r)
    {
        return $this->render('@App/pages/terms_and_conditions.html.twig');
    }
}
