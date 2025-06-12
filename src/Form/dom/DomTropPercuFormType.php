<?php

namespace App\Form\dom;


use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dom\SousTypeDocument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class DomTropPercuFormType extends AbstractType
{
    use FormatageTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** 
         * @var Dom $dom
         */
        $dom = $options['data'];
        $idSousTypeDocument = $dom->getSousTypeDocument()->getId();

        $payement = '';

        $builder
            ->add(
                'agence',
                EntityType::class,
                [
                    'label' => 'Agence Debiteur',
                    'class' => Agence::class,
                    'required' => false,
                    'data' => $dom->getAgenceDebiteurId(),
                    'choice_label' => function (Agence $agence): string {
                        return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                    },
                    'attr' => [
                        'class' => 'agenceDebiteur disabled',
                    ]
                ]
            )
            ->add(
                'service',
                EntityType::class,
                [
                    'label' => 'Service Débiteur',
                    'class' => Service::class,
                    'required' => false,
                    'data' => $dom->getServiceDebiteurId(),
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'attr' => [
                        'class' => 'serviceDebiteur disabled',
                    ]
                ]
            )
            ->add(
                'agenceEmetteurId',
                EntityType::class,
                [
                    'label' => 'Agence',
                    'class' => Agence::class,
                    'data' => $dom->getAgenceEmetteurId(),
                    'choice_label' => function (Agence $agence): string {
                        return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                    },
                    'attr' => [
                        'class' => 'disabled',
                    ],
                ]
            )
            ->add(
                'serviceEmetteurId',
                EntityType::class,
                [
                    'label' => 'Service',
                    'class' => Service::class,
                    'data' => $dom->getServiceEmetteurId(),
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'attr' => [
                        'class' => 'disabled',
                    ],
                ]
            )
            ->add(
                'dateDemande',
                DateType::class,
                [
                    'label' => 'Date',
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'disabled',
                    ],
                    'data' => $dom->getDateDemande()
                ]
            )
            ->add(
                'sousTypeDocument',
                EntityType::class,
                [
                    'label' => 'Type de Mission :',
                    'class' => SousTypeDocument::class,
                    'choice_label' => function (SousTypeDocument $sousTypeDocument): string {
                        return $sousTypeDocument->getCodeSousType();
                    },
                    'attr' => [
                        'class' => 'disabled',
                    ],
                    'data' => $dom->getSousTypeDocument()
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
                        'class' => 'disabled',
                    ],
                    'data' => $dom->getCategorie()
                ]
            )->add(
                'site',
                TextType::class,
                [
                    'label' => 'Site:',
                    'attr' => [
                        'class' => 'disabled',
                    ],
                    'data' => $dom->getSite()
                ]
            )
            ->add(
                'indemniteForfaitaire',
                TextType::class,
                [
                    'label' => 'Indeminté forfaitaire journalière(s)',
                    'attr' => [
                        'class' => 'disabled',
                    ],
                    'data' =>  $dom->getIndemniteForfaitaire()
                ]
            )
            ->add(
                'matricule',
                TextType::class,
                [
                    'label' => 'Matricule',
                    'attr' => [
                        'class' => 'disabled',
                    ],
                    'data' => $dom->getMatricule()
                ]
            )
            ->add(
                'nom',
                TextType::class,
                [
                    'label' => 'Nom',
                    'attr' => [
                        'class' => 'disabled',
                    ],
                    'data' => $dom->getNom()
                ]
            )
            ->add(
                'prenom',
                TextType::class,
                [
                    'label' => 'Prénoms',
                    'attr' => [
                        'class' => 'disabled',
                    ],
                    'data' => $dom->getPrenom()
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
                    'required' => false,
                    'data'  => $dom->getMotifDeplacement(),
                ]
            )
            ->add(
                'client',
                TextType::class,
                [
                    'label' => 'Nom du client',
                    'required' => false,
                    'attr' => [
                        'class' => 'disabled',
                    ],
                    'data'  => $dom->getClient(),
                ]
            )
            ->add(
                'fiche',
                TextType::class,
                [
                    'label' => 'N° fiche',
                    'required' => false,
                    'attr' => [
                        'class' => 'disabled',
                    ],
                    'data'  => $dom->getFiche(),
                ]
            )
            ->add(
                'lieuIntervention',
                TextType::class,
                [
                    'label' => 'Lieu d\'intervention',
                    'required' => false,
                    'data'  => $dom->getLieuIntervention(),
                    'attr' => [
                        'class' => 'disabled',
                    ],
                ]
            )
            ->add(
                'vehiculeSociete',
                TextType::class,
                [
                    'label' => "Véhicule société",
                    'required' => false,
                    'data'  => $dom->getVehiculeSociete(),
                    'attr' => [
                        'class' => 'disabled',
                    ],
                ]
            )
            ->add(
                'numVehicule',
                TextType::class,
                [
                    'label' => 'N°',
                    'required' => false,
                    'data'  => $dom->getNumVehicule(),
                    'attr' => [
                        'class' => 'disabled',
                    ],
                ]
            )
            ->add(
                'idemnityDepl',
                TextType::class,
                [
                    'label' => 'Indemnité de déplacement',
                    'required' => false,
                    'data'  => $dom->getIdemnityDepl(),
                    'attr' => [
                        'class' => 'disabled',
                    ],
                ]
            )
            ->add(
                'totalIndemniteDeplacement',
                TextType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Total indemnité de déplacement',
                    'attr' => [
                        'class' => 'disabled',
                    ]
                ]
            )
            ->add(
                'devis',
                TextType::class,
                [
                    'label' => 'Devise :',
                    'data' => $dom->getDevis(),
                    'attr' => [
                        'class' => 'disabled',
                    ]
                ]
            )

            ->add(
                'supplementJournaliere',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'supplément journalier',
                    'data' => $dom->getDroitIndemnite(),
                    'required' => false,
                    'attr' => [
                        'class' => 'disabled',
                    ]
                ]
            )
            ->add(
                'totalIndemniteForfaitaire',
                TextType::class,
                [
                    'label' => "Total de l'indemnite forfaitaire",
                    'data' => $dom->getTotalIndemniteForfaitaire(),
                    'attr' => [
                        'class' => 'disabled',
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
                TextType::class,
                [
                    'label' => 'Mode paiement',
                    'required' => false,
                    'data' => $payement,
                    'attr' => [
                        'class' => 'disabled',
                    ],
                ]
            )
            ->add(
                'mode',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'MOBILE MONEY',
                    'required' => false,
                    'data' => $payement,
                    'attr' => [
                        'class' => 'disabled',
                    ],
                ]
            )
            ->add(
                'pieceJoint01',
                FileType::class,
                [
                    'label' => 'Fichier Joint 01 (Merci de mettre un fichier PDF)',
                    'required' => false,
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
                    'required' => false,
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
