<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 12.09.2018
 * Time: 17:21
 */
declare(strict_types=1);

namespace AppBundle\Controller\CustomerProfile;

use AppBundle\Form\CustomerType;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/profile/customer")
 * @Security("has_role('ROLE_CUSTOMER')")
 */
class EditController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/edit", name="customer_edit")
     * @Method({"GET", "POST"})
     * @Security("user.getID() == id")
     */
    public function editAction(Request $r, $id)
    {
        $locale = $r->getLocale();
        $customer = $this->getDoctrine()->getRepository("AppBundle:UserData")->findOneBy(['user' => $id]);

        if (!$customer) {
            throw $this->createNotFoundException('Unable to find Customer entity.');
        }

        $editForm = $this->createForm(CustomerType::class, $customer, [
            'locale' => $locale
        ]);
        $editForm->handleRequest($r);

        if ($editForm->isSubmitted()) {
            if (is_null($customer->getImageFile())
                && ($customer->getImage() === '' || is_null($customer->getImage()))) {
                $editForm->get('imageFile')->addError(new FormError("Please, upload your photo!"));
            }

            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                return new JsonResponse($this->prepareJsonArr(
                    true,
                    'Profile Saved'
                ));
            } else {
                $errors = $this->getErrorMessages($editForm);
                return new JsonResponse($this->prepareJsonArr(
                    false,
                    'Form errors.',
                    array('errors' => $errors)
                ));
            }
        }

        return $this->render('@App/user/customer/edit.html.twig', array(
            'customer' => $customer,
            'form' => $editForm->createView()
        ));
    }

    /**
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
