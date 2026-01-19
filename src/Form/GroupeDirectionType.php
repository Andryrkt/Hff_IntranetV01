<?php

namespace App\Form;

use App\Entity\GroupeDirection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupeDirectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matricule', null, [
                'label' => 'Numéro matricule',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le numéro matricule'
                ]
            ])
            ->add('nomPrenoms', null, [
                'label' => 'Nom & Prénoms',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom et prénoms'
                ]
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'property_path' => 'actif', // Utiliser la propriété directement pour que Symfony la convertisse correctement
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GroupeDirection::class
        ]);
    }
}