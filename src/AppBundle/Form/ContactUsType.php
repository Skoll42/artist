<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactUsType extends AbstractType
{
    private $locale;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->locale = $options['locale'];

        $builder
            ->add('name', TextType::class, array(
                'label' => "Full Name: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 255
                ),
            ))
            ->add('email', EmailType::class, array(
                'label' => "Email: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 255,
                ),
            ))
            ->add('feedback', TextareaType::class, array(
                'label' => "Message: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 1000
                ),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'locale' => 'en'
        ));
    }
}
