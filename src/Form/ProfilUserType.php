<?php

namespace App\Form;

use App\Entity\ProfilUserEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('utilisateur', 
        TextType::class, 
        [
            'label' => "Nom d'utilisateur",
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 4]),
            ],
        ])
    ->add('profil', 
        ChoiceType::class, 
        [
            'label' => 'Rôle',
            'choices' => [
                'utilisateur' => 'utilisateur',
                'validateur' => 'validateur'
            ],
            'placeholder' => '-- Choisir une rôle --'
        ])
    ->add('app', 
        ChoiceType::class, 
        [
            'label' => 'Applications',
            'choices' => [
                'Dom' => 'DOM',
                'Badm' => 'BDM'
            ],
            'placeholder' => '-- Choisir une Application --'
        ])
    ->add('matricule', 
        NumberType::class,
        [
            'label' => 'Numero Matricule'
        ])
    ->add('email', 
        EmailType::class, [
            'label' => 'Email'
        ])
    
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfilUserEntity::class,
        ]);
    }


}