<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PaymentType extends AbstractType
{
    private $account;

    private $artist;

    private $verified;

    private $birthday;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->account = $options['data']['account'];
        $this->artist = $options['data']['artist'];
        $this->verified = $this->setVerified();
        $this->birthday = $this->setBirthday();

        global $kernel;

        $builder
            ->add('first_name', TextType::class, array(
                'label' => "First Name",
                'required' => true,
                'attr' => array(
                    'maxlength' => 100
                ),
            ))
            ->add('last_name', TextType::class, array(
                'label' => "Last Name",
                'required' => true,
                'attr' => array(
                    'maxlength' => 100
                ),
            ))
            ->add('birthday', BirthdayType::class, array(
                'label' => "Date Of Birth",
                'required' => true,
                'widget' => 'single_text',
                'attr' => array(
                    'maxlength' => 255
                ),
                'format' => 'dd-MM-yyyy',
                'constraints' => array(
                    new Constraints\LessThanOrEqual("-13 years"),
                ),
            ))
            ->add('country', TextType::class, array(
                'label' => "Country",
                'required' => true,
                'attr' => array(
                    'maxlength' => 255,
                    'readonly' => true,
                ),
                'disabled' => true
            ))
            ->add('city', TextType::class, array(
                'label' => "City",
                'required' => true,
                'attr' => array(
                    'maxlength' => 100
                ),
            ))
            ->add('postal_code', TextType::class, array(
                'label' => "Postal Code",
                'required' => true,
                'attr' => array(
                    'maxlength' => 4, // Maximum for Norway
                    'pattern' => '[0-9]{4}',
                ),
            ))
            ->add('address', TextType::class, array(
                'label' => "Address",
                'required' => true,
                'attr' => array(
                    'maxlength' => 200
                ),
            ));

        if ($kernel->getEnvironment() == 'dev' || $kernel->getEnvironment() == 'demo') {
            $builder = $builder->add('iban', TextType::class, array(
                'label' => "IBAN",
                'required' => true,
                'attr' => array(
                    'maxlength' => 34
                )
            ));
        } else {
            $builder = $builder->add('iban', TextType::class, array(
                'label' => "IBAN",
                'required' => true,
                'constraints' => array(
                    new Constraints\Iban(),
                ),
                'attr' => array(
                    'maxlength' => 34
                )
            ));
        }

        $builder = $builder->add('photo', FileType::class, array(
                'label' => "Photo: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 255
                ),
            ))
            ->add('email', HiddenType::class, array(
                'label' => "Email: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 255
                ),
                'disabled' => $this->verified
            ))
            ->add('phone', HiddenType::class, array(
                'label' => "Phone: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 10
                ),
                'disabled' => $this->verified
            ))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_extra_fields' => true,
            'account' => false,
            'artist' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_artist';
    }

    private function setVerified()
    {
        if($this->account) {
            if($this->account->legal_entity->verification->status == 'verified' || $this->account->legal_entity->verification->status == 'pending')
                return true;
        }

        return false;
    }

    private function setBirthday()
    {
        if($this->verified) {
           return $this->account->legal_entity->dob->month . '/' . $this->account->legal_entity->dob->day . '/' . $this->account->legal_entity->dob->year;
        }

        return false;
    }


}
