<?php

namespace App\Form\magasin\devis;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\common\AgenceServiceType;
use App\Form\common\DateRangeType;

class DevisMagasinSearchType extends AbstractType
{
    private const STATUT_DW = [
        'Prix à confirmer' => 'Prix à confirmer',
        'Prix validé magasin' => 'Prix validé magasin',
        'Prix refusé magasin' => 'Prix refusé magasin',
        'Demande refusée par le PM' => 'Demande refusée par le PM',
        'A valider chef d\'agence' => 'A valider chef d\'agence'
    ];

    private const STATUT_IPS = [
        '--' => '--',
        'AC' => 'AC',
        'DE' => 'DE',
        'RE' => 'RE',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroDevis', TextType::class, [
                'label' => 'Numéro de devis',
                'required' => false,
            ])
            ->add('codeClient', TextType::class, [
                'label' => 'code Client',
                'required' => false
            ])
            ->add('Operateur', TextType::class, [
                'label' => 'Operateur',
                'required' => false
            ])
            ->add('statutDw', ChoiceType::class, [
                'label' => 'statut docuware',
                'placeholder' => '-- Choisir le choix --',
                'choices' => self::STATUT_DW,
                'required' => false
            ])
            ->add('statutIps', ChoiceType::class, [
                'label' => 'statut IPS',
                'placeholder' => '-- Choisir le choix --',
                'choices' => self::STATUT_IPS,
                'required' => false
            ])
            ->add('emetteur', AgenceServiceType::class, [
                'label' => false,
                'required' => false,
                'agence_label' => 'Agence Emetteur',
                'service_label' => 'Service Emetteur',
                'agence_placeholder' => '-- Agence Emetteur --',
                'service_placeholder' => '-- Service Emetteur --',
                'em' => $options['em'] ?? null,
            ])
            // ->add('debitteur', AgenceServiceType::class, [
            //     'label' => false,
            //     'required' => false,
            //     'agence_label' => 'Agence Debiteur',
            //     'service_label' => 'Service Debiteur',
            //     'agence_placeholder' => '-- Agence Debiteur --',
            //     'service_placeholder' => '-- Service Debiteur --',
            // ])
            ->add('dateCreation', DateRangeType::class, [
                'label' => false,
                'debut_label' => 'Date création (début)',
                'fin_label' => 'Date création (fin)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'em' => null,
        ]);
    }
}
