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
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;





class DomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('dateDemande', 
        DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date demande',
            'required' => false,
        ]) 


        ->add('sousTypeDocument', 
        EntityType::class, [
            'label' => 'sous type de document',
            'placeholder' => '-- Choisir un sous type de document --',
            'class' => SousTypeDocument::class,
            'choice_label' =>'codeDocument',
            'required' => false,
        ])

        

        ->add('matricule', 
         NumberType::class,
            [
                'label' => 'Numero Matricule',
                'required'=>false,
                // 'disabled' => true
            ])

            ->add('nomSessionUtiisateur',
        TextType::class,
        [
            'label' => 'Nom du session utilisateur',
            'required' => false,
        ])

        ->add('codeAgenceServiceDebiteur', 
        EntityType::class, [
            'label' => "code de l'agence du service debiteur",
            'placeholder' => '-- Choisir un code agence du service debiteur --',
            'class' => Agence::class,
            'choice_label' =>'codeAgence',
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
        TextType::class,
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
        TextType::class,
        [
            'label' => 'Heure fin',
            'required' => false,
        ])
        ->add('nombreJour', 
         NumberType::class,
        [
                'label' => 'Nombre jour',
                'required'=>false,
                // 'disabled' => true
        ])
        ->add('$motifDeplacement',
        TextareaType::class,
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
        ->add('numeroOr', 
        NumberType::class,
        [
            'label' => 'Numero Or *'
        ])  
        ->add('lieuIntervention', 
        TextType::class,
         [
            'label' => 'Lieu d\'intervention',
            'required' => true,
        ])
        ->add('vehiculeSociete', 
        EntityType::class, [
            'label' => "Vehicule de la societe",
            'placeholder' => '-- Choisir un vehicule --',
            'class' => Societte::class,
            'choice_label' =>'codeSociete',
            'required' => false,  
        ])
        ->add('indemniteForfaitaire', 
        EntityType::class, [
            'label' => "Indemnite forfaitaire",
            'class' => Idemnity::class,
            'choice_label' =>'catg',
            'required' => false,  
        ])
        ->add('totalIndemniteForfaitaire', 
        EntityType::class, [
            'label' => "Total de l'indemnite forfaitaire",
            'class' => Idemnity::class,
            'choice_label' =>'catg',
            'required' => false,  
        ])
        ->add('$motifAutresDepense1',
        TextareaType::class,
        [
                'label' => 'motif de l\'autre depense numero1',
                'required' => false,
        ]) 
        ->add('autresDepense1', 
        TextType::class,
         [
            'label' => 'Type de depense',
            'required' => true,
        ]) 
        ->add('$motifAutresDepense2',
        TextareaType::class,
        [
                'label' => 'motif de l\'autre depense numero2',
                'required' => false,
        ]) 
        ->add('autresDepense2', 
        TextType::class,
         [
            'label' => 'Type de depense',
            'required' => true,
        ]) 
        ->add('$motifAutresDepense3',
        TextareaType::class,
        [
                'label' => 'motif de l\'autre depense numero3',
                'required' => false,
        ]) 
        ->add('autresDepense3', 
        TextType::class,
         [
            'label' => 'Type de depense',
            'required' => true,
        ]) 
        ->add('totalAutresDepenses', 
        MoneyType::class,
         [
            'label' => 'total autre depense',
            'currency' => 'Ariary', // Spécifiez la devise si nécessaire
            'required' => true,
        ]) 
        ->add('totalGeneralPayer', 
        MoneyType::class,
         [
            'label' => 'total general payer',
            'currency' => 'Ariary', // Spécifiez la devise si nécessaire
            'required' => true,
        ]) 
        ->add('modePayement', 
        ChoiceType::class, [
            'label' => 'Mode de paiement',
            'choices' => [
                'Espèces' => 'Espèces',
                'Carte de crédit' => 'Carte de crédit',
                'Virement bancaire' => 'Virement bancaire',
                'Chèque' => 'Chèque',
                'required' => true,
            ],
        ])
        ->add('pieceJoint01',
        FileType::class, 
        [
            'label' => 'Pièce Joint 01 (PDF, JPEG, XLSX, DOCX)',
            'required' => true,
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
        ->add('pieceJoint02',
        FileType::class, 
        [
            'label' => 'Pièce Joint 02 (PDF, JPEG, XLSX, DOCX)',
            'required' => true,
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
        ->add('pieceJoint03',
        FileType::class, 
        [
            'label' => 'Pièce Joint 03 (PDF, JPEG, XLSX, DOCX)',
            'required' => true,
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
        ->add('utilisateurCreation', 
        TextType::class,
         [
            'label' => 'utilisateur de creation',
            'required' => false,
        ])
        ->add('utilisateurModification', 
        TextType::class, 
        [
            'label' => 'Utilisateur de modification',
            'required' => false, // Ajustez selon vos besoins
        ])
      
        ->add('dateModif', 
        DateType::class,
         [
            'label' => 'Date de modification',
            'required' => false,
            'widget' => 'single_text',
        ]) 
        ->add('codeStatut', 
        EntityType::class, [
            'label' => 'code de statut',
            'placeholder' => '-- Choisir un code de statut --',
            'class' => StatutDemande::class,
            'choice_label' =>'codeStatut',
        ])
        ->add('numeroTel',
        TelType::class,
        [
            'label' => 'N° téléphone',
            'required' => false,
        ])
        ->add('nom',
        TextType::class,
        [
            'label' => 'Nom',
            'required' => false,
        ])
        ->add('prenom',
        TextType::class,
        [
            'label' => 'preom',
            'required' => false,
        ])
        ->add('devis',
         TextType::class, 
         [
            'label' => 'Numéro de devis',
            'required' => false,
        ])
        ->add('libelleCodeAgenceService',
         TextType::class, 
         [
            'label' => 'Libellé Code Agence Service',
            'required' => false,
        ])
        ->add('fiche',
         TextType::class, 
         [
            'label' => 'fiche de demande',
            'required' => false,
        ])
        ->add('numVehicule', 
        NumberType::class,
        [
            'label' => 'Numero de vehicule'
        ]) 
        ->add('droitIndemnite', 
        EntityType::class, [
            'label' => "droitIndemnite",
            'class' => Idemnity::class,
            'choice_label' =>'catg',
            'required' => false,  
        ])
        ->add('categorie',
        TextType::class,
        [
            'label' => 'categorie de demande',
            'required' => false,
        ]) 
        ->add('site',
        TextType::class,
        [
            'label' => 'site',
            'required' => false,
        ]) 
        ->add('idemnityDepl', 
        EntityType::class, [
            'label' => 'indemnite de deplacement',
            'placeholder' => '-- Choisir un indemnite de deplacement --',
            'class' => Idemnity::class,
            'choice_label' =>'catg',
        ])
        
        ->add('dateCpt', 
        DateType::class,
         [
            'widget' => 'single_text',
            'label' => 'Date cpt',
            'required' => false,
        ]) 
        ->add('datePay', 
        DateType::class,
         [
            'widget' => 'single_text',
            'label' => 'Date pay',
            'required' => false,
        ]) 
        ->add('dateAnn', 
        DateType::class,
         [
            'widget' => 'single_text',
            'label' => 'Date ann',
            'required' => false,
        ]) 
        ->add('emetteur',
        TextType::class,
        [
            'label' => 'emetteur',
            'required' => false,
        ])
        ->add('debiteur',
        TextType::class,
        [
            'label' => 'debiteur',
            'required' => false,
        ])
        ->add('idStatutDemande', 
        EntityType::class, [
            'label' => "statut de demande",
            'class' => StatutDemande::class,
            'choice_label' =>'code statut',
            'required' => false,  
        ])
        ->add('dateHeureModifStatut', 
        DateTimeType::class,
         [
            'widget' => 'single_text',
            'label' => 'date et heure du modification de statut',
            'required' => false,
        ])
    ;
    }
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => Dom::class,
            ]);
        }


}