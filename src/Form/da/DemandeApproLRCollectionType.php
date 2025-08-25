<?php

namespace App\Form\da;

use Symfony\Component\Form\AbstractType;
use App\Entity\da\DemandeApproLRCollection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DemandeApproLRCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statutChange', CheckboxType::class, [
                'label' => 'Passer la main à l\'APPRO',
                'mapped' => false,
                'required' => false
            ])
            ->add('estValidee', CheckboxType::class, [
                'label' => 'Cocher pour valider et generer le bon d\'achat',
                'required' => false,
            ])
            ->add('observation', TextareaType::class, [
                'label' => 'Observation à la proposition / validation',
                'attr' => [
                    'rows' => 5,
                ],
                'required' => false
            ])
            ->add('DALR', CollectionType::class, [
                'label'         => false,
                'entry_type'    => DemandeApproLRFormType::class, // Le formulaire enfant
                'allow_add'     => true, // Autoriser l'ajout d'éléments
                'allow_delete'  => true, // Autoriser la suppression d'éléments
                'by_reference'  => false, // Important pour fonctionner avec des objets
                'prototype'     => true, // Permet d'avoir un prototype en JS
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeApproLRCollection::class,
        ]);
    }
}
