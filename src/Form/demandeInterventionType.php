<?php

namespace App\Form;

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
   public function __construct()
   {
    //$this->serviceRepository = new ServiceRepository();
   }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        $typeReparation = [
            'EN COURS' => 'EN COURS',
            'DEJA EFFECTUEE' => 'DEJA EFFECTUEE',
            'A REALISER' => 'A REALISER'
        ];

        $reparationRealise = [
            'ATELIER' => 'ATELIER',
            'ENERGIE' => 'ENERGIE'
        ];

        $internetExterne = [
            'INTERNE' => 'INTERNE',
            'EXTERNE' => 'EXTERNE'
        ];

        $ouiNon = [
            'NON' => 'NON',
            'OUI' => 'OUI'
        ];
        
        $builder
        ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $agence = $event->getData()['agenceEmetteur'] ?? null;

           // $services = $agence === null ? [] : $serviceRepository->findByService($agence, ['codeService' => 'ASC']);

            $event->getForm()->add('serviceemetteur',
            EntityType::class,
        [
            'mapped' => false,
            'label' => 'Agence Debiteur',
            'placeholder' => '-- Choisir une agence Debiteur --',
            'class' => Agence::class,
            'choice_label' => function (Agence $agence): string {
                return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
            },
            //'choices' => $services,
            'required' => false,
        ]);
            
        })
        ->add('typeDocument', 
            EntityType::class, [
                'label' => 'type de document',
                'placeholder' => '-- Choisir un type de document --',
                'class' => WorTypeDocument::class,
                'choice_label' =>'codeDocument',
                'required' => false,
                // 'query_builder' => function(RoleRepository $roleRepository) {
                //     return $roleRepository->createQueryBuilder('r')->orderBy('r.codeDocument', 'ASC');
                // }
            ])
        ->add('codeSociete', 
        EntityType::class, [
            'label' => 'Société',
            'placeholder' => '-- Choisir une société --',
            'class' => Societte::class,
            'choice_label' =>'codeSociete',
            'required' => false,
        ])
        ->add('typeReparation', 
        ChoiceType::class, 
        [
            'label' => "Type de réparation",
            'choices' => $typeReparation,
            'placeholder' => '-- Choisir un type de réparation --',
            'required' => false,
            'data' => 'A REALISER',
           
        ])
        ->add('reparationRealise', 
        ChoiceType::class, 
        [
            'label' => "Type de réparation",
            'choices' => $reparationRealise,
            'placeholder' => '-- Choisir le répartion réalisé --',
            'required' => false,
            'data' => 'ATELIER',
        ])
        ->add('categorieDemande', 
        EntityType::class, [
            'label' => 'catégorie de demande',
            'placeholder' => '-- Choisir une catégorie --',
            'class' => CategorieATEAPP::class,
            'choice_label' =>'codeSociete',
            'required' => false,
        ])
        ->add('internetExterne', 
        ChoiceType::class, 
        [
            'label' => "Interne et Externe",
            'choices' => $internetExterne,
            'placeholder' => '-- Choisir --',
           'required' => false,
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
        // ->add('agenceDebiteur', 
        // EntityType::class,
        // [
        //     'mapped' => false,
        //     'label' => 'Agence Debiteur',
        //     'placeholder' => '-- Choisir une agence Debiteur --',
        //     'class' => Agence::class,
        //     'choice_label' => function (Agence $agence): string {
        //         return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
        //     },
        //     'required' => false,
        // ])
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
        ->add('serviceDebiteur', 
        EntityType::class,
        [
            'mapped' => false,
            'label' => 'Service Débiteut',
            'placeholder' => '-- Choisir une service débiteur --',
            'class' => Service::class,
            'choice_label' => function (Service $service): string {
                return $service->getCodeService() . ' ' . $service->getLibelleService();
            },
            'required' => false,
        ])
        ->add('nomClient',
        TextType::class,
        [
            'label' => 'Nom du client',
            'required' => false,
        ])
        ->add('numeroTel',
        TelType::class,
        [
            'label' => 'N° téléphone',
            'required' => false,
        ])
        
        /**à discuter */
        /*
        ->add('dateOr', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date OR',
            'required' => false,
        ])
        ->add('heureOR',
        TextType::class,
        [
            'label' => 'Heure OR',
            'required' => false,
        ])
        ->add('mailDemandeur',
        EmailType::class,
        [
            'label' => 'Mail du demandeur',
            'required' => false,
        ])
            */
        /**fin à discuter */

        ->add('datePrevueTravaux', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date prévue travaux',
            'required' => false,
        ])
        ->add('demandeDevis', 
        ChoiceType::class, 
        [
            'label' => "Demande de devis",
            'choices' => $ouiNon,
            'placeholder' => '-- Choisir --',
           'required' => false,
        ])
        ->add('idNiveauUrgence', 
        EntityType::class, [
            'label' => 'Niveau d\'urgence',
            'placeholder' => '-- Choisir un niveau --',
            'class' => WorNiveauUrgence::class,
            'choice_label' =>'codeSociete',
            'required' => false,
        ])
        ->add('avisRecouvrement', 
        ChoiceType::class, 
        [
            'label' => "Avis de recouvrement",
            'choices' => $ouiNon,
           'required' => false,
           'data' => 'NON'
        ])
        ->add('clientSousContrat', 
        ChoiceType::class, 
        [
            'label' => "client sous contrat",
            'choices' => $ouiNon,
           'required' => false,
           'data' => 'NON'
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
        ])
        ->add('livraisonPartiel', 
        ChoiceType::class, 
        [
            'label' => "livraison Partiel",
            'choices' => $ouiNon,
           'required' => false,
           'data' => 'NON'
        ])
       ->add('idMateriel', 
       NumberType::class, [
        'label' => " Id Matériel",
        'required' => false,
        
       ])
       ->add('pieceJoint03',
        FileType::class, 
        [
            'label' => 'Pièce Joint 03 (PDF, JPEG, XLSX, DOCX)',
            'required' => false,
            'constraints' => [
                new NotNull(['message' => 'Please upload a file.']),
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
                new NotNull(['message' => 'Please upload a file.']),
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
                new NotNull(['message' => 'Please upload a file.']),
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