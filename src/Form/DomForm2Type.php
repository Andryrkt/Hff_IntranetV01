<?php

namespace App\Form;

use App\Entity\Dom;
use App\Entity\Agence;
use App\Entity\Idemnity;
use App\Entity\Societte;
use App\Entity\StatutDemande;
use App\Entity\WorTypeDocument;
use App\Entity\SousTypeDocument;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RoleRepository;
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
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;





class DomForm2Type extends AbstractType
{

    const OUI_NON = [
        'NON' => 'NON',
        'OUI' => 'OUI'
    ];
    const DEVISE = [
        'MGA' => 'MGA'
    ];

    const MODE_PAYEMENT = [
            'ESPECES' => 'ESPECES',
            'CARTE DE CREDIT' => 'CARTE DE CREDIT',
            'VIREMENT BANCAIRE' => 'VIREMENT BANCAIRE',
            'CHEQUE' => 'CHEQUE',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
       
        ->add('site',
        TextType::class,
        [
            'label' => 'site',
            'required' => false,
        ]) 
        

        ->add('dateDebut', 
        DateType::class,
         [
            'widget' => 'single_text',
            'label' => 'Date debut',
            'required' => false,
        ]) 

        ->add('heureDebut',
        TimeType::class,
        [
            'label' => 'Heure debut',
            'required' => false,
        ])

        
        ->add('dateFin', 
        DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date fin',
            'required' => false,
        ]) 

        ->add('heureFin',
        TimeType::class,
        [
            'label' => 'Heure fin',
            'required' => false,
        ])

       
        ->add('motifDeplacement',
        TextType::class,
        [
                'label' => 'motif de deplacement',
                'required' => false,
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
            'label' => "Vehicule de la societe",
            'choices' => self::OUI_NON,
            'data' => "OUI",  
        ])
        ->add('numVehicule', 
        TextType::class,
        [
            'label' => 'Numero de vehicule'
        ]) 
        ->add('idemnityDepl', 
        NumberType::class, [
            'label' => 'indemnite de deplacement',
        ])

        ->add('indemniteForfaitaire', 
        EntityType::class, [
            'label' => "Indemnite forfaitaire",
            'class' => Idemnity::class,
            'choice_label' =>'catg',
            'required' => false,  
        ])

        ->add('devis',
         ChoiceType::class, 
         [
            'label' => 'devis',
            'choices' => self::DEVISE,
            'data' => 'MGA'
        ])
        ->add('indemniteForfaitaireJournaliere',
        NumberType::class,
        [
            'mapped' => false,
            'label' => 'Indeminté forfaitaire journalière(s)'
        ])
        ->add('supplementJournaliere',
        NumberType::class,
        [
            'mapped' => false,
            'label' => 'Supplément journalié'
        ])
        ->add('totalIndemniteForfaitaire', 
            NumberType::class, [
            'label' => "Total de l'indemnite forfaitaire",
        ])
        ->add('motifAutresDepense1',
            TextType::class,
            [
                'label' => 'motif autre depense numero1',
                'required' => false,
            ]) 
        ->add('autresDepense1', 
        NumberType::class,
         [
            'label' => 'Type de depense',
            'required' => false,
        ]) 
        ->add('motifAutresDepense2',
        TextType::class,
        [
                'label' => 'motif de l\'autre depense numero2',
                'required' => false,
        ]) 
        ->add('autresDepense2', 
        NumberType::class,
         [
            'label' => 'Type de depense',
            'required' => false,
        ]) 
        ->add('motifAutresDepense3',
        TextType::class,
        [
                'label' => 'motif de l\'autre depense numero3',
                'required' => false,
        ]) 
        ->add('autresDepense3', 
        NumberType::class,
         [
            'label' => 'Type de depense',
            'required' => false,
        ]) 

        ->add('totalAutresDepenses', 
        MoneyType::class,
         [
            'label' => 'total Montant autre depense',
            'currency' => 'Ariary', // Spécifiez la devise si nécessaire
            'required' => true,
        ]) 
        ->add('totalGeneralPayer', 
        MoneyType::class,
         [
            'label' => 'Montant total',
            'currency' => 'Ariary', // Spécifiez la devise si nécessaire
            'required' => true,
        ]) 

        ->add('modePayement', 
        ChoiceType::class, [
            'label' => 'Mode de paiement',
            'choices' => self::MODE_PAYEMENT
        ])

        ->add('pieceJoint01',
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
        ->add('pieceJoint02',
        FileType::class, 
        [
            'label' => 'Fichier Joint 01 (Merci de mettre un fichier PDF)',
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