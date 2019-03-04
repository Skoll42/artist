<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 12.09.2018
 * Time: 16:43
 */
declare(strict_types=1);

namespace AppBundle\Controller\ArtistProfile;

use AppBundle\Form\PaymentType;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("has_role('ROLE_ARTIST')")
 */
class PaymentDetailsController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/edit/payment", name="artist_edit_payment")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     */
    public function editPaymentAction(Request $r, $id)
    {
        $saved = false;
        $stripeStatus = 'false';
        $account = null;
        $balance = [];
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        if ($artist
            && empty($artist->getFirstName())
            && empty($artist->getLastName())
            && empty($artist->getLocation())
            && empty($artist->getPhone())) {
            throw $this->createNotFoundException('You should enter you profile details first.');
        }

        $stripeAccount = $this->get('stripe.account');
        if ($artist->getStripeId()) {
            $account = $stripeAccount->retrieve($artist->getStripeId());
            $balance = $stripeAccount->retrieveBalance($account->id);
        }

        $form = $this->createForm(PaymentType::class, array(
            'account' => $account,
            'artist' => $artist
        ));
        $form->handleRequest($r);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $errors['param'] = $this->getErrorMessages($form);

                $stripeStatus = ($artist->getStripeId()) ? 'true' : 'false';

                return $this->render('@App/user/artist/payment.html.twig', [
                    'form' => $form->createView(),
                    'artist' => $artist,
                    'account' => $account,
                    'errors' => $errors,
                    'stripe_status' => $stripeStatus,
                    'edit_profile' => 'true'
                ]);
            }
            $data = $form->getData();
            $data['country'] = "NO";
            $data['currency'] = "NOK";
            $data['type'] = "custom";

            if (!$account) {
                $result = $this->createAccount($stripeAccount, $data, $artist);

                if (!$result['success']) {
                    return $this->render('@App/user/artist/payment.html.twig', [
                        'form' => $form->createView(),
                        'artist' => $artist,
                        'account' => $account,
                        'errors' => $result['errors'],
                        'stripe_status' => $stripeStatus,
                        'edit_profile' => 'true'
                    ]);
                }
                $account = $result['data'];
            }

            $result = $this->updateAccount($stripeAccount, $data, $account);

            if (!$result['success']) {
                return $this->render('@App/user/artist/payment.html.twig', [
                    'form' => $form->createView(),
                    'artist' => $artist,
                    'account' => $account,
                    'balance' => $balance,
                    'errors' => $result['errors'],
                    'stripe_status' => $stripeStatus,
                    'edit_profile' => 'true'
                ]);
            }
            $account = $result['data'];

            $saved = true;
        }

        if ($artist->getStripeId()) {
            if ($saved) {
                //get the Doctrine Manager to update the Artist Data entity (update visibility) in DB
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($artist);

                $visibleArtist = $manager->find('AppBundle:ArtistData', $artist->getId());
                $visibleArtist->setIsVisible(true);
                $manager->flush();
            }
            $stripeStatus = 'true';
        }

        return $this->render('@App/user/artist/payment.html.twig', [
            'form' => $form->createView(),
            'artist' => $artist,
            'account' => $account,
            'balance' => $balance,
            'saved' => $saved,
            'stripe_status' => $stripeStatus,
            'edit_profile' => 'true',
            'errors' => [
                'message' => '',
                'param' => ''
            ]
        ]);
    }

    /**
     * Create Stripe account for Artist
     *
     * @param $stripeAccount
     * @param $data
     * @param $artist
     * @return mixed
     */
    private function createAccount($stripeAccount, $data, $artist)
    {
        $result = $stripeAccount->create($data);

        if ($result['success']) {
            $account = $result['data'];
            $artist->setStripeId($account->id);
            $em = $this->getDoctrine()->getManager();
            $em->persist($artist);
            $em->flush();
        }

        return $result;
    }

    /**
     * Upload and attachment legal document at account
     *
     * @param $stripeAccount
     * @param $data
     * @param $account
     * @return mixed
     */
    private function updateAccount($stripeAccount, $data, $account)
    {
        $result = null;
        if ($account->legal_entity->verification->status == 'unverified'
            || $account->legal_entity->verification->status == 'pending') {
            $result = $stripeAccount->documentUploaded(
                $account->id,
                $data['photo'],
                $this->getParameter('tmp_directory')
            );
            if (!$result['success']) {
                return $result;
            }

            $result = $stripeAccount->documentAttachment($account->id, $result['uploadedFile']);
            if (!$result['success']) {
                return $result;
            }
        }

        $result = $stripeAccount->createExternalAccount($account->id, $data, $data['currency'], $data['country']);
        if (!$result['success']) {
            return $result;
        }

        return $stripeAccount->updateAddress($account->id, $data);
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

        foreach ($form->getErrors() as $error) {
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
