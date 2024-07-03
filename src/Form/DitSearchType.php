<?php

// src/Form/SearchType.php
namespace App\Form;

use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\StatutDemande;
use App\Entity\TypeMouvement;
use App\Entity\WorTypeDocument;
use App\Entity\WorNiveauUrgence;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class DitSearchType extends AbstractType
{
    const INTERNE_EXTERNE = [
        'INTERNE' => 'I',
        'EXTERNE' => 'E'
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('niveauUrgence', EntityType::class, [
            'label' => 'Niveau d\'urgence',
            'class' => WorNiveauUrgence::class,
            'choice_label' => 'description',
            'placeholder' => '-- Choisir une niveau--',
            'required' => false,
            'attr' => [
                'class' => 'niveauUrgence'
            ]
        ])
        ->add('statut', EntityType::class, [
            'label' => 'Statut',
            'class' => StatutDemande::class,
            'choice_label' => 'description',
            'placeholder' => '-- Choisir une niveau--',
            'required' => false,
            'attr' => [
                'class' => 'statut'
            ]
        ])
            ->add('idMateriel', NumberType::class, [
                'label' => 'Id Materiel',
                'required' => false,
            ])
            ->add('typeDocument', EntityType::class, [
                'label' => 'Type de Document',
                'class' => WorTypeDocument::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir une type de document--',
                'required' => false,
            ])
            ->add('internetExterne', 
            ChoiceType::class, 
            [
                'label' => "Interne et Externe",
                'choices' => self::INTERNE_EXTERNE,
                'placeholder' => '-- Choisir --',
               'required' => false,
               'attr' => [ 'class' => 'interneExterne']
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
            ->add('agenceEmetteur', EntityType::class, [
                'label' => "Agence Emetteur",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence--',
                'required' => false,
            ])
            ->add('serviceEmetteur', EntityType::class, [
                'label' => "Service Emetteur",
                'class' => Service::class,
                'choice_label' => function (Service $service): string {
                    return $service->getCodeService() . ' ' . $service->getLibelleService();
                },
                'placeholder' => '-- Choisir une service--',
                'required' => false,
            ])
            ->add('agenceDebiteur', EntityType::class, [
                'label' => "Agence Debiteur",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence--',
                'required' => false,
            ])
            ->add('serviceDebiteur', EntityType::class, [
                'label' => "Service Debiteur",
                'class' => Service::class,
                'choice_label' => function (Service $service): string {
                    return $service->getCodeService() . ' ' . $service->getLibelleService();
                },
                'placeholder' => '-- Choisir une service--',
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
