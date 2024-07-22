<?php

namespace App\Form;

use App\Entity\Dom;
use App\Entity\Rmq;
use App\Entity\Catg;
use App\Entity\Site;
use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\Idemnity;
use App\Entity\Indemnite;
use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Repository\SiteRepository;
use App\Entity\DemandeIntervention;
use App\Repository\AgenceRepository;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;






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
        $builder
        ->add('agence', 
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
            'query_builder' => function(AgenceRepository $agenceRepository) {
                    return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
                },
                'attr' => [ 'class' => 'agenceDebiteur']
        ])
        ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options){
            $form = $event->getForm();
            $data = $event->getData();
          
            $services = null;

            if ($data instanceof Dom && $data->getAgence()) {
                $services = $data->getAgence()->getServices();
            }
         
      
            $form->add('service',
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
            'query_builder' => function(ServiceRepository $serviceRepository) {
                    return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                },
            //'data' => $options['data']->getService(),
                'attr' => [ 'class' => 'serviceDebiteur']
            ]);
            
        })
        ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event)  {
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
        ->add('agenceEmetteur', 
        TextType::class,
        [
           'mapped' => false,
                    'label' => 'Agence',
                    'required' => false,
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options["data"]->getAgenceEmetteur() ?? null        
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
                    'data' => $options["data"]->getServiceEmetteur() ?? null
        ])
       ->add('dateDemande',
       TextType::class,
       [
        'label' => 'Date',
        'attr' => [
            'disabled' => true
        ]
       ])
       ->add('sousTypeDocument',
        TextType::class,
       [
        'label' => 'Type de Mission :',
        'attr' => [
            'disabled' => true
        ],
        'data' => $options["data"]->getSousTypeDocument()->getCodeSousType()
       ])
       ->add('categorie',
       TextType::class,
       [
        'label' => 'Catégorie :',
        'attr' => [
            'disabled' => true
        ],
        'data' => $options["data"]->getCategorie() !== null ? $options["data"]->getCategorie()->getDescription() : null
       ])
       ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            dump($data);
            $sousTypedocument = $data->getSousTypeDocument();
            $catg = $data->getCategorie();
            dump(substr($data->getAgenceEmetteur(),0,2));
            if(substr($data->getAgenceEmetteur(),0,2) === '50'){
                $rmq = $this->em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
               
           } else {
            $rmq = $this->em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
           }
           $criteria = [
            'sousTypeDoc' => $sousTypedocument,
            'rmq' => $rmq,
            'categorie' => $catg
            ];
            dump($criteria);
            $indemites = $this->em->getRepository(Indemnite::class)->findBy($criteria);
            dump($indemites);
            $sites = [];
            foreach ($indemites as $key => $value) {
                $sites[] = $value->getSite();
            }
            dd($sites);
            $form->add('site',
            EntityType::class,
            [
                'label' => 'Site:',
                'class' => Site::class,
                'choice_label' => 'nomZone',
                'choices' => $sites,
            ]);
       })
        
        ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options){
            $form = $event->getForm();
            $data = $event->getData();
          
         $montant = $this->em->getRepository(Indemnite::class)->findOneBy(['site' => $data->getSite()])->getMontant();

         $montant = $this->formatNumber($montant);
         
            $form ->add('indemniteForfaitaire',
            TextType::class,
            [
                'label' => 'Indeminté forfaitaire journalière(s)',
                'attr' => [
                    'readonly' => true
                ],
                'data' => $montant
            ]);
            
        })
        
        ->add('matricule',
        TextType::class,
       [
        'label' => 'Matricule',
        'attr' => [
            'disabled' => true
        ],
        'data' => $options["data"]->getMatricule() ?? null
       ])
       ->add('nom',
        TextType::class,
        [
            'label' => 'Nom',
            'attr' => [
                'disabled' => true
            ],
            'data' => $options["data"]->getNom() ?? null
        ])
        ->add('prenom',
        TextType::class,
        [
            'label' => 'Prénoms',
            'attr' => [
                'disabled' => true
            ],
            'data' => $options["data"]->getPrenom() ?? null
        ])
        ->add('cin',
        NumberType::class,
        [
            'mapped' => false,
            'label' => 'CIN',
            'attr' => [
                'disabled' => true
            ],
            'data' => $options["data"]->getCin() ?? null
        ])

        ->add('dateDebut', 
        DateType::class,
         [
            'widget' => 'single_text',
            'label' => 'Date debut',
            
        ]) 

        ->add('heureDebut',
        TimeType::class,
        [
           'label' => 'Heure début',
            'widget' => 'single_text', // Pour utiliser un champ de saisie unique
            'attr' => [
                'class' => 'form-control', // Pour ajouter des classes CSS si nécessaire
                'value' => '08:00', // Définit la valeur par défaut
             ],
            'input' => 'datetime', // Spécifie que l'entrée est une instance de \DateTime
        ])

        ->add('dateFin', 
        DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date fin',
            
        ]) 
        ->add('heureFin',
        TimeType::class,
        [
            'label' => 'Heure fin',
            'widget' => 'single_text', // Pour utiliser un champ de saisie unique
            'attr' => [
                'class' => 'form-control', // Pour ajouter des classes CSS si nécessaire
                'value' => '18:00', // Définit la valeur par défaut
            ],
            'input' => 'datetime', // Spécifie que l'entrée est une instance de \DateTime
        ])
        ->add('nombreJour',
        TextType::class,
        [
            'label' => 'Nombre de Jour',
            'attr' => [
                'readonly' => true
            ]
        ])
        ->add('motifDeplacement',
        TextType::class,
        [
                'label' => 'Motif',
                
        ])    
        ->add('client',
        TextType::class,
        [
            'label' => 'Nom du client',
            'required' => false,
        ])  
        
        ->add('fiche',
         NumberType::class, 
         [
            'label' => 'fiche de demande',
            'required' => false,
        ])
       
        ->add('lieuIntervention', 
        TextType::class,
         [
            'label' => 'Lieu d\'intervention',
            'required' => true,
        ])
        ->add('vehiculeSociete', 
        ChoiceType::class, [
            'label' => "Véhicule société",
            'choices' => self::OUI_NON,
            'data' => "OUI",  
        ])
        ->add('numVehicule', 
        TextType::class,
        [
            'label' => 'N°'
        ]) 
        ->add('idemnityDepl', 
        NumberType::class, [
            'label' => 'Indemnité de déplacement',
        ])

        ->add('totalIndemniteDeplacement',
        TextType::class,
        [
            'mapped' => false,
            'label' => 'Total indemnité de déplacement',
            'attr' => [
                'readonly' => true
            ]
        ])
        ->add('devis',
         ChoiceType::class, 
         [
            'label' => 'Devise :',
            'choices' => self::DEVISE,
            'data' => 'MGA'
        ])
       
        ->add('supplementJournaliere',
        NumberType::class,
        [
            'mapped' => false,
            'label' => 'supplément journalier'
        ])
        ->add('totalIndemniteForfaitaire', 
            NumberType::class, 
            [
            'label' => "Total de l'indemnite forfaitaire",
            'attr' => [
                'readonly' => true
            ]
        ])
        ->add('motifAutresDepense1',
            TextType::class,
            [
                'label' => 'Motif Autre dépense 1',
                'required' => false,
            ]) 
        ->add('autresDepense1', 
        NumberType::class,
         [
            'label' => 'Montant',
            'required' => false,
        ]) 
        ->add('motifAutresDepense2',
        TextType::class,
        [
                'label' => 'Motif Autre dépense 2',
                'required' => false,
        ]) 
        ->add('autresDepense2', 
        NumberType::class,
         [
            'label' => 'Montant',
            'required' => false,
        ]) 
        ->add('motifAutresDepense3',
        TextType::class,
        [
                'label' => 'Motif Autre dépense 3',
                'required' => false,
        ]) 
        ->add('autresDepense3', 
        NumberType::class,
         [
            'label' => 'Montant',
            'required' => false,
        ]) 

        ->add('totalAutresDepenses', 
        TextType::class,
         [
            'label' => 'Total Montant Autre Dépense',
            'required' => true,
            'attr' => [
                'readonly' => true
            ]
        ]) 
        ->add('totalGeneralPayer', 
        TextType::class,
         [
            'label' => 'Montant Total',
            'required' => true,
            'attr' => [
                'readonly' => true
            ]
        ]) 

        ->add('modePayement', 
        ChoiceType::class, [
            'label' => 'Mode paiement',
            'choices' => self::MODE_PAYEMENT
        ])
        ->add('mode',
        TextType::class,
        [
            'mapped' => false,
            'label' => 'MOBILE MONEY',
        ])

        ->add('pieceJoint1',
        FileType::class, 
        [
            'label' => 'Fichier Joint 01 (Merci de mettre un fichier PDF)',
            'required' => true,
            'constraints' => [
                new NotNull(['message' => 'Please upload a file.']),
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'application/pdf'
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF file.',
                ])
            ],
        ]
        )
        ->add('pieceJoint2',
        FileType::class, 
        [
            'label' => 'Fichier Joint 02 (Merci de mettre un fichier PDF)',
            'required' => true,
            'constraints' => [
                new NotNull(['message' => 'Please upload a file.']),
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