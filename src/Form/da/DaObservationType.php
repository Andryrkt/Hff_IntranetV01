<?php

namespace App\Form\da;

use App\Entity\da\DaObservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaObservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statutChange', CheckboxType::class, [
                'label' => 'Autoriser l\'ATELIER à modifier',
                'required' => false
            ])
            ->add('observation', TextareaType::class, [
                'label' => 'Observation',
                'attr' => [
                    'rows' => 5,
                ],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DaObservation::class,
        ]);
    }
}
