<?php

namespace App\Form\da;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\da\DaSearch;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DaSearchType extends  AbstractType
{
    private const STATUT_DA = [
        DemandeAppro::STATUT_VALIDE               => DemandeAppro::STATUT_VALIDE,
        DemandeAppro::STATUT_SOUMIS_ATE           => DemandeAppro::STATUT_SOUMIS_ATE,
        DemandeAppro::STATUT_SOUMIS_APPRO         => DemandeAppro::STATUT_SOUMIS_APPRO,
        DemandeAppro::STATUT_DEMANDE_DEVIS        => DemandeAppro::STATUT_DEMANDE_DEVIS,
        DemandeAppro::STATUT_DEVIS_A_RELANCER     => DemandeAppro::STATUT_DEVIS_A_RELANCER,
        DemandeAppro::STATUT_EN_COURS_CREATION    => DemandeAppro::STATUT_EN_COURS_CREATION,
        DemandeAppro::STATUT_AUTORISER_MODIF_ATE  => DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
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

    private const TYPE_ACHAT = [
        'DA Avec DIT' => DemandeAppro::TYPE_DA_AVEC_DIT,
        'DA Direct'   => DemandeAppro::TYPE_DA_DIRECT,
        'DA reappro'  => DemandeAppro::TYPE_DA_REAPPRO,
    ];

    private $agenceRepository;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->agenceRepository = $this->em->getRepository(Agence::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statut = self::STATUT;
        ksort($statut);

        $statut_bc = self::STATUT_BC;
        ksort($statut_bc);

        $statut_da = self::STATUT_DA;
        ksort($statut_da);

        $type_achat = self::TYPE_ACHAT;
        ksort($type_achat);

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
            ->add('agenceEmetteur', EntityType::class, [
                'label'         => "Agence émetteur",
                'class'         => Agence::class,
                'choice_label'  => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder'   => '-- Choisir une agence --',
                'required'      => false,
                'attr'          => ['class' => 'agenceEmetteur']
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data && $data->getAgenceEmetteur()) {
                    $services = $data->getAgenceEmetteur()->getServices();
                } else {
                    $services = [];
                }

                $form->add('serviceEmetteur', EntityType::class, [
                    'label' => "Service émetteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir un service --',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceEmetteur']
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $services = [];
                if (isset($data['agenceEmetteur']) && $data['agenceEmetteur']) {
                    $agenceId = $data['agenceEmetteur'];
                    $agence = $this->agenceRepository->find($agenceId);

                    if ($agence) {
                        $services = $agence->getServices();
                    }
                }

                $form->add('serviceEmetteur', EntityType::class, [
                    'label' => "Service Emetteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir un service --',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceEmetteur']
                ]);
            })
            ->add('agenceDebiteur', EntityType::class, [
                'label' => "Agence débiteur",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence --',
                'required' => false,
                'attr' => ['class' => 'agenceDebiteur']
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data && $data->getAgenceDebiteur()) {
                    $services = $data->getAgenceDebiteur()->getServices();
                } else {
                    $services = [];
                }

                $form->add('serviceDebiteur', EntityType::class, [
                    'label' => "Service débiteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir un service --',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceDebiteur']
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if (isset($data['agenceDebiteur']) && $data['agenceDebiteur']) {
                    $agenceId = $data['agenceDebiteur'];
                    $agence = $this->agenceRepository->find($agenceId);

                    if ($agence) {
                        $services = $agence->getServices();
                    } else {
                        $services = [];
                    }
                } else {
                    $services = [];
                }

                $form->add('serviceDebiteur', EntityType::class, [
                    'label' => "Service débiteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir un service --',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceDebiteur']
                ]);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DaSearch::class,
        ]);
    }
}
