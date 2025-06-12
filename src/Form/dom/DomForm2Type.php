<?php

namespace App\Form\dom;


use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\dom\Rmq;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\dom\Indemnite;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dom\SousTypeDocument;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class DomForm2Type extends AbstractType
{
    use FormatageTrait;
    private $em;

    const OUI_NON = [
        'NON' => 'NON',
        'OUI' => 'OUI'
    ];
    const DEVISE = [
        'MGA' => 'MGA',
        'EUR' => 'EUR',
        'USD' => 'USD'
    ];

    const MODE_PAYEMENT = [
        'MOBILE MONEY' => 'MOBILE MONEY',
        'ESPECES' => 'ESPECES',
        'VIREMENT BANCAIRE' => 'VIREMENT BANCAIRE',
    ];

    public function __construct()
    {
        $this->em = Controller::getEntity();
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $idSousTypeDocument = $options['data']->getSousTypeDocument()->getId();
        $sousTypeDocument = $options["data"]->getSousTypeDocument()->getCodeSousType();
        $salarier = $options['data']->getSalarier();

        $builder
            ->add(
                'agence',
                EntityType::class,
                [
                    'label' => 'Agence Debiteur',
                    'placeholder' => '-- Choisir une agence Debiteur --',
                    'class' => Agence::class,
                    'choice_label' => function (Agence $agence): string {
                        return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                    },
                    'required' => false,
                    //'data' => $options["data"]->getAgence() ?? null,
                    'query_builder' => function (AgenceRepository $agenceRepository) {
                        return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
                    },
                    'attr' => ['class' => 'agenceDebiteur']
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();

                $services = null;

                if ($data instanceof Dom && $data->getAgence()) {
                    $services = $data->getAgence()->getServices();
                }


                $form->add(
                    'service',
                    EntityType::class,
                    [

                        'label' => 'Service Débiteur',
                        'class' => Service::class,
                        'choice_label' => function (Service $service): string {
                            return $service->getCodeService() . ' ' . $service->getLibelleService();
                        },
                        'choices' => $services,
                        // 'disabled' => $agence === null,
                        'required' => false,
                        'query_builder' => function (ServiceRepository $serviceRepository) {
                            return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                        },
                        //'data' => $options['data']->getService(),
                        'attr' => ['class' => 'serviceDebiteur']
                    ]
                );
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();


                $agenceId = $data['agence'];

                $agence = $this->em->getRepository(Agence::class)->find($agenceId);
                $services = $agence->getServices();

                $form->add('service', EntityType::class, [
                    'label' => 'Service Débiteur',
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'choices' => $services,
                    'required' => false,
                    'attr' => [
                        'class' => 'serviceDebiteur',
                        'disabled' => false,
                    ]
                ]);
            })
            ->add(
                'agenceEmetteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Agence',
                    'required' => false,
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options["data"]->getAgenceEmetteur() ?? null
                ]
            )

            ->add(
                'serviceEmetteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Service',
                    'required' => false,
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options["data"]->getServiceEmetteur() ?? null
                ]
            )
            ->add(
                'dateDemande',
                DateTimeType::class,
                [
                    'label' => 'Date',
                    'mapped' => false,
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options["data"]->getDateDemande()
                ]
            )
            ->add(
                'sousTypeDocument',
                TextType::class,
                [
                    'label' => 'Type de Mission :',
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options["data"]->getSousTypeDocument()->getCodeSousType()
                ]
            )
            ->add(
                'categorie',
                TextType::class,
                [
                    'label' => 'Catégorie :',
                    'row_attr' => [
                        'style' => $idSousTypeDocument === 3 || $idSousTypeDocument === 4 || $idSousTypeDocument === 10 ? 'display: none;' : ''
                    ],
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options["data"]->getCategorie() !== null ? $options["data"]->getCategorie()->getDescription() : null
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($idSousTypeDocument) {
                $form = $event->getForm();
                $data = $event->getData();


                if ($data->getCategorie() !== null) {
                    $catgId = $data->getCategorie()->getId();
                    $catg = $this->em->getRepository(Catg::class)->find($catgId);
                } else {
                    $catg = null;
                }
                $docId = $data->getSousTypeDocument()->getId();
                $rmqId = $data->getRmq()->getId();

                $sousTypedocument = $this->em->getRepository(SousTypeDocument::class)->find($docId);

                $rmq = $this->em->getRepository(Rmq::class)->find($rmqId);

                $criteria = [
                    'sousTypeDoc' => $sousTypedocument,
                    'rmq' => $rmq,
                    'categorie' => $catg
                ];

                $indemites = $this->em->getRepository(Indemnite::class)->findBy($criteria);

                $sites = [];
                foreach ($indemites as $value) {

                    $sites[] = $value->getSite();
                }

                $form->add(
                    'site',
                    EntityType::class,
                    [
                        'label' => 'Site:',
                        'class' => Site::class,
                        'choice_label' => 'nomZone',
                        'choices' => $sites,
                        'row_attr' => [
                            'style' => $idSousTypeDocument === 3 || $idSousTypeDocument === 4 || $idSousTypeDocument === 10 ? 'display: none;' : ''
                        ],
                        'attr' => [
                            'disabled' => $idSousTypeDocument === 3 || $idSousTypeDocument === 4 || $idSousTypeDocument === 10,
                        ]
                    ]
                );
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($idSousTypeDocument, $options) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($options['data']->getCategorie() !== null) {
                    $catgId = $options['data']->getCategorie()->getId();
                    $catg = $this->em->getRepository(Catg::class)->find($catgId);
                } else {
                    $catg = null;
                }
                $docId = $options['data']->getSousTypeDocument()->getId();
                $rmqId = $options['data']->getRmq()->getId();

                $sousTypedocument = $this->em->getRepository(SousTypeDocument::class)->find($docId);
                $rmq = $this->em->getRepository(Rmq::class)->find($rmqId);

                $criteria = [
                    'sousTypeDoc' => $sousTypedocument,
                    'rmq' => $rmq,
                    'categorie' => $catg
                ];

                $indemites = $this->em->getRepository(Indemnite::class)->findBy($criteria);

                $sites = [];
                foreach ($indemites as $key => $value) {
                    $sites[] = $value->getSite();
                }

                $form->add(
                    'site',
                    EntityType::class,
                    [
                        'label' => 'Site:',
                        'class' => Site::class,
                        'choice_label' => 'nomZone',
                        'choices' => $sites,
                        'row_attr' => [
                            'style' => $idSousTypeDocument === 3 || $idSousTypeDocument === 4 || $idSousTypeDocument === 10 ? 'display: none;' : ''
                        ],
                        'attr' => [
                            'disabled' => $idSousTypeDocument === 3 || $idSousTypeDocument === 4 || $idSousTypeDocument === 10,
                        ]
                    ]
                );
            })

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($idSousTypeDocument) {
                $form = $event->getForm();
                $data = $event->getData();

                $siteId = $data->getSite()->getId();
                $docId = $data->getSousTypeDocument()->getId();
                if ($data->getCategorie() !== null) {
                    $catgId = $data->getCategorie()->getId();
                    $catg = $this->em->getRepository(Catg::class)->find($catgId);
                } else {
                    $catg = null;
                }

                $rmqId = $data->getRmq()->getId();
                $site = $this->em->getRepository(Site::class)->find($siteId);
                $sousTypedocument = $this->em->getRepository(SousTypeDocument::class)->find($docId);
                $rmq = $this->em->getRepository(Rmq::class)->find($rmqId);

                $criteria = [
                    'sousTypeDoc' => $sousTypedocument,
                    'rmq' => $rmq,
                    'categorie' => $catg,
                    'site' => $site
                ];

                if ($this->em->getRepository(Indemnite::class)->findOneBy($criteria) !== null) {
                    $montant = $this->em->getRepository(Indemnite::class)->findOneBy($criteria)->getMontant();

                    $montant = $this->formatNumber($montant);
                } else {
                    $montant = 0;
                }


                if ($idSousTypeDocument === 10) {
                    $montant = 0;
                } else if ($idSousTypeDocument === 3 || $idSousTypeDocument === 4) {
                    $montant  = '';
                }

                $form->add(
                    'indemniteForfaitaire',
                    TextType::class,
                    [
                        'label' => 'Indeminté forfaitaire journalière(s)',
                        'attr' => [
                            'readonly' => $idSousTypeDocument === 2 || $idSousTypeDocument === 5,
                            'disabled' => $idSousTypeDocument === 3 || $idSousTypeDocument === 4
                        ],
                        'data' =>  $montant
                    ]
                );
            })

            ->add(
                'matricule',
                TextType::class,
                [
                    'label' => 'Matricule',
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options["data"]->getMatricule() ?? null
                ]
            )
            ->add(
                'nom',
                TextType::class,
                [
                    'label' => 'Nom',
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options["data"]->getNom() ?? null
                ]
            )
            ->add(
                'prenom',
                TextType::class,
                [
                    'label' => 'Prénoms',
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options["data"]->getPrenom() ?? null
                ]
            )
            ->add(
                'cin',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'CIN',
                    'attr' => [
                        'disabled' => true,
                    ],
                    'data' => $options["data"]->getCin() ?? null
                ]
            )

            ->add(
                'dateDebut',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'label' => 'Date debut',

                ]
            )

            ->add(
                'heureDebut',
                TimeType::class,
                [
                    'label' => 'Heure début',
                    'widget' => 'single_text', // Pour utiliser un champ de saisie unique
                    'attr' => [
                        'class' => 'form-control', // Pour ajouter des classes CSS si nécessaire
                        'value' => '08:00', // Définit la valeur par défaut
                    ],
                    'input' => 'datetime', // Spécifie que l'entrée est une instance de \DateTime
                ]
            )

            ->add(
                'dateFin',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'label' => 'Date fin',

                ]
            )
            ->add(
                'heureFin',
                TimeType::class,
                [
                    'label' => 'Heure fin',
                    'widget' => 'single_text', // Pour utiliser un champ de saisie unique
                    'attr' => [
                        'class' => 'form-control', // Pour ajouter des classes CSS si nécessaire
                        'value' => '18:00', // Définit la valeur par défaut
                    ],
                    'input' => 'datetime', // Spécifie que l'entrée est une instance de \DateTime
                ]
            )
            ->add(
                'nombreJour',
                TextType::class,
                [
                    'label' => 'Nombre de Jour',
                    'attr' => [
                        'readonly' => true
                    ]
                ]
            )
            ->add(
                'motifDeplacement',
                TextType::class,
                [
                    'label' => 'Motif',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'Le motif de déplacement ne peut pas être vide.']),
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Le motif de déplacement doit comporter au moins {{ limit }} caractères',
                            'max' => 100,
                            'maxMessage' => 'Le motif de déplacement ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'client',
                TextType::class,
                [
                    'label' => 'Nom du client',
                    'required' => true,
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Le Client doit comporter au moins {{ limit }} caractères',
                            'max' => 50,
                            'maxMessage' => 'Le Client ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )

            ->add(
                'fiche',
                TextType::class,
                [
                    'label' => 'N° fiche',
                    'required' => false,
                ]
            )

            ->add(
                'lieuIntervention',
                TextType::class,
                [
                    'label' => 'Lieu d\'intervention',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'Le lieu d\'intervention ne peut pas être vide.']),
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Le lieu doit comporter au moins {{ limit }} caractères',
                            'max' => 100,
                            'maxMessage' => 'Le lieu ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'vehiculeSociete',
                ChoiceType::class,
                [
                    'label' => "Véhicule société",
                    'choices' => self::OUI_NON,
                    'data' => "OUI",
                ]
            )
            ->add(
                'numVehicule',
                TextType::class,
                [
                    'label' => 'N°',
                    'required' => false,
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Le n° vehicule doit comporter au moins {{ limit }} caractères',
                            'max' => 10,
                            'maxMessage' => 'Le n° vehicule ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'idemnityDepl',
                TextType::class,
                [
                    'label' => 'Indemnité de chantier',
                    'required' => false
                ]
            )

            ->add(
                'totalIndemniteDeplacement',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Total indemnité de chantier',
                    'attr' => [
                        'readonly' => true
                    ]
                ]
            )
            ->add(
                'devis',
                ChoiceType::class,
                [
                    'label' => 'Devise :',
                    'choices' => self::DEVISE,
                    'data' => 'MGA'
                ]
            )

            ->add(
                'supplementJournaliere',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'supplément journalier',
                    'required' => false,
                    'attr' => [
                        'disabled' => $idSousTypeDocument === 11,
                    ]
                ]
            )
            ->add(
                'totalIndemniteForfaitaire',
                TextType::class,
                [
                    'label' => "Total de l'indemnite forfaitaire",
                    'attr' => [
                        'readonly' => true
                    ]
                ]
            )
            ->add(
                'motifAutresDepense1',
                TextType::class,
                [
                    'label' => 'Motif Autre dépense 1',
                    'required' => false,
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Le motif autre dépense 1 doit comporter au moins {{ limit }} caractères',
                            'max' => 30,
                            'maxMessage' => 'Le motif autre dépense 1 ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'autresDepense1',
                TextType::class,
                [
                    'label' => 'Montant',
                    'required' => false,
                ]
            )
            ->add(
                'motifAutresDepense2',
                TextType::class,
                [
                    'label' => 'Motif Autre dépense 2',
                    'required' => false,
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Le motif autre dépense 2 doit comporter au moins {{ limit }} caractères',
                            'max' => 30,
                            'maxMessage' => 'Le motif autre dépense 2 ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'autresDepense2',
                TextType::class,
                [
                    'label' => 'Montant',
                    'required' => false,
                ]
            )
            ->add(
                'motifAutresDepense3',
                TextType::class,
                [
                    'label' => 'Motif Autre dépense 3',
                    'required' => false,
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Le motif autre dépense 3 doit comporter au moins {{ limit }} caractères',
                            'max' => 30,
                            'maxMessage' => 'Le motif autre dépense 3 ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'autresDepense3',
                TextType::class,
                [
                    'label' => 'Montant',
                    'required' => false,
                ]
            )

            ->add(
                'totalAutresDepenses',
                TextType::class,
                [
                    'label' => 'Total Montant Autre Dépense',
                    'required' => true,
                    'attr' => [
                        'readonly' => true
                    ]
                ]
            )
            ->add(
                'totalGeneralPayer',
                TextType::class,
                [
                    'label' => 'Montant Total',
                    'required' => true,
                    'attr' => [
                        'readonly' => true
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le montant total ne peut pas être vide.',
                        ]),
                        // new GreaterThan([
                        //     'value' => 0,
                        //     'message' => 'Le montant total doit être supérieur à 0.',
                        // ]),
                    ],
                ]
            )
            ->add(
                'modePayement',
                ChoiceType::class,
                [
                    'label' => 'Mode paiement',
                    'choices' => self::MODE_PAYEMENT
                ]
            )
            ->add(
                'mode',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'MOBILE MONEY',
                    'required' => true,
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Le Mode doit comporter au moins {{ limit }} caractères',
                            'max' => 30,
                            'maxMessage' => 'Le mode ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                    'data' => $options['data']->getNumerotel()
                ]
            )
            ->add(
                'pieceJoint01',
                FileType::class,
                [
                    'label' => 'Fichier Joint 01 (Merci de mettre un fichier PDF)',
                    'required' => $salarier !== 'PERMANENT',
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid PDF file.',
                        ])
                    ],
                ]
            )
            ->add(
                'pieceJoint02',
                FileType::class,
                [
                    'label' => 'Fichier Joint 02 (Merci de mettre un fichier PDF)',
                    'required' => $salarier !== 'PERMANENT',
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid PDF file.',
                        ])
                    ],
                ]
            )
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Dom::class,
        ]);
    }
}
