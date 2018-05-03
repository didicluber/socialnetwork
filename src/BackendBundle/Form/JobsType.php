<?php

namespace BackendBundle\Form;

use BackendBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options){

        $builder
            ->add('titre', TextType::class, array(
                'label' => 'Titre de l\'annonce',
                'required' => 'required',
                'attr' => array(
                    'class' => 'form-control'
                )
            ))    ->add('description', TextareaType::class, array(
                'label' => 'Description de l\'offre',
                'required' => 'required',
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('user', EntityType::class,array(
                'class' => User::class,
                'choice_label' => 'entreprise',
                'label' => 'Entreprise',
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('image', FileType::class, array(
                'label' => 'Logo entreprise',
                'required' => false,
                'data_class' => null,
                'attr' => array(
                    'class' => 'form-image form-control'
                )
            ))

            ->add('Envoyer', SubmitType::class, array(
                "attr" => array(
                    "class" => "btn btn-success"
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
            'data_class' => 'BackendBundle\Entity\Jobs'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'backendbundle_jobs';
    }


}
