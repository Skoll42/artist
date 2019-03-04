<?php

namespace AppBundle\Form;

use AppBundle\Entity\ArtistData;
use AppBundle\Entity\UserData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    private $locale;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->locale = $options['locale'];

        $builder
            ->add('imageFile', FileType::class, array(
                'label' => "Profile Image: ",
                'required' => false,
            ))
            ->add('description', TextareaType::class, array(
                'label' => "About: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 1000
                )
            ))
            ->add('firstName', TextType::class, array(
                'label' => "First Name: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 255
                )
            ))
            ->add('lastName', TextType::class, array(
                'label' => "Last Name: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 255
                )
            ))
            ->add('phoneCode', HiddenType::class, array(
                'label' => "Phone Code: ",
                'required' => false
            ))
            ->add('phone', NumberType::class, array(
                'label' => "Phone Number: ",
                'required' => true,
                'attr' => array(
                    'onKeyPress' =>"if(this.value.length==11) return false;"
                )
            ))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'locale' => 'en',
            'data_class' => 'AppBundle\Entity\UserData',
            'mapped' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_customer';
    }


}
