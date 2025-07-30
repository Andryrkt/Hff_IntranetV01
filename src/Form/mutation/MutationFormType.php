<?php

namespace App\Form\mutation;

use App\Entity\admin\Agence;

use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\Personnel;
use App\Entity\admin\dom\Indemnite;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\admin\AgenceServiceIrium;
use App\Entity\admin\dom\Site;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\Service;
use App\Entity\mutation\Mutation;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\dom\CatgRepository;
use App\Repository\admin\PersonnelRepository;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MutationFormType extends AbstractType
{
    private $em;
    const MODE_PAYEMENT = [
        'MOBILE MONEY'      => 'MOBILE MONEY',
        'VIREMENT BANCAIRE' => 'VIREMENT BANCAIRE',
    ];
    const AVANCE_SUR_INDEMNITE = [
        'OUI' => 'OUI',
        'NON' => 'NON',
    ];

    public function __construct()
    {
        $this->em = Controller::getEntity();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $indemites = $this->em->getRepository(Indemnite::class)->findBy(['sousTypeDoc' => '5']);

        $sites = [];
        foreach ($indemites as $value) {
            $sites[] = $value->getSite();
        }

        $agenceEmetteur = $options["data"]->getAgenceEmetteur();
        $serviceEmetteur = $options["data"]->getServiceEmetteur();

        $builder
            ->add(
                'agenceEmetteur',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'Agence Emetteur / Origine',
                    'attr'     => [
                        'readonly' => true,
                        'class'    => 'readonly',
                    ],
                    'data'     => $agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence()
                ]
            )
            ->add(
                'serviceEmetteur',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'Service Emetteur / Origine',
                    'attr'     => [
                        'readonly' => true,
                        'class'    => 'readonly',
                    ],
                    'data'     => $serviceEmetteur->getCodeService() . ' ' . $serviceEmetteur->getLibelleService()
                ]
            )
            ->add(
                'agenceDebiteur',
                EntityType::class,
                [
                    'label'         => 'Agence Debiteur / Destination',
                    'placeholder'   => '-- Choisir une agence Debiteur --',
                    'class'         => Agence::class,
                    'attr'          => [
                        'class' => 'agenceDebiteur',
                    ],
                    'choice_label'  => function (Agence $agence): string {
                        return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                    },
                    'query_builder' => function (AgenceRepository $agenceRepository) {
                        return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
                    }
                ]
            )
            ->add(
                'categorie',
                EntityType::class,
                [
                    'label'         => 'Catégorie professionnelle',
                    'placeholder'   => '-- Choisir une catégorie professionnelle --',
                    'class'         => Catg::class,
                    'choice_label'  => 'description',
                    'query_builder' => function (CatgRepository $catg) {
                        return $catg->createQueryBuilder('c')
                            ->where('c.id NOT IN (:excluded)')
                            ->setParameter('excluded', [5, 6, 7])
                            ->orderBy('c.description', 'ASC');
                    }
                ]
            )
            ->add(
                'site',
                EntityType::class,
                [
                    'label'        => 'Site d\'affectation',
                    'class'        => Site::class,
                    'placeholder'  => '-- choisir une site --',
                    'choice_label' => 'nomZone',
                    'choices'      => $sites
                ]
            )
            ->add(
                'dateDebut',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'label'  => 'Date début affectation / Frais d\'installation',
                ]
            )
            ->add(
                'dateFin',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'label'  => 'Date fin frais / indemnité',
                ]
            )
            ->add(
                'motifMutation',
                TextType::class,
                [
                    'label'       => 'Motif de la mutation',
                    'constraints' => [
                        new NotBlank(['message' => 'Le motif de mutation ne peut pas être vide.']),
                        new Length([
                            'min'        => 3,
                            'minMessage' => 'Le motif de mutation doit comporter au moins {{ limit }} caractères',
                            'max'        => 100,
                            'maxMessage' => 'Le motif de mutation ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'client',
                TextType::class,
                [
                    'label'       => 'Nom du client',
                    'required'    => false,
                    'constraints' => [
                        new Length([
                            'min'        => 3,
                            'minMessage' => 'Le Client doit comporter au moins {{ limit }} caractères',
                            'max'        => 100,
                            'maxMessage' => 'Le Client ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'nombreJourAvance',
                TextType::class,
                [
                    'label' => 'Nombre de Jour',
                    'attr'     => [
                        'readonly' => true,
                        'class'    => 'readonly',
                    ],
                ]
            )
            ->add(
                'lieuMutation',
                TextType::class,
                [
                    'label'       => 'Lieu d\'affectation',
                    'constraints' => [
                        new NotBlank(['message' => 'Le lieu d\'affectation ne peut pas être vide.']),
                        new Length([
                            'min'        => 3,
                            'minMessage' => 'Le lieu doit comporter au moins {{ limit }} caractères',
                            'max'        => 100,
                            'maxMessage' => 'Le lieu ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'avanceSurIndemnite',
                ChoiceType::class,
                [
                    'mapped'  => false,
                    'label'   => 'Frais d\'installation / Avances sur Indemnité',
                    'choices' => self::AVANCE_SUR_INDEMNITE
                ]
            )
            ->add(
                'indemniteForfaitaire',
                TextType::class,
                [
                    'label' => 'Indemnité forfaitaire / jour',
                    'attr'  => [
                        'readonly' => true,
                        'class'    => 'readonly',
                    ]
                ]
            )
            ->add(
                'supplementJournaliere',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'Supplément journalier',
                    'required' => false
                ]
            )
            ->add(
                'totalIndemniteForfaitaire',
                TextType::class,
                [
                    'label' => "Total de l'indemnité forfaitaire",
                    'attr'     => [
                        'readonly' => true,
                        'class'    => 'readonly',
                    ],
                ]
            )
            ->add(
                'motifAutresDepense1',
                TextType::class,
                [
                    'label'       => 'Motif Autre dépense 1',
                    'required'    => false,
                    'constraints' => [
                        new Length([
                            'min'        => 3,
                            'minMessage' => 'Le motif autre dépense 1 doit comporter au moins {{ limit }} caractères',
                            'max'        => 50,
                            'maxMessage' => 'Le motif autre dépense 1 ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'autresDepense1',
                TextType::class,
                [
                    'label'    => 'Montant (Autre Dépense 1)',
                    'required' => false,
                ]
            )
            ->add(
                'motifAutresDepense2',
                TextType::class,
                [
                    'label'       => 'Motif Autre dépense 2',
                    'attr'        => ['class' => 'disabled'],
                    'required'    => false,
                    'constraints' => [
                        new Length([
                            'min'        => 3,
                            'minMessage' => 'Le motif autre dépense 2 doit comporter au moins {{ limit }} caractères',
                            'max'        => 50,
                            'maxMessage' => 'Le motif autre dépense 2 ne peut pas dépasser {{ limit }} caractères',
                        ]),
                    ],
                ]
            )
            ->add(
                'autresDepense2',
                TextType::class,
                [
                    'label'    => 'Montant (Autre Dépense 2)',
                    'required' => false,
                    'attr'        => ['class' => 'disabled'],
                ]
            )
            ->add(
                'totalAutresDepenses',
                TextType::class,
                [
                    'label'    => 'Total Montant Autre Dépense',
                    'attr'     => [
                        'readonly' => true,
                        'class'    => 'readonly',
                    ],
                ]
            )
            ->add(
                'totalGeneralPayer',
                TextType::class,
                [
                    'label' => 'Montant Total (Autres dépenses + Indemnité)',
                    'attr'  => [
                        'readonly' => true,
                        'class'    => 'readonly',
                    ]
                ]
            )
            ->add(
                'modePaiementLabel',
                ChoiceType::class,
                [
                    'mapped'      => false,
                    'label'       => 'Mode paiement',
                    'choices'     => self::MODE_PAYEMENT,
                    'placeholder' => '-- Choisir une mode de paiement --',
                    'data'        => 'MOBILE MONEY',
                ]
            )
            ->add(
                'modePaiementValue',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'TEL'
                ]
            )
            ->add(
                'pieceJoint01',
                FileType::class,
                [
                    'label'       => 'Fichier Joint 01 (Merci de mettre un fichier PDF)',
                    'required'    => false,
                    'constraints' => [
                        new File([
                            'maxSize'   => '5M',
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Veuillez mettre un fichier PDF valide.',
                        ])
                    ],
                ]
            )
            ->add(
                'pieceJoint02',
                FileType::class,
                [
                    'label'       => 'Fichier Joint 02 (Merci de mettre un fichier PDF)',
                    'required'    => false,
                    'constraints' => [
                        new File([
                            'maxSize'   => '5M',
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Veuillez mettre un fichier PDF valide.',
                        ])
                    ],
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();

                $codeAgence = $options['data']->getAgenceEmetteur()->getCodeAgence();   // obtenir le code agence de l'utilisateur
                $codeService = $options['data']->getServiceEmetteur()->getCodeService();  // obtenir le code service de l'utilisateur

                // Récupération de l'ID du service agence irium
                $agenceServiceIriumId = $this->em->getRepository(AgenceServiceIrium::class)
                    ->findId($codeAgence, $codeService, $options['data']->getServiceEmetteur());

                $services = null;

                // Ajout du champ 'matriculeNom'
                $form
                    ->add(
                        'matriculeNomPrenom',
                        EntityType::class,
                        [
                            'mapped'        => false,
                            'label'         => 'Matricule, nom et prénoms',
                            'class'         => Personnel::class,
                            'placeholder'   => '-- choisir un personnel --',
                            'choice_label'  => function (Personnel $personnel): string {
                                return $personnel->getMatricule() . ' ' . $personnel->getNom() . ' ' . $personnel->getPrenoms();
                            },
                            'query_builder' => function (PersonnelRepository $repository) use ($agenceServiceIriumId) {
                                return $repository->createQueryBuilder('p')
                                    ->where('p.agenceServiceIriumId IN (:agenceIps)')
                                    ->setParameter('agenceIps', $agenceServiceIriumId)
                                    ->orderBy('p.Matricule', 'ASC');
                            },
                        ]
                    )
                    ->add(
                        'serviceDebiteur',
                        EntityType::class,
                        [
                            'label'         => 'Service Débiteur / Destination',
                            'class'         => Service::class,
                            'placeholder'   => '-- Choisir une service débiteur --',
                            'choice_label'  => function (Service $service): string {
                                return $service->getCodeService() . ' ' . $service->getLibelleService();
                            },
                            'attr'          => [
                                'class' => 'serviceDebiteur',
                            ],
                            'choices'       => $services,
                            'query_builder' => function (ServiceRepository $serviceRepository) {
                                return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                            }
                        ]
                    )
                ;
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $mutation = $event->getData(); // Objet
                $form = $event->getForm();

                $personnelId = $form->get('matriculeNomPrenom')->getData(); // id du personnel sélectionné

                /** 
                 * @var Personnel $personnel
                 */
                $personnel = $this->em->getRepository(Personnel::class)->find($personnelId);

                // On met à jour les données du formulaire
                $mutation->setMatricule($personnel->getMatricule());
                $mutation->setNom($personnel->getNom());
                $mutation->setPrenom($personnel->getPrenoms());
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mutation::class,
        ]);
    }
}
