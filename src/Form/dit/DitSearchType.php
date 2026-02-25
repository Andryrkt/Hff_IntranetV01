<?php

namespace App\Form\dit;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\dit\DitSearch;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\StatutDemande;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use App\Repository\admin\StatutDemandeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class DitSearchType extends AbstractType
{
    const INTERNE_EXTERNE = [
        'INTERNE' => 'INTERNE',
        'EXTERNE' => 'EXTERNE'
    ];

    const ETAT_FACTURE = [
        'Complètement facturé' => 'Complètement facturé',
        'Partiellement facturé' => 'Partiellement facturé',
        'A valider client interne' => 'A valider client interne'
    ];

    const REPARATION_REALISE = [
        'ATE TANA' => 'ATE TANA',
        'ATE POL TANA' => 'ATE POL TANA',
        'ATE STAR' => 'ATE STAR',
        'ATE MAS' => 'ATE MAS',
        'ATE TMV' => 'ATE TMV',
        'ATE FTU' => 'ATE FTU',
        'ATE ABV' => 'ATE ABV',
        'ATE LEV' => 'ATE LEV',
        'ENERGIE MAN' => 'ENERGIE MAN'
    ];

    private $agenceRepository;

    private $ditSearchRepository;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->agenceRepository = $this->em->getRepository(Agence::class);
        $this->ditSearchRepository = $this->em->getRepository(DemandeIntervention::class);
    }

    private function statutOr()
    {
        $statutOr = $this->ditSearchRepository->findStatutOr();

        return array_combine($statutOr, $statutOr);
    }

    private function sectionAffectee()
    {
        $sectionAffecte = $this->ditSearchRepository->findSectionAffectee();
        $groupes = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe']; // Les groupes de mots à supprimer
        $sectionAffectee = str_replace($groupes, "", $sectionAffecte);
        return array_combine($sectionAffectee, $sectionAffectee);
    }

    private function sectionSupport1()
    {
        $sectionSupport1 = $this->ditSearchRepository->findSectionSupport1();
        $groupes = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe']; // Les groupes de mots à supprimer
        $sectionSupport1 = str_replace($groupes, "", $sectionSupport1);
        return array_combine($sectionSupport1, $sectionSupport1);
    }

    private function sectionSupport2()
    {
        $sectionSupport2 = $this->ditSearchRepository->findSectionSupport2();
        $groupes = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe']; // Les groupes de mots à supprimer
        $sectionSupport2 = str_replace($groupes, "", $sectionSupport2);
        return array_combine($sectionSupport2, $sectionSupport2);
    }

    private function sectionSupport3()
    {
        $sectionSupport3 = $this->ditSearchRepository->findSectionSupport3();
        $groupes = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe']; // Les groupes de mots à supprimer
        $sectionSupport3 = str_replace($groupes, "", $sectionSupport3);
        return array_combine($sectionSupport3, $sectionSupport3);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $autorisationRoleEnergie = $options['autorisationRoleEnergie'];


        $builder
            ->add('niveauUrgence', EntityType::class, [
                'label' => 'Niveau d\'urgence',
                'label_html' => true,
                'class' => WorNiveauUrgence::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir un niveau--',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.description', 'DESC');
                },
                'attr' => [
                    'class' => 'niveauUrgence'
                ]
            ])
            ->add('statut', EntityType::class, [
                'label' => 'Statut',
                'class' => StatutDemande::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir un statut --',
                'required' => false,
                'attr' => [
                    'class' => 'statut'
                ],
                'query_builder' => function (StatutDemandeRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.codeApp = :codeApp')
                        ->setParameter('codeApp', 'DIT');
                },
            ])
            ->add('idMateriel', NumberType::class, [
                'label' => 'Id Matériel',
                'required' => false,
            ])
            ->add('typeDocument', EntityType::class, [
                'label' => 'Type de Document',
                'class' => WorTypeDocument::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir un type de document--',
                'required' => false,
            ])
            ->add(
                'internetExterne',
                ChoiceType::class,
                [
                    'label' => "Interne - Externe",
                    'choices' => self::INTERNE_EXTERNE,
                    'placeholder' => '-- Choisir --',
                    'required' => false,
                    'attr' => ['class' => 'interneExterne']
                ]
            )
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début demande',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin demande',
                'required' => false,
            ])
            ->add('numParc', TextType::class, [
                'label' => "N° Parc",
                'required' => false
            ])
            ->add('numSerie', TextType::class, [
                'label' => "N° Série",
                'required' => false
            ])

            ->add(
                'numDit',
                TextType::class,
                [
                    'label' => 'N° DIT',
                    'required' => false
                ]
            )
            ->add(
                'numOr',
                NumberType::class,
                [
                    'label' => 'N° OR',
                    'required' => false
                ]
            )
            ->add(
                'statutOr',
                ChoiceType::class,
                [
                    'label' => 'Statut OR',
                    'required' => false,
                    'choices' => $this->statutOr(),
                    'placeholder' => '-- choisir une statut --'
                ]
            )
            ->add(
                'ditSansOr',
                CheckboxType::class,
                [
                    'label' => 'DIT sans OR',
                    'required' => false
                ]
            )

            ->add(
                'categorie',
                EntityType::class,
                [
                    'label' => 'Catégorie de demande',
                    'placeholder' => '-- Choisir une catégorie --',
                    'class' => CategorieAteApp::class,
                    'choice_label' => 'libelleCategorieAteApp',
                    'required' => false,
                ]
            )
            ->add(
                'utilisateur',
                TextType::class,
                [
                    'label' => 'Utilisateur',
                    'required' => false
                ]
            )
            ->add(
                'sectionAffectee',
                ChoiceType::class,
                [
                    'label' => 'Section affectée',
                    'required' => false,
                    'choices' => $this->sectionAffectee(),
                    'placeholder' => '-- choisir une section --'
                ]
            )
            ->add(
                'sectionSupport1',
                ChoiceType::class,
                [
                    'label' => 'Section support 1',
                    'placeholder' => '-- choisir une section --',
                    'required' => false,
                    'choices' => $this->sectionSupport1(),

                ]
            )
            ->add(
                'sectionSupport2',
                ChoiceType::class,
                [
                    'label' => 'Section support 2',
                    'placeholder' => '-- choisir une section --',
                    'required' => false,
                    'choices' => $this->sectionSupport2(),

                ]
            )
            ->add(
                'sectionSupport3',
                ChoiceType::class,
                [
                    'label' => 'Section support 3',
                    'placeholder' => '-- choisir une section --',
                    'required' => false,
                    'choices' => $this->sectionSupport3(),

                ]
            )
            ->add(
                'etatFacture',
                ChoiceType::class,
                [
                    'label' => "Statut facture",
                    'choices' => self::ETAT_FACTURE,
                    'placeholder' => '-- Choisir --',
                    'required' => false,
                ]
            )
            ->add(
                'numDevis',
                TextType::class,
                [
                    'label' => 'N° devis',
                    'required' => false
                ]
            )
            ->add(
                'reparationRealise',
                ChoiceType::class,
                [
                    'label' => "Réalisé par",
                    'choices' => self::REPARATION_REALISE,
                    'placeholder' => '-- Choisir le répartion réalisé --',
                    'required' => false,
                ]
            )
        ;

        if ($autorisationRoleEnergie) {
            $builder->add('agenceEmetteur', EntityType::class, [
                'label' => "Agence émetteur",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence--',
                'required' => false,
                'query_builder' => function (AgenceRepository $agenceRepository) {
                    return $agenceRepository->createQueryBuilder('a')
                        ->where('a.codeAgence IN (:codes)')
                        ->setParameter('codes', ['90', '91', '92'])
                        ->orderBy('a.codeAgence', 'ASC');
                },
                'attr' => ['class' => 'agenceEmetteur']
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
                        'placeholder' => '-- Choisir un service--',
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

                    if (isset($data['agenceEmetteur']) && $data['agenceEmetteur']) {
                        $agenceId = $data['agenceEmetteur'];
                        $agence = $this->agenceRepository->find($agenceId);

                        if ($agence) {
                            $services = $agence->getServices();
                        } else {
                            $services = [];
                        }
                    } else {
                        $services = [];
                    }

                    $form->add('serviceEmetteur', EntityType::class, [
                        'label' => "Service Emetteur",
                        'class' => Service::class,
                        'choice_label' => function (Service $service): string {
                            return $service->getCodeService() . ' ' . $service->getLibelleService();
                        },
                        'placeholder' => '-- Choisir un service--',
                        'choices' => $services,
                        'required' => false,
                        'query_builder' => function (ServiceRepository $serviceRepository) {
                            return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                        },
                        'attr' => ['class' => 'serviceEmetteur']
                    ]);
                });
        } else {
            $builder->add('agenceEmetteur', EntityType::class, [
                'label' => "Agence émetteur",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence--',
                'required' => false,
                'attr' => ['class' => 'agenceEmetteur']
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
                        'placeholder' => '-- Choisir un service--',
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
                        'placeholder' => '-- Choisir un service--',
                        'choices' => $services,
                        'required' => false,
                        'query_builder' => function (ServiceRepository $serviceRepository) {
                            return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                        },
                        'attr' => ['class' => 'serviceEmetteur']
                    ]);
                });
        }

        $builder
            ->add('agenceDebiteur', EntityType::class, [
                'label' => "Agence débiteur",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence--',
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
                    'placeholder' => '-- Choisir un service--',
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
                    'placeholder' => '-- Choisir un service--',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceDebiteur']
                ]);
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DitSearch::class,
        ]);
        //$resolver->setDefined('idAgenceEmetteur');
        $resolver->setDefined('interne_externe');
        $resolver->setDefined('autorisationRoleEnergie');
    }
}
