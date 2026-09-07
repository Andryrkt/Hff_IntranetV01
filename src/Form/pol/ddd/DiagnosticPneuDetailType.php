<?php

namespace App\Form\pol\ddd;

use App\Entity\admin\Agence;
use App\Entity\ddd\DiagnosticPneu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DiagnosticPneuDetailType extends AbstractType
{
    private const DIAGNOSTICS = [
        'Réparable' => 'reparable',
        'A Remplacer' => 'a remplacer',
        'Rechapable' => 'rechapable',
        'Détruit' => 'detruit',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('diagnostic', ChoiceType::class, [
                'label' => 'Diagnostic atelier',
                'choices' => self::DIAGNOSTICS,
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
