<?php

namespace App\Form\pol\ddd;

use App\Entity\ddd\Chantier;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Repository\ddd\ChantierRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;



class DemandeDiagnosticPneuType extends AbstractType
{
  
    const LIVRAISON = [
        'MACHINE' => 'MACHINE',
        'PNEU' => 'PNEU'
    ];

    public const MOTIFS = [
        'Usure normale' => 'Usure normale',
        'Contrôle préventif' => 'Contrôle préventif',
        'Crevaison / perte de pression' => 'Crevaison / perte de pression',
        'Dommage mécanique' => 'Dommage mécanique',
        'Autre' => 'Autre',
    ];


    public function buildForm(FormBuilderInterface $builder, array $options)
    {



        $builder
            // Livraison
            ->add(
                'livraison',
                ChoiceType::class,
                [
                    'label' => 'Livraison *',
                    'choices' => self::LIVRAISON,
                    'required' => true,
                    'placeholder' => '-- Choisir --',
                ]
            )
            ->add('chantier', EntityType::class, [
                'class' => Chantier::class,
                'choice_label' => 'nomChantier',
                'label' => 'Chantier *',
                'placeholder' => '-- Choisir --',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'la date ne doit pas être vide'])
                ],
                'query_builder' => function (ChantierRepository $repository) {
                    return $repository->createQueryBuilder('c')
                        ->orderBy('c.nomChantier', 'ASC');
                },
            ])
            ->add('dateDepartChantier', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date départ chantier *',
                'required' => true,
                'attr' => ['class' => 'noEntrer'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'la date ne doit pas être vide'])
                ]
            ])


            ->add(
                'nbPneuSurMachine',
                ChoiceType::class,
                [
                    'label' => 'Nombre pneu sur machine *',
                    'choices' => array_combine(range(1, 12), range(1, 12)),
                    'placeholder' => 'Sélectionner',
                    'required' => false,
                ]
            )

            ->add(
                'nbPneuSecours',
                ChoiceType::class,
                [
                    'label' => 'Nombre pneu secours *',
                    'choices' => array_combine(range(0, 12), range(0, 12)),
                    'placeholder' => 'Sélectionner',
                    'required' => false,
                ]
            )

            ->add(
                'nbPneuADiagnostiquer',
                ChoiceType::class,
                [
                    'label' => 'Nombre pneu à diagnostiquer *',
                    'choices' => array_combine(range(1, 10), range(1, 10)),
                    'placeholder' => 'Sélectionner',
                    'required' => false,
                ]
            )

            ->add('motifs', ChoiceType::class, [
                'choices' => self::MOTIFS,
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'Veuillez sélectionner au moins un motif.',
                    ]),
                ],
            ])
            ->add(
                'observation',
                TextareaType::class,
                [
                    'label' => 'Observation',
                    'required' => false,
                    'attr' => [
                        'rows' => 5,
                        'class' => 'observation'
                    ],

                ]
            )

            ->add(
                'id_materiel',
                TextType::class,
                [
                    'label' => "Id Matériel *",
                    'required' => true,
                    'attr' => [
                        'class' => 'noEntrer autocomplete',
                        'autocomplete' => 'off',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'l\id materiel ne peut pas être vide.', // Message d'erreur si le champ est vide
                        ]),
                    ],
                ]
            )
            ->add(
                'numero_parc_materiel',
                TextType::class,
                [
                    'label' => " N° Parc",
                    'required' => false,
                    'attr' => [
                        'autocomplete' => 'off',
                        'readonly' => true,
                    ]
                ]

            )
            ->add('marqueMateriel', TextType::class, [
                'label' => 'Marque',
                'required' => false,
                'attr' => [
                    'readonly' => true,
                ],
            ])
            ->add('typeMateriel', TextType::class, [
                'label' => 'Type',
                'required' => false,
                'attr' => [
                    'readonly' => true,
                ],
            ])
            ->add('designationMateriel', TextType::class, [
                'label' => 'Désignation',
                'required' => false,
                'attr' => [
                    'readonly' => true,
                ],
            ])


            ->add(
                'piecesJointes',
                FileType::class,
                [
                    'label' => 'Pièces Jointes',
                    'required' => false,
                    'multiple' => true,
                    // 'data_class' => null,
                    'mapped' => false,
                    'constraints' => [
                        new Callback([$this, 'validateFiles']),
                    ],
                ]
            )
            ->add('diagnosticPneus', CollectionType::class, [
                'entry_type' => DiagnosticPneuType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Diagnostic des pneus',
                'prototype' => true,   // enables JavaScript dynamic adding
            ]);
    }

    public function validateFiles($files, ExecutionContextInterface $context)
    {
        $maxSize = '5M';
        $mimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        if ($files) {
            foreach ($files as $file) {
                $fileConstraint = new File([
                    'maxSize' => $maxSize,
                    'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser 5 Mo.',
                    'mimeTypes' => $mimeTypes,
                    'mimeTypesMessage' => 'Veuillez télécharger un fichier valide.',
                ]);

                $violations = $context->getValidator()->validate($file, $fileConstraint);

                if (count($violations) > 0) {
                    foreach ($violations as $violation) {
                        $context->buildViolation($violation->getMessage())
                            ->addViolation();
                    }
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeDiagnosticPneu::class,
        ]);
    }
}
