<?php

namespace App\Form\pol\ddd;

use App\Entity\ddd\DiagnosticPneu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiagnosticPneuType extends AbstractType
{
    const MOTIFS = [
        'Usure normale' => 'Usure normale',
        'Contrôle préventif' => 'Contrôle préventif',
        'Crevaison / perte de pression' => 'Crevaison / perte de pression',
        'Dommage mécanique' => 'Dommage mécanique',
        'Autre' => 'Autre',
    ];
    const POSITIONS_TRUCK = [
        'Avant gauche'       => 'avant_gauche',
        'Avant droite'       => 'avant_droite',
        'Arrière gauche'     => 'arriere_gauche',
        'Arrière droite'     => 'arriere_droite',
        'Remorque gauche'    => 'remorque_gauche',
        'Remorque droite'    => 'remorque_droite',
        'Semi-remorque gauche' => 'semi_gauche',
        'Semi-remorque droite' => 'semi_droite',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroSerie', TextType::class, [
                'label' => 'N/S pneu',
                'required' => true,
            ])
            ->add('coteDim', TextType::class, [
                'label' => 'Cote / dim',
                'required' => true,
            ])
            ->add('positionMachine', ChoiceType::class, [
                'label' => 'Position machine *',
                'choices' => self::POSITIONS_TRUCK,
                'placeholder' => 'Choisissez une position',
                'required' => true,
            ])
            ->add('motifChantier', ChoiceType::class, [
                'label' => 'Motif chantier *',
                'choices' => self::MOTIFS,
                'placeholder' => 'Sélectionnez un motif',
                'required' => true,
            ])
            ->add('diagnostic', ChoiceType::class, [
                'label' => 'Diagnostic atelier',
                'choices' => [
                    'Réparable'    => 'reparable',
                    'Remplacer'    => 'remplacer',
                    'Rechapable'   => 'rechapable',
                    'Détruit'      => 'detruit',
                ],
                'placeholder' => 'Choisir',
                'required' => false,
            ])
            ->add('observationAtelier', TextareaType::class, [
                'label' => 'Observation atelier',
                'required' => false,
                'attr' => ['rows' => 2],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DiagnosticPneu::class,
        ]);
    }
}
