<?php

namespace App\Form\pol\ddd;

use App\Entity\admin\Agence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class demandeDiagnosticPneuType extends AbstractType
{
    private $agenceRepository;


    const LIVRAISON = [
        'MACHINE' => 'MACHINE',
        'PNEU' => 'PNEU'
    ];


    public function __construct(EntityManagerInterface $em)
    {
        $this->agenceRepository = $em->getRepository(Agence::class);
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $codeSociete = $options['data']->getCodeSociete();

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
            // Chantier
            ->add(
                'codeChantier',
                TextType::class,
                [
                    'label' => 'Code chantier',
                    'required' => false,
                ]
            )

            ->add(
                'nomChantier',
                TextType::class,
                [
                    'label' => 'Nom chantier',
                    'required' => false,
                ]
            )
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
                'idMateriel',
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
                'numParc',
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
            ->add(
                'numSerie',
                TextType::class,
                [
                    'label' => " N° Serie",
                    'required' => false,
                    'attr' => [
                        'class' => 'noEntrer autocomplete',
                        'autocomplete' => 'off',
                    ]
                ]
            )
            ->add(
                'pieceJointes',
                FileType::class,
                [
                    'label' => 'Pièce Jointes (PDF, JPG, PNG)',
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
                            'mimeTypesMessage' => 'Please upload a valid PDF file.',
                        ])
                    ],
                ]
            )

        ;
    }
}
