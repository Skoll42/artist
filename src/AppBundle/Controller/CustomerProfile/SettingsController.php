<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 12.09.2018
 * Time: 17:11
 */
declare(strict_types=1);

namespace AppBundle\Controller\CustomerProfile;

use AppBundle\Entity\User;
use AppBundle\Form\UserSettingsType;
use AppBundle\Traits\ControllerSupport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/profile/customer")
 * @Security("has_role('ROLE_CUSTOMER')")
 */
class SettingsController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/edit/settings", name="customer_edit_settings")
     * @Security("user.getID() == id")
     */
    public function editSettingsAction(Request $r, $id)
    {
        $customer = $this->getDoctrine()->getRepository("AppBundle:UserData")->findOneBy(['user' => $id]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        $user = $customer->getUser();

        $editForm = $this->createForm(UserSettingsType::class, $user);
        $editForm->handleRequest($r);

        //TODO Add email recheck and validation
        if ($editForm->isSubmitted()) {
            if (!$editForm->isValid()) {
                $errors = $this->getErrorMessages($editForm);
                return new JsonResponse($this->prepareJsonArr(false, 'Error', array('errors' => $errors)));
            }

            $data = $r->get('appbundle_user');

            if (!empty($data['new-artist-password']) && !empty($data['c-artist-password'])) {
                if (empty($data['current-artist-password'])) {
                    return new JsonResponse($this->prepareJsonArr(
                        false,
                        'Current Password can not be empty',
                        ['errors' => array(
                            'current-artist-password' => 'Current Password can not be empty'
                        )]
                    ));
                }

                if (!$this->isPasswordValid($this->getUser(), $data['current-artist-password'])
                    || empty($data['current-artist-password'])) {
                    return new JsonResponse($this->prepareJsonArr(
                        false,
                        'Current Password is not valid',
                        ['errors' => array(
                            'current-artist-password' => 'Current Password is not valid'
                        )]
                    ));
                }

                if (empty($data['new-artist-password'])) {
                    return new JsonResponse($this->prepareJsonArr(
                        false,
                        'New passwords can not be empty',
                        ['errors' => array(
                            'new-artist-password' => 'New passwords can not be empty',
                            'c-artist-password' => 'New passwords can not be empty'
                        )]
                    ));
                }

                if ($data['new-artist-password'] != $data['c-artist-password']) {
                    return new JsonResponse($this->prepareJsonArr(
                        false,
                        'New passwords is not equals',
                        ['errors' => array(
                            'new-artist-password' => 'New passwords is not equals',
                            'c-artist-password' => 'New passwords is not equals'
                        )]
                    ));
                }

                $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
                $user->setSalt($salt);
                $user->setPassword($this->encodePassword($user, $data['new-artist-password'], $salt));

                // It updates user password in DB
                $this->container->get('fos_user.user_manager')->updateUser($user, true);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($this->getUser());
            $em->flush();

            return new JsonResponse($this->prepareJsonArr(true, 'Settings saved.'));
        }

        return $this->render('@App/user/customer/settings.html.twig', array(
            'form' => $editForm->createView(),
        ));
    }

    /**
     * @param $form
     * @return array
     */
    private function getErrorMessages($form)
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

    /**
     * It checks whether user password valid or not
     *
     * @param User $user
     * @param string $rawPassword
     * @return bool
     */
    public function isPasswordValid($user, $rawPassword)
    {
        return $this->container->get('security.encoder_factory')->getEncoder($user)
            ->isPasswordValid($user->getPassword(), $rawPassword, $user->getSalt());
    }

    /**
     * It returns encoded password
     *
     * @param User $user
     * @param string $rawPassword
     * @param string $salt
     * @return string
     */
    private function encodePassword($user, $rawPassword, $salt)
    {
        return $this->container->get('security.encoder_factory')->getEncoder($user)
            ->encodePassword($rawPassword, $salt);
    }
}
