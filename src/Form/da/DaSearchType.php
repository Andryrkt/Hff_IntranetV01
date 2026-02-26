<?php

namespace App\Form\da;

use App\Entity\da\DaSearch;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use App\Traits\PrepareAgenceServiceTrait;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Controller\Traits\da\MarkupIconTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DaSearchType extends  AbstractType
{
    use PrepareAgenceServiceTrait;
    use MarkupIconTrait;

    private const STATUT_DA = [
        DemandeAppro::STATUT_VALIDE               => DemandeAppro::STATUT_VALIDE,
        DemandeAppro::STATUT_CLOTUREE             => DemandeAppro::STATUT_CLOTUREE,
        DemandeAppro::STATUT_SOUMIS_ATE           => DemandeAppro::STATUT_SOUMIS_ATE,
        DemandeAppro::STATUT_SOUMIS_APPRO         => DemandeAppro::STATUT_SOUMIS_APPRO,
        DemandeAppro::STATUT_DEMANDE_DEVIS        => DemandeAppro::STATUT_DEMANDE_DEVIS,
        DemandeAppro::STATUT_DEVIS_A_RELANCER     => DemandeAppro::STATUT_DEVIS_A_RELANCER,
        DemandeAppro::STATUT_EN_COURS_CREATION    => DemandeAppro::STATUT_EN_COURS_CREATION,
        DemandeAppro::STATUT_AUTORISER_EMETTEUR   => DemandeAppro::STATUT_AUTORISER_EMETTEUR,
        DemandeAppro::STATUT_EN_COURS_PROPOSITION => DemandeAppro::STATUT_EN_COURS_PROPOSITION,
    ];

    private const STATUT_BC = [
        DaSoumissionBc::STATUT_A_GENERER                => DaSoumissionBc::STATUT_A_GENERER,
        DaSoumissionBc::STATUT_A_EDITER                 => DaSoumissionBc::STATUT_A_EDITER,
        DaSoumissionBc::STATUT_A_SOUMETTRE_A_VALIDATION => DaSoumissionBc::STATUT_A_SOUMETTRE_A_VALIDATION,
        DaSoumissionBc::STATUT_A_VALIDER_DA             => DaSoumissionBc::STATUT_A_VALIDER_DA,
        DaSoumissionBc::STATUT_A_ENVOYER_AU_FOURNISSEUR => DaSoumissionBc::STATUT_A_ENVOYER_AU_FOURNISSEUR,
        DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR => DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR,
        DaSoumissionBc::STATUT_NON_DISPO                => DaSoumissionBc::STATUT_NON_DISPO,
        DaSoumissionBc::STATUT_SOUMISSION               => DaSoumissionBc::STATUT_SOUMISSION,
        DaSoumissionBc::STATUT_VALIDE                   => DaSoumissionBc::STATUT_VALIDE,
        DaSoumissionBc::STATUT_CLOTURE                  => DaSoumissionBc::STATUT_CLOTURE,
        DaSoumissionBc::STATUT_REFUSE                   => DaSoumissionBc::STATUT_REFUSE,
        DaSoumissionBc::STATUT_TOUS_LIVRES              => DaSoumissionBc::STATUT_TOUS_LIVRES,
        DaSoumissionBc::STATUT_PARTIELLEMENT_LIVRE      => DaSoumissionBc::STATUT_PARTIELLEMENT_LIVRE,
        DaSoumissionBc::STATUT_PARTIELLEMENT_DISPO      => DaSoumissionBc::STATUT_PARTIELLEMENT_DISPO,
        DaSoumissionBc::STATUT_COMPLET_NON_LIVRE        => DaSoumissionBc::STATUT_COMPLET_NON_LIVRE,
    ];

    private const STATUT = [
        'OR - ' . DitOrsSoumisAValidation::STATUT_VALIDE              => DitOrsSoumisAValidation::STATUT_VALIDE,
        'OR - ' . DitOrsSoumisAValidation::STATUT_A_VALIDER_CA        => DitOrsSoumisAValidation::STATUT_A_VALIDER_CA,
        'OR - ' . DitOrsSoumisAValidation::STATUT_A_VALIDER_CLIENT    => DitOrsSoumisAValidation::STATUT_A_VALIDER_CLIENT,
        'OR - ' . DitOrsSoumisAValidation::STATUT_REFUSE_CA           => DitOrsSoumisAValidation::STATUT_REFUSE_CA,
        'OR - ' . DitOrsSoumisAValidation::STATUT_REFUSE_CLIENT       => DitOrsSoumisAValidation::STATUT_REFUSE_CLIENT,
        'OR - ' . DitOrsSoumisAValidation::STATUT_REFUSE_DT           => DitOrsSoumisAValidation::STATUT_REFUSE_DT,
        'OR - ' . DitOrsSoumisAValidation::STATUT_SOUMIS_A_VALIDATION => DitOrsSoumisAValidation::STATUT_SOUMIS_A_VALIDATION,
        DemandeAppro::STATUT_DW_A_VALIDE                              => DemandeAppro::STATUT_DW_A_VALIDE,
        DemandeAppro::STATUT_DW_VALIDEE                               => DemandeAppro::STATUT_DW_VALIDEE,
        DemandeAppro::STATUT_DW_A_MODIFIER                            => DemandeAppro::STATUT_DW_A_MODIFIER,
        DemandeAppro::STATUT_DW_REFUSEE                               => DemandeAppro::STATUT_DW_REFUSEE,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statut = self::STATUT;
        ksort($statut);

        $statut_bc = self::STATUT_BC;
        ksort($statut_bc);

        $statut_da = self::STATUT_DA;
        ksort($statut_da);

        $type_achat = [
            'Demande d’approvisionnement via OR'      => DemandeAppro::TYPE_DA_AVEC_DIT,
            'Demande d’achat direct'                  => DemandeAppro::TYPE_DA_DIRECT,
            'Demande de réapprovisionnement mensuel'  => DemandeAppro::TYPE_DA_REAPPRO_MENSUEL,
            'Demande de réapprovisionnement ponctuel' => DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL
        ];

        $choices = $this->prepareAgenceServiceChoices($options['agenceServiceAutorises']);

        $agenceChoices = $choices['agenceChoices'];
        $serviceChoices = $choices['serviceChoices'];
        $serviceAttr = $choices['serviceAttr'];

        $builder
            ->add('numDit', TextType::class, [
                'label'         => 'n° DIT',
                'required'      => false
            ])
            ->add('numDa', TextType::class, [
                'label'         => 'n° DAP',
                'required'      => false
            ])
            ->add('demandeur', TextType::class, [
                'label'         => 'Demandeur',
                'required'      => false
            ])
            ->add('statutDA', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut de la DA',
                'choices'       => $statut_da,
                'required'      => false
            ])
            ->add('statutOR', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut',
                'choices'       => $statut,
                'required'      => false
            ])
            ->add('statutBC', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut du BC',
                'choices'       => $statut_bc,
                'required'      => false
            ])
            ->add('sortNbJours', ChoiceType::class, [
                'placeholder'   => '-- Choisir un tri --',
                'label'         => 'Tri par Nbr Jour(s)',
                'choices'       => [
                    'Ordre croissant'   => 'asc',
                    'Ordre décroissant' => 'desc',
                ],
                'required'      => false
            ])
            ->add(
                'codeCentrale',
                TextType::class,
                [
                    'label'    => false,
                    'required' => false
                ]
            )
            ->add(
                'desiCentrale',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'Centrale rattachée à la DA',
                    'required' => false
                ]
            )
            ->add('idMateriel', TextType::class, [
                'label'         => "N° Matériel",
                'required'      => false
            ])
            ->add('typeAchat', ChoiceType::class, [
                'label'         => 'Type de la demande d\'achat',
                'placeholder'   => '-- Choisir le type de la DA --',
                'choices'       => $type_achat,
                'required'      => false
            ])
            ->add('niveauUrgence', EntityType::class, [
                'label'         => 'Niveau d\'urgence',
                'label_html'    => true,
                'class'         => WorNiveauUrgence::class,
                'choice_label'  => 'description',
                'choice_value'  => 'description',
                'placeholder'   => '-- Choisir un niveau --',
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.description', 'DESC');
                },
                'attr' => [
                    'class' => 'niveauUrgence'
                ]
            ])
            ->add('dateDebutCreation', DateType::class, [
                'widget'        => 'single_text',
                'label'         => 'Date création (début)',
                'required'      => false,
            ])
            ->add('dateFinCreation', DateType::class, [
                'widget'        => 'single_text',
                'label'         => 'Date création (fin)',
                'required'      => false,
            ])
            ->add('dateDebutfinSouhaite', DateType::class, [
                'widget'        => 'single_text',
                'label'         => 'Date fin souhaitée (début)',
                'required'      => false,
            ])
            ->add('dateFinFinSouhaite', DateType::class, [
                'widget'        => 'single_text',
                'label'         => 'Date fin souhaitée (fin)',
                'required'      => false,
            ])
            // --- agenceEmetteur : ChoiceType ---
            ->add('agenceEmetteur', ChoiceType::class, [
                'label'       => 'Agence émetteur',
                'placeholder' => '-- Choisir une agence --',
                'required'    => false,
                'choices'     => $agenceChoices
            ])
            // --- serviceEmetteur : ChoiceType ---
            ->add('serviceEmetteur', ChoiceType::class, [
                'label'       => 'Service émetteur',
                'placeholder' => '-- Choisir une service --',
                'required'    => false,
                'choices'     => $serviceChoices,
                'choice_label' => function ($value) use ($options) {
                    // Retrouver le bon item et afficher service_code . ' ' . service_libelle
                    $item = $options['agenceServiceAutorises'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
            // --- agenceDebiteur : ChoiceType ---
            ->add('agenceDebiteur', ChoiceType::class, [
                'label'       => 'Agence débiteur',
                'placeholder' => '-- Choisir une agence --',
                'required'    => false,
                'choices'     => $agenceChoices
            ])
            // --- serviceDebiteur : ChoiceType ---
            ->add('serviceDebiteur', ChoiceType::class, [
                'label'       => 'Service débiteur',
                'placeholder' => '-- Choisir une service --',
                'required'    => false,
                'choices'     => $serviceChoices,
                'choice_label' => function ($value) use ($options) {
                    // Retrouver le bon item et afficher service_code . ' ' . service_libelle
                    $item = $options['agenceServiceAutorises'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'             => DaSearch::class,
            'agenceServiceAutorises' => [],
        ]);
    }
}
