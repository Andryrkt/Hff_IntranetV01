<?php

namespace App\Form;

use App\Entity\Personnel;
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
            'label' => "Numero Matricule",
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 4]),
            ],
        ])
        ->add('Nom', 
            TextType::class, 
            [
                'label' => 'Nom',
            ])
        ->add('Code_AgenceService_Sage', 
            TextType::class, 
            [
                'label' => 'Code Ag\Serv Sage'
            ])
        ->add('Numero_Fourniseur_IRIUM', 
            NumberType::class,
            [
                'label' => 'Numero Fournisseur IRIUM'
            ])
        ->add('Code_AgenceService_IRIUM', 
            NumberType::class, 
            [
                'label' => 'Code Ag\Serv IRIUM'
            ])
        ->add('Numero_Telephone',
            TelType::class,
            [
                'label' => 'Numero téléphone'
            ]
        )
        ->add('Numero_Compte_Bancaire',
                NumberType::class,
                [
                    'label' => 'N° Compte Bancaire'
                ]
        )
        ->add('Libelle_AgenceService_Sage',
        TextType::class,
        [
            'label' => ' Libelle Ag\Serv Sage'
        ]
        )
        ->add('Code_Service_Agence_IRIUM',
        TextType::class,
        [
            'label' => 'code Serv\Ag IRIUM'
        ]
        )
        ->add('Libelle_Service_Agence_IRIUM',
        TextType::class,
        [
            'label' =>'Libelle Serv\Ag IRIUM'
        ]
        )
        ->add('Prenoms',
        TextType::class,
        [
            'label' => 'Prénoms'
        ]
        )
        ->add('Qualification',
        TextType::class,
        [
            'label' => 'Qualification'
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