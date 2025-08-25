<?php

namespace App\Form\da;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaPropositionValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prixUnitaire', HiddenType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('refsValide', HiddenType::class, [
                'label' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 'allow_extra_fields' => true,
            // 'data_class' => ,
        ]);
    }
}
