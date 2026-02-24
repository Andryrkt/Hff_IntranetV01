<?php

namespace App\Form\magasin\devis;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MotifStopRelanceType extends AbstractType
{
    const MOTIF = [
        "Prix excessif" => "pe",
        "Achat direct en import du client" => "adic",
        "Juste pour comparaison mais pas d'achat" => "jpcma",
        "Hors budget" => "hb",
        "Pièce non dispo" => "pnd"
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return $builder
            ->add('choixMotif', ChoiceType::class, [
                'label' => 'Choisir un motif',
                'choices' => self::MOTIF,
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
