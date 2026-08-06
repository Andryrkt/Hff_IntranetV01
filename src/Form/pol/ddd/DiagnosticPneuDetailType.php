<?php

namespace App\Form\pol\ddd;

use App\Entity\ddd\DiagnosticPneu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiagnosticPneuDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('diagnostic', ChoiceType::class, [
                'label' => 'Diagnostic atelier',
                'choices' => [
                    'Réparable' => 'reparable',
                    'Remplacer' => 'remplacer',
                    'Rechapable' => 'rechapable',
                    'Détruit' => 'detruit',
                ],
                'placeholder' => 'Choisir',
                'required' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('observationAtelier', TextareaType::class, [
                'label' => 'Observation atelier',
                'required' => false,
                'attr' => ['rows' => 2, 'class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DiagnosticPneu::class,
        ]);
    }
}
