<?php

namespace App\Form\dit;


use App\Entity\dit\DitInsertionOr;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class DitOrsSoumisAValidationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'numeroDit',
                TextType::class,
                [
                    'label' => 'Numéro DIT',
                    'data' => $options['data']->getNumeroDit(),
                    'attr' => [
                        'disabled' => true
                    ]
                ]
            )
            ->add(
                'numeroOR',
                IntegerType::class,
                [
                    'label' => 'Numéro OR *',
                    'required' => false,
                    'constraints' => [
                        new Assert\Length([
                            'max' => 8,
                            'maxMessage' => 'Le numéro OR ne doit pas dépasser {{ limit }} caractères.',
                        ]),
                    ],
                    'attr' => [
                        'min' => 0,
                        'pattern' => '\d*', // Permet uniquement l'entrée de chiffres
                        'disabled' => true
                    ],
                    'data' => $options['data']->getNumeroOR()
                ]
            )
            ->add(
                'observation',
                TextareaType::class,
                [
                    'label' => 'Observation',
                    'required' => false,
                    'attr' => [
                        'rows' => 5,
                    ],
                ]
            )
            ->add(
                'pieceJoint01',
                FileType::class,
                [
                    'label' => 'Upload File',
                    'required' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuiller sélectionner l\'OR.', // Message d'erreur si le champ est vide
                        ]),
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'Le fichier ne doit pas dépasser 5 Mo.', // Message personnalisé pour la taille
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide.',
                        ])
                    ],
                ]
            )
            ->add(
                'pieceJoint02',
                FileType::class,
                [
                    'label' => 'Upload File',
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'Le fichier ne doit pas dépasser 5 Mo.',
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide.',
                        ])
                    ],
                ]
            )
            ->add(
                'pieceJoint03',
                FileType::class,
                [
                    'label' => 'Upload File',
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'Le fichier ne doit pas dépasser 5 Mo.',
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide.',
                        ])
                    ],
                ]
            )
            ->add(
                'pieceJoint04',
                FileType::class,
                [
                    'label' => 'Upload File',
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'Le fichier ne doit pas dépasser 5 Mo.',
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide.',
                        ])
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DitOrsSoumisAValidation::class,
        ]);
    }
}
