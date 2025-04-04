<?php

namespace App\Form\da;

use App\Entity\da\DemandeApproLRCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeApproLRCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('DALR', CollectionType::class, [
            'label'         => false,
            'entry_type'    => DemandeApproLRFormType::class, // Le formulaire enfant
            'allow_add'     => true, // Autoriser l'ajout d'éléments
            'allow_delete'  => true, // Autoriser la suppression d'éléments
            'by_reference'  => false, // Important pour fonctionner avec des objets
            'prototype'     => true, // Permet d'avoir un prototype en JS
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeApproLRCollection::class,
        ]);
    }
}
