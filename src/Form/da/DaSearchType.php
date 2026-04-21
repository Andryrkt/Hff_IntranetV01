<?php

namespace App\Form\da;

use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
use App\Controller\Traits\da\MarkupIconTrait;
use App\Entity\admin\Agence;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\Service;
use App\Entity\da\DaSearch;
use App\Entity\da\DemandeAppro;
use App\Repository\admin\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaSearchType extends  AbstractType
{
    use MarkupIconTrait;

    private $agenceRepository;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->agenceRepository = $this->em->getRepository(Agence::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statut_or = StatutOrConstant::STATUT_OR;
        ksort($statut_or);

        if ($options['estAppro']) {
            $statut_da = StatutDaConstant::STATUT_DA;
            $statut_bc = StatutBcConstant::STATUT_BC;
        } else {
            $statut_da = StatutDaConstant::STATUT_DA_PAS_APPRO_NI_ADMIN;
            $statut_bc = StatutBcConstant::STATUT_BC_PAS_APPRO_NI_ADMIN;
        }


        $type_achat = [
            'Demande d’approvisionnement via OR'      => DemandeAppro::TYPE_DA_AVEC_DIT,
            'Demande d’achat direct'                  => DemandeAppro::TYPE_DA_DIRECT,
            'Demande de réapprovisionnement mensuel'  => DemandeAppro::TYPE_DA_REAPPRO_MENSUEL,
            'Demande de réapprovisionnement ponctuel' => DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL
        ];

        $isApproUser = $options['codeAgence'] == '80' && $options['codeService'] == 'APP';
        $statutsDA_Traiter_Appro = [
            StatutDaConstant::STATUT_SOUMIS_APPRO,
            StatutDaConstant::STATUT_DEMANDE_DEVIS,
            StatutDaConstant::STATUT_DEVIS_A_RELANCER,
            StatutDaConstant::STATUT_EN_COURS_PROPOSITION
        ];
        $statutsBC_Traiter_Appro = [
            StatutBcConstant::STATUT_PAS_DANS_BC,
            StatutBcConstant::STATUT_PAS_DANS_OR_CESSION,
            StatutBcConstant::STATUT_A_GENERER,
            StatutBcConstant::STATUT_CESSION_A_GENERER,
            StatutBcConstant::STATUT_A_EDITER,
            StatutBcConstant::STATUT_A_SOUMETTRE_A_VALIDATION,
            StatutBcConstant::STATUT_A_ENVOYER_AU_FOURNISSEUR
        ];
        $statutsDA_Traiter_PasAppro = [
            StatutDaConstant::STATUT_EN_COURS_CREATION,
            StatutDaConstant::STATUT_AUTORISER_EMETTEUR,
            StatutDaConstant::STATUT_SOUMIS_ATE
        ];

        $builder
            ->add('afficherCloturees', CheckboxType::class, [
                'label'    => 'Inclure les DA clôturées',
                'required' => false
            ])
            ->add('afficherDaTraiter', CheckboxType::class, [
                'label'    => "N'afficher que les DA à traiter",
                'required' => false,
                'data'     => true,
                'attr'     => [
                    'data-is-appro-user' => $isApproUser ? '1' : '0'
                ]
            ])
            ->add('numDit', TextType::class, [
                'label'         => 'N° OR/DIT',
                'required'      => false
            ])
            ->add('numDa', TextType::class, [
                'label'         => 'N° DAP',
                'required'      => false
            ])
            ->add('numCde', TextType::class, [
                'label' => 'N° Commande',
                'required' => false
            ])
            ->add('demandeur', TextType::class, [
                'label'         => 'Demandeur',
                'required'      => false
            ])
            ->add('statutDA', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut de la DA',
                'choices'       => $statut_da,
                'required'      => false,
                'choice_attr' => function ($choice, $key, $value) use ($statutsDA_Traiter_Appro, $statutsDA_Traiter_PasAppro) {
                    $attr = [];
                    if (in_array($value, $statutsDA_Traiter_Appro)) $attr['data-traiter-appro'] = '1';
                    if (in_array($value, $statutsDA_Traiter_PasAppro)) $attr['data-traiter-pas-appro'] = '1';
                    return $attr;
                }
            ])
            ->add('statutOR', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut',
                'choices'       => $statut_or,
                'required'      => false
            ])
            ->add('statutBC', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut du BC',
                'choices'       => $statut_bc,
                'required'      => false,
                'choice_attr' => function ($choice, $key, $value) use ($statutsBC_Traiter_Appro) {
                    $attr = [];
                    if (in_array($value, $statutsBC_Traiter_Appro)) $attr['data-traiter-appro'] = '1';
                    return $attr;
                }
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
        $transformer = new CallbackTransformer(
            function ($value) {
                return is_array($value) ? null : $value;
            },
            function ($value) {
                return $value;
            }
        );

        $builder->get('statutDA')->addModelTransformer($transformer);
        $builder->get('statutOR')->addModelTransformer($transformer);
        $builder->get('statutBC')->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DaSearch::class,
            'estAppro'   => false,
            'codeAgence' => null,
            'codeService' => null,
        ]);
    }
}
