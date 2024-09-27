<?php

namespace App\Form\dit;


use App\Entity\dit\DitInsertionOr;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class DitOrsSoumisAValidationType extends AbstractType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                'label' => 'Numéro OR',
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
                ],
            ])
            ->add('pieceJoint01', 
            FileType::class, 
            [
                'label' => 'Upload File',
                'required' => true,
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
            'data_class' => DitOrsSoumisAValidation::class,
        ]);
    }


}