<?php

namespace App\Form\bordereau;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BordereauSearchType extends AbstractType
{

    const CHOIX = [
        'TOUT' => 'TOUT',
        'ECART' => 'ECART',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('choix', ChoiceType::class, [
                'label' => "Choix",
                'choices' => self::CHOIX,
                'attr' => ['class' => 'choix'],
                'data' => 'TOUT'
            ])
            ->add('numInv', TextType::class, [
                'label' => "Numero inventaire",
                'disabled' => true
               
            ]);
    }
}
