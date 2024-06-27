<?php

namespace App\Form;

use App\Controller\Controller;
use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\Societte;
use App\Entity\Application;
use App\Entity\CategorieATEAPP;
use App\Entity\WorTypeDocument;
use App\Entity\WorNiveauUrgence;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RoleRepository;
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
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Number;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Contracts\EventDispatcher\Event;

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
        'INTERNE' => 'I',
        'EXTERNE' => 'E'
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
            
            'label' => 'Service Debiteur',
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
          
            $agenceId = $data['agence'] ?? null;

            if ($agenceId) {
               
                $agence = $this->agenceRepository->find($agenceId);
                $services = $agence ? $agence->getServices() : [];

                $form->add('service', EntityType::class, [
                    'label' => 'Service Debiteur',
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'choices' => $services,
                    'required' => false,
                    'attr' => ['class' => 'serviceDebiteur']
                ]);
            //Ajouter des validations ou des traitements supplémentaires ici si nécessaire
        }})
        ->add('typeDocument', 
            EntityType::class, [
                'label' => 'type de document',
                'placeholder' => '-- Choisir--',
                'class' => WorTypeDocument::class,
                'choice_label' => 'description',
                'required' => false,
                // 'query_builder' => function(RoleRepository $roleRepository) {
                //     return $roleRepository->createQueryBuilder('r')->orderBy('r.codeDocument', 'ASC');
                // }
            ])
        
        ->add('typeReparation', 
        ChoiceType::class, 
        [
            'label' => "Type de réparation",
            'choices' => self::TYPE_REPARATION,
            'placeholder' => '-- Choisir un type de réparation --',
            'required' => false,
            'data' => 'A REALISER',
           
        ])
        ->add('reparationRealise', 
        ChoiceType::class, 
        [
            'label' => "Réparation Réalisé",
            'choices' => self::REPARATION_REALISE,
            'placeholder' => '-- Choisir le répartion réalisé --',
            'required' => false,
            'data' => 'ATELIER',
        ])
        ->add('categorieDemande', 
        EntityType::class, [
            'label' => 'catégorie de demande',
            'placeholder' => '-- Choisir une catégorie --',
            'class' => CategorieATEAPP::class,
            'choice_label' =>'libelleCategorieAteApp',
            'required' => false,
        ])
        ->add('internetExterne', 
        ChoiceType::class, 
        [
            'label' => "Interne et Externe",
            'choices' => self::INTERNE_EXTERNE,
            'placeholder' => '-- Choisir --',
            'data' => 'I',
           'required' => false,
           'attr' => [ 'class' => 'interneExterne']
        ])
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
        ->add('agenceEmetteur', 
        TextType::class,
        [
           'mapped' => false,
                    'label' => 'Agence',
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
                    'label' => 'Service',
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
            'label' => 'Nom du client',
            'required' => false,
            'attr' => [
                'class' => 'nomClient'
            ]
        ])
        ->add('numeroTel',
        TelType::class,
        [
            'label' => 'N° téléphone',
            'required' => false,
            'attr' => [
                'class' => 'numTel'
            ]
        ])
        

        ->add('datePrevueTravaux', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date prévue travaux',
            'required' => false,
        ])
        ->add('demandeDevis', 
        ChoiceType::class, 
        [
            'label' => "Demande de devis",
            'choices' => self::OUI_NON,
            'placeholder' => '-- Choisir --',
           'required' => false,
           'data' => 'OUI'
        ])
        ->add('idNiveauUrgence', 
        EntityType::class, [
            'label' => 'Niveau d\'urgence',
            'placeholder' => '-- Choisir un niveau --',
            'class' => WorNiveauUrgence::class,
            'choice_label' =>'description',
            'required' => false,
        ])
        ->add('avisRecouvrement', 
        ChoiceType::class, 
        [
            'label' => "Avis de recouvrement",
            'choices' => self::OUI_NON,
           'required' => false,
           'data' => 'NON'
        ])
        ->add('clientSousContrat', 
        ChoiceType::class, 
        [
            'label' => "client sous contrat",
            'choices' => self::OUI_NON,
           'required' => false,
           'data' => 'NON',
           'attr' => [ 'class' => 'clientSousContrat']
        ])
        ->add('objetDemande',
        TextType::class,
        [
            'label' => 'Objet de la demande',
            'required' => false,
        ])
        ->add('detailDemande',
        TextareaType::class,
        [
            'label' => 'Détail de la demande',
            'required' => false,
            'attr' => [
                'rows' => 5,
                'class' => 'detailDemande'  
              ],
        ])
        ->add('livraisonPartiel', 
        ChoiceType::class, 
        [
            'label' => "livraison Partiel",
            'choices' => self::OUI_NON,
           'required' => false,
           'data' => 'NON'
        ])
       ->add('idMateriel', 
       TextType::class, [
        'label' => " Id Matériel",
        'required' => false,
        'attr' => [ 'class' => 'idMateriel']
       ])
       ->add('numParc', 
       TextType::class, [
        'label' => " N° Parc",
        'required' => false,
        'attr' => [ 'class' => 'numParc']
       ])
       ->add('numSerie', 
       TextType::class, [
        'label' => " N° Serie",
        'required' => false,
        'attr' => [ 'class' => 'numSerie']
       ])
       ->add('pieceJoint03',
        FileType::class, 
        [
            'label' => 'Pièce Joint 03 (PDF, JPEG, XLSX, DOCX)',
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'application/pdf',
                        'image/jpeg',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF, JPEG, XLSX, or DOCX file.',
                ])
            ],
        ])
        ->add('pieceJoint02',
        FileType::class, 
        [
            'label' => 'Pièce Joint 02 (PDF, JPEG, XLSX, DOCX)',
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'application/pdf',
                        'image/jpeg',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF, JPEG, XLSX, or DOCX file.',
                ])
            ],
        ]
        )
        ->add('pieceJoint01',
        FileType::class, 
        [
            'label' => 'Pièce Joint 01 (PDF, JPEG, XLSX, DOCX)',
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'application/pdf',
                        'image/jpeg',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF, JPEG, XLSX, or DOCX file.',
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