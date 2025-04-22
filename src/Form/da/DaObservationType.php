<?php

namespace App\Form\da;

use App\Entity\da\DaObservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaObservationType extends  AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('observation', TextareaType::class, [
                'label' => 'Observation',
                'attr' => [
                    'rows' => 5,
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DaObservation::class
        ]);
    }
}
