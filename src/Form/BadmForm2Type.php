<?php


namespace App\Form;

use App\Entity\Badm;
use App\Entity\Agence;
use App\Entity\Casier;
use App\Entity\Service;
use App\Controller\Controller;
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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class BadmForm1Type extends AbstractType
{
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
                    'disabled' => true
                ],
                'data' => $options["data"]->getNumParc()
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
            'data' => $options["data"]->getDateCreation()
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
        //AGENCE -SERVICE EMETTEUR
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

            if ($data instanceof Badm && $data->getAgence()) {
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
        ->add('casierDestinataire',
        EntityType::class,
        [
            'label' => 'Casier Destinataire',
            'class' => Casier::class,
            'choice_label' => 'casier',
            'placeholder' => ' -- Choisir un casier --'
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
        TextType::class,
        [
            'label' => 'Date de mise en location',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
        ])
        //Valeur
        ->add('coutAcquisition',
        TextType::class,
        [
            'label' => 'Coût d\'acquisition',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
        ])
        ->add('amortissement',
        TextType::class,
        [
            'label' => 'Amortissement',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
        ])
        ->add('valeurNetComptable',
        TextType::class,
        [
            'label' => 'VNC',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
        ])
        //CESSION ACTIF
        ->add('nomClient',
        TextType::class,
        [
            'label' => 'Nom client',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
        ])
        ->add('modalitePaiement',
        ChoiceType::class,
        [
            'label' => 'Modalité de paiement',
            'choices' => self::MODE_PAYEMENT
        ])
        ->add('prixVenteHt',
        TextType::class,
        [
            'label' => 'Prix HT',
            'attr' => [
                'disabled' => true
            ],
            'required' => false,
        ])
        //MISE AU REBUT
        ->add('motifMiseRebut',
        TextType::class,
        [
            'label' => 'Motif de mise au rebut',
            'attr' => [
                'disabled' => true
            ],
        ]
        )
        ->add('nomImage',
        FileType::class, 
        [
            'label' => 'Image (Merci de mettre un fichier image)',
            'required' => true,
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
        ; 
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Badm::class
        ]);
    }
}
