<?php

namespace Form\da;

use App\Entity\da\DemandeApproParent;
use App\Form\da\DapLineAffectationFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaAffectationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('demandeApproParentLines', CollectionType::class, [
            'label'        => false,
            'entry_type'   => DapLineAffectationFormType::class,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype'    => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeApproParent::class,
        ]);
    }
}
