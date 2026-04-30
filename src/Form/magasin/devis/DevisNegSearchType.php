<?php

namespace App\Form\magasin\devis;

use App\Constants\Magasin\Devis\StatutBcNegConstant;
use App\Constants\Magasin\Devis\StatutDevisNegContant;
use App\Dto\Magasin\Devis\DevisSearchDto;
use App\Form\common\AgenceServiceType;
use App\Form\common\DateRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisNegSearchType extends AbstractType
{
    private const STATUT_IPS = [
        '--' => '--',
        'AC' => 'AC',
        'DE' => 'DE',
        'RE' => 'RE',
        'TR' => 'TR',
    ];

    private const FILTER_RELANCE = [
        'À relancer' => 'A_RELANCER',
        '3 relances terminées (Non stoppé)' => '3_RELANCES_OK',
        '3 relances terminées (Stoppé)' => '3_RELANCES_STOP',
        'Stoppé avant relance 1' => 'STOP_AVANT_R1',
        'Stoppé à la relance 1' => 'STOP_R1',
        'Stoppé à la relance 2' => 'STOP_R2',
        'Relance 1 en cours' => 'R1_EN_COURS',
        'Relance 2 en cours' => 'R2_EN_COURS',
        'Relance 3 en cours' => 'R3_EN_COURS',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroPO', TextType::class, [
                'label' => 'PO/BC client',
                'required' => false,
            ])
            ->add('numeroDevis', TextType::class, [
                'label' => 'Numéro de devis',
                'required' => false,
            ])
            ->add('codeClient', TextType::class, [
                'label' => 'code Client',
                'required' => false,
            ])
            ->add('Operateur', TextType::class, [
                'label' => 'Soumis par',
                'required' => false,
            ])
            ->add('CreePar', TextType::class, [
                'label' => 'Crée par',
                'required' => false,
            ])
            ->add('statutDw', ChoiceType::class, [
                'label' => 'Statut devis',
                'placeholder' => '-- Choisir le choix --',
                'choices' => StatutDevisNegContant::STATUTS_DW,
                'required' => false,
            ])
            ->add('statutBc', ChoiceType::class, [
                'label' => 'Statut BC',
                'placeholder' => '-- Choisir le choix --',
                'choices' => StatutBcNegConstant::STATUTS_BC,
                'required' => false,
            ])
            ->add('statutIps', ChoiceType::class, [
                'label' => 'Position IPS',
                'placeholder' => '-- Choisir le choix --',
                'choices' => self::STATUT_IPS,
                'required' => false,
            ])
            ->add('filterRelance', ChoiceType::class, [
                'label' => 'Filtrer par relance',
                'placeholder' => '-- Choisir le choix --',
                'choices' => self::FILTER_RELANCE,
                'required' => false,
                // 'data' => $options['data']->getFilterRelance(),
            ])
            ->add('emetteur', AgenceServiceType::class, [
                'label' => false,
                'required' => false,
                'agence_label' => 'Agence Emetteur',
                'service_label' => 'Service Emetteur',
                'agence_placeholder' => '-- Agence Emetteur --',
                'service_placeholder' => '-- Service Emetteur --',
                'em' => $options['em'] ?? null,
                // 'data_agence' => $options['data']->getEmetteur()['agence'] ?? null,
                // 'data_service' => $options['data']->getEmetteur()['service'] ?? null,
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
                // 'data_date_debut' => $options['data']->getDateCreation()['debut'] ?? null,
                // 'data_date_fin' => $options['data']->getDateCreation()['fin'] ?? null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DevisSearchDto::class,
            'em' => null,
        ]);
    }
}
