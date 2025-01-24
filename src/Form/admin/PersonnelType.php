<?php

namespace App\Form\admin;

use App\Entity\admin\Personnel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class PersonnelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('Matricule', 
        NumberType::class, 
        [
            'label' => "Numero Matricule *",
            'attr' => [
                'pla'
            ],
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 4]),
            ],
        ])
        ->add('Nom', 
            TextType::class, 
            [
                'label' => 'Nom *',
            ])
        ->add('CodeAgenceServiceSage', 
            TextType::class, 
            [
                'label' => 'Code Ag\Serv Sage *'
            ])
        ->add('NumeroFournisseurIRIUM', 
            NumberType::class,
            [
                'label' => 'Numero Fournisseur IRIUM *'
            ])
        ->add('CodeAgenceServiceIRIUM', 
            NumberType::class, 
            [
                'label' => 'Code Ag\Serv IRIUM *'
            ])
        ->add('NumeroTelephone',
            TelType::class,
            [
                'label' => 'Numero téléphone',
                'required' => false
            ]
        )
        ->add('NumeroCompteBancaire',
                TextType::class,
                [
                    'label' => 'N° Compte Bancaire',
                    'required' => false
                ]
        )
        ->add('LibelleAgenceServiceSage',
        TextType::class,
        [
            'label' => ' Libelle Ag\Serv Sage',
            'required' => false
        ]
        )
        ->add('CodeServiceAgenceIRIUM',
        TextType::class,
        [
            'label' => 'code Serv\Ag IRIUM',
            'required' => false
        ]
        )
        ->add('LibelleServiceAgenceIRIUM',
        TextType::class,
        [
            'label' =>'Libelle Serv\Ag IRIUM',
            'required' => false
        ]
        )
        ->add('Prenoms',
        TextType::class,
        [
            'label' => 'Prénoms *'
        ]
        )
        ->add('Qualification',
        TextType::class,
        [
            'label' => 'Qualification',
            'required' => false
        ])
    
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Personnel::class,
        ]);
    }


}