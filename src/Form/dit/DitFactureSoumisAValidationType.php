<?php

namespace App\Form\dit;


use Symfony\Component\Form\AbstractType;
use App\Entity\dit\DitFactureSoumisAValidation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class DitFactureSoumisAValidationType extends AbstractType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        // ->add('numeroFact',
        //     IntegerType::class,
        //     [
        //         'label' => 'Numéro Fact *',
        //         'required' => true,
        //         'constraints' => [
        //             new Assert\Length([
        //                 'max' => 8,
        //                 'maxMessage' => 'Le numéro OR ne doit pas dépasser {{ limit }} caractères.',
        //             ]),
        //         ],
        //         'attr' => [
        //             'min' => 0,
        //             'pattern' => '\d*', // Permet uniquement l'entrée de chiffres
        //             'readonly' => true
        //         ],
        //     ])
            ->add('numeroDit',
            TextType::class,
            [
                'label' => 'Numéro DIT',
                'data' => $options['data']->getNumeroDit(),
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('numeroOR',
            IntegerType::class,
            [
                'label' => 'Numéro OR *',
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'max' => 8,
                        'maxMessage' => 'Le numéro OR ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'attr' => [
                    'min' => 0,
                    'pattern' => '\d*', // Permet uniquement l'entrée de chiffres
                    'readonly' => true
                ],
                'data' => $options['data']->getNumeroOR()
            ])
            ->add('pieceJoint01', 
            FileType::class, 
            [
                'label' => 'Upload File',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuiller sélectionner la facture à soumettre .', // Message d'erreur si le champ est vide
                    ]),
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF file.',
                    ])
                ],
            ])
            ->add('pieceJoint02', 
            FileType::class, 
            [
                'label' => 'Upload File',
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
            ])
            ->add('pieceJoint03', 
            FileType::class, 
            [
                'label' => 'Upload File',
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
            ])
            ->add('pieceJoint04', 
            FileType::class, 
            [
                'label' => 'Upload File',
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
            ])
       ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DitFactureSoumisAValidation::class,
        ]);
    }


}