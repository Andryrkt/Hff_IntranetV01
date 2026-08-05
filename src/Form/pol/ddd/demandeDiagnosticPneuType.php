<?php

namespace App\Form\pol\ddd;

use App\Entity\ddd\Chantier;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Repository\ddd\ChantierRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;


class DemandeDiagnosticPneuType extends AbstractType
{
    private $agenceRepository;


    const LIVRAISON = [
        'MACHINE' => 'MACHINE',
        'PNEU' => 'PNEU'
    ];




    public function buildForm(FormBuilderInterface $builder, array $options)
    {



        $builder
            // Livraison
            ->add(
                'livraison',
                ChoiceType::class,
                [
                    'label' => 'Livraison',
                    'choices' => self::LIVRAISON,
                    'required' => false,
                    'placeholder' => 'Sélectionner',
                ]
            )
            ->add('chantier', EntityType::class, [
                'class' => Chantier::class,
                'choice_label' => 'nomChantier',
                'placeholder' => '-- Choisir--',
                'required' => true,
                'query_builder' => function (ChantierRepository $repository) {
                    return $repository->createQueryBuilder('c')
                        ->orderBy('c.nomChantier', 'ASC');
                },
            ])


            ->add(
                'nbPneuSurMachine',
                IntegerType::class,
                [
                    'label' => 'Nombre pneu sur machine',
                    'required' => false,
                ]
            )
            ->add(
                'nbPneuSecours',
                IntegerType::class,
                [
                    'label' => 'Nombre pneu secours',
                    'required' => false,
                ]
            )
            ->add(
                'nbPneuADiagnostiquer',
                IntegerType::class,
                [
                    'label' => 'Nombre pneu à diagnostiquer',
                    'required' => false,
                ]
            )
            ->add(
                'observation',
                TextareaType::class,
                [
                    'label' => 'Observation *',
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
                    'label' => " Id Matériel *",
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
                        'class' => 'noEntrer autocomplete',
                        'autocomplete' => 'off',
                    ]
                ]

            )

            // ->add(
            //     'pieceJointes',
            //     FileType::class,
            //     [
            //         'label' => 'Pièce Jointes (PDF, JPG, PNG)',
            //         'required' => false,
            //         'constraints' => [
            //             new File([
            //                 'maxSize' => '5M',
            //                 'mimeTypes' => [
            //                     'application/pdf',
            //                     'image/jpeg',
            //                     'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            //                 ],
            //                 'mimeTypesMessage' => 'Please upload a valid PDF file.',
            //             ])
            //         ],
            //     ]
            // )

        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeDiagnosticPneu::class,
        ]);
    }
}
