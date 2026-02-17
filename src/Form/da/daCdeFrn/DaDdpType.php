<?php

namespace App\Form\da\daCdeFrn;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaDdpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ddp', ChoiceType::class, [
                'choices'  => [
                    'Avance' => 'avance',
                    'Régule' => 'regule'
                ],
                'expanded' => true, // pour afficher des boutons radio
                'multiple' => false, // un seul choix possible
                'required' => true,
                'label' => 'Demande de paiement',
            ])
            ->add('commande_id', HiddenType::class)
            ->add('da_id', HiddenType::class)
            ->add('num_or', HiddenType::class)
            ->add('type_da', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
