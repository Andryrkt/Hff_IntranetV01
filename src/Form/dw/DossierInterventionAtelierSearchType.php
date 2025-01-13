<?php

namespace App\Form\dw;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class DossierInterventionAtelierSearchType extends AbstractType
{
    const INTERNE_EXTERNE = [
        'INTERNE' => 'INTERNE',
        'EXTERNE' => 'EXTERNE'
    ];


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    
        $builder
    
            ->add('idMateriel', NumberType::class, [
                'label' => 'Id Materiel',
                'required' => false,
            ])
            ->add('typeIntervention', 
            ChoiceType::class, 
            [
                'label' => "Type intervention",
                'choices' => self::INTERNE_EXTERNE,
                'placeholder' => '-- Choisir --',
                'required' => false,
                'data' => 'INTERNE'
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Demande Début',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Demande Fin',
                'required' => false,
            ])
            ->add('numParc', TextType::class, [
                'label' => "N° Parc",
                'required' => false
            ])
            ->add('numSerie', TextType::class, [
                'label' => "N° Serie",
                'required' => false
            ])

            ->add('numDit', TextType::class,
            [
                'label' => 'N° DIT',
                'required' => false
            ])
            ->add('numOr', NumberType::class,
            [
                'label' => 'N° Or',
                'required' => false
            ])
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
                'required' => false
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
