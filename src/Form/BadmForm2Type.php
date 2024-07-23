<?php


namespace App\Form;

use App\Entity\Badm;
use App\Entity\Agence;
use App\Entity\Casier;
use App\Entity\Service;
use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Entity\CasierValider;
use App\Repository\AgenceRepository;
use App\Repository\casierRepository;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class BadmForm2Type extends AbstractType
{
    use FormatageTrait;
    private $em;

    const MODE_PAYEMENT = [
        "TRAITE" => "TRAITE",
        "CHEQUE" => "CHEQUE",
        "VIREMENT" => "VIREMENT"
    ];
    
    public function __construct()
    {
     $this->em = Controller::getEntity();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $idTypeMouvement = $options["data"]->getTypeMouvement()->getId();
        
        $builder
        ->add('agenceEmetteur', 
        TextType::class,
        [
           'mapped' => false,
            'label' => 'Agence',
            'required' => false,
            'attr' => [
                'readonly' => true
            ],
            'data' => $options['data']->getAgenceEmetteur()
        ])
        ->add('serviceEmetteur', 
        TextType::class,
        [
            'mapped' => false,
            'label' => 'Service',
            'required' => false,
            'attr' => [
                'readonly' => true,
                'disable' => true
            ],
            'data' => $options['data']->getServiceEmetteur()
        ])
        ->add('designation', 
            TextType::class,
            [
                'label' => 'Désignation ',
                'mapped' => false,
                'attr' => [
                    'disabled' => true
                ],
                'data' => $options["data"]->getDesignation()
            ]
        )
        ->add('idMateriel', 
            TextType::class,
            [
                'label' => 'ID matériel',
                'mapped' => false,
                'attr' => [
                    'disabled' => true
                ],
                'data' => $options["data"]->getIdMateriel()
            ]
        )
        ->add('numSerie', 
            TextType::class,
            [
                'label' => 'N° Série ',
                'mapped' => false,
                'attr' => [
                    'disabled' => true
                ],
                'data' => $options["data"]->getNumSerie()
            ]
        )
        ->add('numParc', 
            TextType::class,
            [
                'label' => 'N° Parc',
                'attr' => [
                    'disabled' => $idTypeMouvement !== 1
                ],
                'data' => $options["data"]->getNumParc(),
                'required' => $idTypeMouvement === 1
            ]
        )
        ->add('groupe', 
            TextType::class,
            [
                'label' => 'Groupe ',
                'mapped' => false,
                'attr' => [
                    'disabled' => true
                ],
                'data' => $options["data"]->getGroupe()
            ]
        )
        ->add('constructeur', 
            TextType::class,
            [
                'label' => 'Constructeur',
                'mapped' => false,
                'attr' => [
                    'disabled' => true
                ],
                'data' => $options["data"]->getConstructeur()
            ]
        )
        ->add('modele', 
            TextType::class,
            [
                'label' => 'Modèle',
                'mapped' => false,
                'attr' => [
                    'disabled' => true
                ],
                'data' => $options["data"]->getModele()
            ]
        )
        ->add('anneeDuModele', 
            TextType::class,
            [
                'label' => 'Année du modèle',
                'mapped' => false,
                'attr' => [
                    'disabled' => true
                ],
                'data' => $options["data"]->getAnneeDuModele()
            ]
        )
        ->add('affectation', 
            TextType::class,
            [
                'label' => 'Affectation',
                'mapped' => false,
                'attr' => [
                    'disabled' => true
                ],
                'data' => $options["data"]->getAffectation()
            ]
        )
        ->add('dateAchat', 
            TextType::class,
            [
                'label' => 'Date d’achat ',
                'mapped' => false,
                'attr' => [
                    'disabled' => true
                ],
                'data' => $options["data"]->getDateAchat()
            ]
        )
        
        ->add('dateDemande',
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
        ])
        //ETAT MACHINE
        ->add('heureMachine', 
        TextType::class,
        [
            'label' => 'Heures machine',
            'attr' => [
                    'disabled' => true
                ],
            'data' => $options["data"]->getheureMachine()
        ])
        ->add('kmMachine', 
        TextType::class,
        [
            'label' => 'Kilométrage',
            'attr' => [
                    'disabled' => true
                ],
            'data' => $options["data"]->getkmMachine()
        ])
        //AGENCE -SERVICE EMETTEUR
        ->add('agenceEmetteur', 
        TextType::class,
        [
           'mapped' => false,
            'label' => 'Agence',
            'required' => false,
            'attr' => [
                'disabled' => true
            ],
            'data' => $options['data']->getAgenceEmetteur()
        ])
        ->add('serviceEmetteur', 
        TextType::class,
        [
            'mapped' => false,
            'label' => 'Service',
            'required' => false,
            'attr' => [
                'disabled' => true
            ],
            'data' => $options['data']->getServiceEmetteur()
        ])
        ->add('casierEmetteur', 
        TextType::class,
        [
            'label' => 'Casier',
            'attr' => [
                'disabled' => true
            ]
        ])
        //AGENCE -SERVICE DESTINATAIRE
        ->add('agence', 
        EntityType::class,
        [
            'label' => 'Agence Debiteur',
            'placeholder' => '-- Choisir une agence --',
            'class' => Agence::class,
            'choice_label' => function (Agence $agence): string {
                return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
            },
            
            'query_builder' => function(AgenceRepository $agenceRepository) {
                    return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
                },
                'attr' => [ 
                    'disabled' => $idTypeMouvement === 4 || $idTypeMouvement === 5 || $idTypeMouvement === 3
                ],
                'required' => $idTypeMouvement !== 4 && $idTypeMouvement !== 5,
        ])
        ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($idTypeMouvement){
            $form = $event->getForm();
            $data = $event->getData();
          
            $services = [];
            $casiers = [];

            if ($data instanceof Badm && $data->getAgence()) {
                $services = $data->getAgence()->getServices();
                $casiers = $data->getAgence()->getCasiers();
            }

            $form
            ->add('service',
            EntityType::class,
            [
            
            'label' => 'Service Débiteur',
            'class' => Service::class,
            'choice_label' => function (Service $service): string {
                return $service->getCodeService() . ' ' . $service->getLibelleService();
            },
            'placeholder' => ' -- Choisir une service --',
            'choices' => $services,
            // 'disabled' => $agence === null,
           
            'query_builder' => function(ServiceRepository $serviceRepository) {
                    return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                },
                'attr' => [ 
                    'disabled' => $idTypeMouvement === 4 || $idTypeMouvement === 5 || $idTypeMouvement === 3
                ],
                'required' => $idTypeMouvement !== 4 && $idTypeMouvement !== 5,
            ])
            ->add('casierDestinataire',
                EntityType::class,
                [
                    'label' => 'Casier Destinataire',
                    'class' => CasierValider::class,
                    'choice_label' => 'casier',
                    'placeholder' => ' -- Choisir un casier --',
                    'choices' => $casiers,
                    'query_builder' => function(casierRepository $casierRepository) {
                    return $casierRepository->createQueryBuilder('c')->orderBy('c.casier', 'ASC');
                },
                'attr' => [ 
                    'disabled' => $idTypeMouvement === 4 || $idTypeMouvement === 5
                ],
                'required' => $idTypeMouvement !== 4 && $idTypeMouvement !== 5,
                ])
        ;
            
        })
        ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use($idTypeMouvement) {
            $form = $event->getForm();
                $data = $event->getData();
                $agenceId = $data['agence'] ?? null;

                if ($agenceId) {
                    $agence = $this->em->getRepository(Agence::class)->find($agenceId);
                    if ($agence) {
                        $services = $agence->getServices();
                        $casiers = $agence->getCasiers();

                        $form
                        ->add('service', EntityType::class, [
                            'label' => 'Service Débiteur',
                            'class' => Service::class,
                            'choice_label' => function (Service $service): string {
                                return $service->getCodeService() . ' ' . $service->getLibelleService();
                            },
                            'placeholder' => ' -- Choisir une service --',
                            'choices' => $services,
                            'required' => false,
                            'attr' => [ 
                                'disabled' => $idTypeMouvement === 4 || $idTypeMouvement === 5
                            ],
                            'required' => $idTypeMouvement !== 4 && $idTypeMouvement !== 5,
                        ])
                        ->add('casierDestinataire', EntityType::class, [
                            'label' => 'Casier Destinataire',
                            'class' => Casier::class,
                            'choice_label' => 'casier',
                            'placeholder' => ' -- Choisir un casier --',
                            'choices' => $casiers,
                            'required' => false,
                            'attr' => [ 
                            'disabled' => $idTypeMouvement === 4 || $idTypeMouvement === 5
                            ],
                        'required' => $idTypeMouvement !== 4 && $idTypeMouvement !== 5,
    
                        ]);
                    }
                }
            })
        
        ->add('motifMateriel',
        TextType::class,
        [
            'label' => 'Motif',
            'attr' => [
                'disabled' => $idTypeMouvement !== 1 && $idTypeMouvement !==2 && $idTypeMouvement !==3,
            ],
            'required' => $idTypeMouvement === 1 || $idTypeMouvement === 2||$idTypeMouvement === 3,

        ])
        //ENTREE EN PARC
        ->add('etatAchat',
        TextType::class,
        [
            'label' => 'Etat à l\'achat',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
        ])
        ->add('dateMiseLocation',
        DateTimeType::class,
        [
            'label' => 'Date mise en location',
            'mapped' => false,
                'widget' => 'single_text', 
                'html5' => false, 
                'format' => 'dd/MM/yyyy', 
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
            'data' => $options["data"]->getDateMiseLocation()
        ])
        //BILAN FINANCIERE
        ->add('coutAcquisition',
        TextType::class,
        [
            'label' => 'Coût d\'acquisition',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
            'data' => $this->formatNumber($options["data"]->getCoutAcquisition())
        ])
        ->add('amortissement',
        TextType::class,
        [
            'label' => 'Amortissement',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
            'data' => $this->formatNumber($options["data"]->getAmortissement())
        ])
        ->add('valeurNetComptable',
        TextType::class,
        [
            'label' => 'VNC',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
            'data' => $this->formatNumber($options["data"]->getValeurNetComptable())
        ])
        //CESSION ACTIF
        ->add('nomClient',
        TextType::class,
        [
            'label' => 'Nom client',
            'attr' => [
                'disabled' => $idTypeMouvement !== 4
            ],
            'required' => $idTypeMouvement === 4,
        ])
        ->add('modalitePaiement',
        ChoiceType::class,
        [
            'label' => 'Modalité de paiement',
            'choices' => self::MODE_PAYEMENT,
            'attr' => [
                'disabled' => $idTypeMouvement !== 4
            ],
            'required' => $idTypeMouvement === 4,
        ])
        ->add('prixVenteHt',
        TextType::class,
        [
            'label' => 'Prix HT',
            'attr' => [
                'disabled' => $idTypeMouvement !== 4
            ],
            'required' => $idTypeMouvement === 4,
        ])
        //MISE AU REBUT
        ->add('motifMiseRebut',
        TextType::class,
        [
            'label' => 'Motif de mise au rebut',
            'attr' => [
                'disabled' =>  $idTypeMouvement !== 5
            ],
            'required' => $idTypeMouvement === 5,
        ]
        )
        ->add('nomImage',
        FileType::class, 
        [
            'label' => 'Image (Merci de mettre un fichier image)',
            'required' => false,
            'constraints' => [
                new NotNull(['message' => 'Please upload a file.']),
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid jpeg, jpg, png file.',
                ])
            ],
        ])
        ->add('nomFichier',
        FileType::class, 
        [
            'label' => 'Fichier (Merci de mettre un fichier PDF)',
            'required' => false,
            'constraints' => [
                new NotNull(['message' => 'Please upload a file.']),
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF, DOCX file.',
                ])
            ],
        ]
        )
        ; 
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Badm::class
        ]);
    }
}
