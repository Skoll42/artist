<?php

namespace AppBundle\Form;

use AppBundle\Entity\ArtistData;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtistType extends AbstractType
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
                'label' => "First Name: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 255
                ),
            ))
            ->add('imageFile', FileType::class, array(
                'label' => "Profile Image: ",
                'required' => false,
                'attr' => array(
                    'maxlength' => 255
                ),
            ))
            ->add('category', EntityType::class, array(
                'label' => "Category",
                'required' => true,
                'class' => 'AppBundle:Category',
                'choice_label' => function ($category) {
                    return $category->getDisplayName($this->locale);
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->getCategories();
                }))
            ->add('themes', EntityType::class, array(
                'label' => "Events",
                'class' => 'AppBundle:Theme',
                'required' => true,
                'multiple' => true,
                'choice_label' => function ($category) {
                    return $category->getDisplayName($this->locale);
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->getAllByLocale();
                }))
            ->add('description', TextareaType::class, array(
                'label' => "Description: ",
                'required' => false,
                'required' => true,
                'attr' => array(
                    'maxlength' => 1000
                ),
            ))
            ->add('tags', EntityType::class, array(
                'label' => "Tags",
                'class' => 'AppBundle:Tag',
                'multiple' => true,
                'required' => true,
                'choice_label' => 'name',
                'attr' => array(
                    'maxlength' => 255
                ),
                'query_builder' => function (EntityRepository $er) {
                    return $er->getAll();
                }))
            ->add('location', EntityType::class, array(
                'label' => "Location: ",
                'class' => 'AppBundle:Location',
                'choice_label' => 'name',
                'required' => true,
                'attr' => array(
                    'maxlength' => 255
                ),
            ))
            ->add('price', NumberType::class, array(
                'label' => "Price: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 12
                ),
            ))
            ->add('time', TextType::class, array(
                'label' => "Performance duration: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 255
                ),
            ))
            ->add('requirements', EntityType::class, array(
                'label' => true,
                'class' => 'AppBundle:Requirement',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'choice_label' => function ($requirement) {
                    return $requirement->getDisplayName($this->locale);
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->getRequirement();
                }))
            ->add('otherRequirements', TextType::class, array(
                'label' => "Other Requirements: ",
                'required' => false,
                'attr' => array(
                    'maxlength' => 255
                ),
            ))
            ->add('first_name', TextType::class, array(
                'label' => "First Name: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 100
                ),
            ))
            ->add('last_name', TextType::class, array(
                'label' => "Last Name: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 100
                ),
            ))
            ->add('age', TextType::class, array(
                'label' => "Age: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 4
                ),
            ))
            ->add('phoneCode', HiddenType::class, array(
                'label' => "Phone Code: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 5,
                    'minlength' => 2
                ),
            ))
            ->add('phone', TextType::class, array(
                'label' => "Phone Number: ",
                'required' => true,
                'attr' => array(
                    'maxlength' => 10,
                    'minlength' => 4
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
            'data_class' => ArtistData::class,
            'locale' => 'en'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_artist';
    }
}
