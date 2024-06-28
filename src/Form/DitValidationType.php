<?php

// src/Form/SearchType.php
namespace App\Form;


use App\Entity\DemandeIntervention;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class DitValidationType extends AbstractType
{
    

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('observationDirectionTechnique',
        TextareaType::class,
        [
            'label' => 'Observation D.T',
            'required' => false,
            'attr' => [
                'rows' => 5,  
              ],
        ])
        ;
    }

    // public function getParent()
    // {
    //     return demandeInterventionType::class;
    // }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeIntervention::class,
        ]);
    }
}
