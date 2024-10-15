<?php

namespace App\Form\dit;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class demandeInterventionType extends AbstractType
{
    private $serviceRepository;
    private $agenceRepository;
    const TYPE_REPARATION = [
        'EN COURS' => 'EN COURS',
        'DEJA EFFECTUEE' => 'DEJA EFFECTUEE',
        'A REALISER' => 'A REALISER'
    ];

    const REPARATION_REALISE = [
        'ATELIER' => 'ATELIER',
        'ENERGIE' => 'ENERGIE'
    ];

    const INTERNE_EXTERNE = [
        'INTERNE' => 'INTERNE',
        'EXTERNE' => 'EXTERNE'
    ];

    const OUI_NON = [
        'NON' => 'NON',
        'OUI' => 'OUI'
    ];

   public function __construct()
   {
    $this->serviceRepository = Controller::getEntity()->getRepository(Service::class);
    $this->agenceRepository = controller::getEntity()->getRepository(Agence::class);
   }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
        
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options){
                $form = $event->getForm();
                $data = $event->getData();
                $services = null;

                if ($data instanceof DemandeIntervention && $data->getAgence()) {
                    $services = $data->getAgence()->getServices();
                }
                //$services = $data->getAgence()->getServices();
                // $agence = $event->getData()->getAgence() ?? null;
                // $services = $agence->getServices();
        
                $form->add('service',
                EntityType::class,
                [
                
                'label' => 'Service Débiteur *',
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
                    
                    $agence = $this->agenceRepository->find($agenceId);
                    $services = $agence->getServices();
                    
                    $form->add('service', EntityType::class, [
                        'label' => 'Service Débiteur *',
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
            ->add('typeDocument', 
                EntityType::class, [
                    'label' => 'Type de document *',
                    'placeholder' => '-- Choisir--',
                    'class' => WorTypeDocument::class,
                    'choice_label' => 'description',
                    'required' => true,
                    'constraints' => [
                        new Assert\NotBlank(['message'=>'le type de document doit être sélectionné'])
                    ]
                    // 'query_builder' => function(RoleRepository $roleRepository) {
                    //     return $roleRepository->createQueryBuilder('r')->orderBy('r.codeDocument', 'ASC');
                    // }
                ])
            
            ->add('typeReparation', 
            ChoiceType::class, 
            [
                'label' => "Type de réparation *",
                'choices' => self::TYPE_REPARATION,
                'placeholder' => '-- Choisir un type de réparation --',
                'required' => true,
                'data' => 'A REALISER',
                'constraints' => [
                        new Assert\NotBlank(['message'=>'le type de réparation doit être sélectionné'])
                    ]
            
            ])
            ->add('reparationRealise', 
            ChoiceType::class, 
            [
                'label' => "Réparation réalisé par *",
                'choices' => self::REPARATION_REALISE,
                'placeholder' => '-- Choisir le répartion réalisé --',
                'required' => true,
                'data' => 'ATELIER',
                'constraints' => [
                        new Assert\NotBlank(['message'=>'le réparation réalisé par doit être sélectionné'])
                    ]
            ])
            ->add('categorieDemande', 
            EntityType::class, [
                'label' => 'Catégorie de demande *',
                'placeholder' => '-- Choisir une catégorie --',
                'class' => CategorieATEAPP::class,
                'choice_label' =>'libelleCategorieAteApp',
                'required' => true,
                'constraints' => [
                        new Assert\NotBlank(['message'=>'le catégorie de demande doit être sélectionné'])
                    ]
            ])
            ->add('internetExterne', 
            ChoiceType::class, 
            [
                'label' => "Interne et Externe *",
                'choices' => self::INTERNE_EXTERNE,
                'placeholder' => '-- Choisir --',
                'data' => 'INTERNE',
            'required' => false,
            'attr' => [ 'class' => 'interneExterne']
            ])
            ->add('agence', 
            EntityType::class,
            [
                
                'label' => 'Agence Debiteur *',
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
            
            ->add('agenceEmetteur', 
            TextType::class,
            [
            'mapped' => false,
                        'label' => 'Agence *',
                        'required' => false,
                        'attr' => [
                            'readonly' => true
                        ],
                        'data' => $options["data"]->getAgenceEmetteur() ?? null
            ])
        
            ->add('serviceEmetteur', 
            TextType::class,
            [
                'mapped' => false,
                        'label' => 'Service *',
                        'required' => false,
                        'attr' => [
                            'readonly' => true,
                            'disable' => true
                        ],
                        'data' => $options["data"]->getServiceEmetteur() ?? null
            ])
            // ->add('service', 
            // EntityType::class,
            // [
                
            //     'label' => 'Service Débiteut',
            //     'placeholder' => '-- Choisir une service débiteur --',
            //     'class' => Service::class,
            //     'choice_label' => function (Service $service): string {
            //         return $service->getCodeService() . ' ' . $service->getLibelleService();
            //     },
            //     'required' => false,
                
            // ])
            ->add('nomClient',
            TextType::class,
            [
                'label' => 'Nom du client (*EXTERNE)',
                'required' => true,
                'attr' => [
                    'class' => 'nomClient noEntrer'
                ]
            ])
            ->add('numeroTel',
            TelType::class,
            [
                'label' => 'N° téléphone (*EXTERNE)',
                'required' => true,
                'attr' => [
                    'class' => 'numTel noEntrer'
                ]
            ])
            

            ->add('datePrevueTravaux', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date prévue travaux *',
                'required' => true,
                'attr' => [ 'class' => 'noEntrer'],
                'constraints' => [
                        new Assert\NotBlank(['message'=>'la date ne doit pas être vide'])
                    ]
            ])
            ->add('demandeDevis', 
            ChoiceType::class, 
            [
                'label' => "Demande de devis *",
                'choices' => self::OUI_NON,
                'placeholder' => '-- Choisir --',
                'required' => false,
                'data' => 'NON',
                'attr' => [
                    'disabled' => true,
                ]
            ])
            ->add('idNiveauUrgence', 
            EntityType::class, [
                'label' => 'Niveau d\'urgence *',
                'placeholder' => '-- Choisir un niveau --',
                'class' => WorNiveauUrgence::class,
                'choice_label' =>'description',
                'required' => false,
            ])
            ->add('avisRecouvrement', 
            ChoiceType::class, 
            [
                'label' => "Avis de recouvrement *",
                'choices' => self::OUI_NON,
            'required' => false,
            'data' => 'NON'
            ])
            ->add('clientSousContrat', 
            ChoiceType::class, 
            [
                'label' => "Client sous contrat (*EXTERNE)",
                'choices' => self::OUI_NON,
            'required' => false,
            'data' => 'NON',
            'attr' => [ 'class' => 'clientSousContrat']
            ])
            ->add('objetDemande',
            TextType::class,
            [
                'label' => 'Objet de la demande *',
                'required' => true,
                'attr' => [ 'class' => 'noEntrer'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'l\'objet de la demande ne peut pas être vide .', // Message d'erreur si le champ est vide
                    ]),
                ],
            ])
            ->add('detailDemande',
            TextareaType::class,
            [
                'label' => 'Détail de la demande *',
                'required' => true,
                'attr' => [
                    'rows' => 5,
                    'class' => 'detailDemande'  
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'le detail de la demande ne peut pas être vide .', // Message d'erreur si le champ est vide
                    ]),
                ],
            ])
            ->add('livraisonPartiel', 
            ChoiceType::class, 
            [
                'label' => "Livraison Partielle *",
                'choices' => self::OUI_NON,
            'required' => false,
            'data' => 'NON'
            ])
        ->add('idMateriel', 
        TextType::class, [
            'label' => " Id Matériel *",
            'required' => true,
            'attr' => [ 'class' => 'noEntrer']
        ])
        ->add('numParc', 
        TextType::class, [
            'label' => " N° Parc",
            'required' => true,
            'attr' => [ 'class' => 'noEntrer']
        ])
        ->add('numSerie', 
        TextType::class, [
            'label' => " N° Serie",
            'required' => true,
            'attr' => [ 'class' => 'noEntrer']
        ])
        ->add('pieceJoint03',
            FileType::class, 
            [
                'label' => 'Pièce Jointe 03 (PDF)',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            // 'image/jpeg',
                            // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            // 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF file.',
                    ])
                ],
            ])
            ->add('pieceJoint02',
            FileType::class, 
            [
                'label' => 'Pièce Jointe 02 (PDF)',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            // 'image/jpeg',
                            // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            // 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF file.',
                    ])
                ],
            ]
            )
            ->add('pieceJoint01',
            FileType::class, 
            [
                'label' => 'Pièce Jointe 01 (PDF)',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            // 'image/jpeg',
                            // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            // 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF file.',
                    ])
                ],
            ]
            )
            
            // ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event){
            //     $nomUtilisateur = $event->getData();
            //     dd($nomUtilisateur);
            // })
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeIntervention::class,
        ]);
    }


}