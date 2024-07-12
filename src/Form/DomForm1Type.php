<?php

namespace App\Form;

use App\Entity\Catg;
use App\Entity\Dom;

use App\Entity\Idemnity;

use App\Entity\Personnel;

use App\Entity\SousTypeDocument;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class DomForm1Type extends AbstractType
{

    const SALARIE = [
        'PERMANENT' => 'PERMANENT',
        'TEMPORAIRE' => 'TEMPORAIRE',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        $builder
        ->add('agenceEmetteur', 
        TextType::class,
        [
           'mapped' => false,
                    'label' => 'Agence',
                    'required' => false,
                    'attr' => [
                        'readonly' => true
                    ],
            'data' => $options["data"]->getAgenceEmetteur() ?? null
        ])
       
        ->add('serviceEmetteur', 
        TextType::class,
        [
            'mapped' => false,
            'label' => 'Service',
            'required' => false,
            'attr' => [
              'readonly' => true,
            ],
            'data' => $options["data"]->getServiceEmetteur() ?? null
        ])
        ->add('sousTypeDocument',
        EntityType::class,
        [
            'label' => 'Type de Mission',
            'class' => SousTypeDocument::class,
            'choice_label' => 'codeSousType'
        ])
        ->add('salarie',
        ChoiceType::class,
        [
            'mapped' => false,
            'label' => 'Salarié',
            'choices' => self::SALARIE,
            'data' => 'PERMANENT'
        ])
        ->add('categorie',
        EntityType::class,
        [
            'label' => 'Catégorie',
            'class' => Catg::class,
            'choice_label' => 'description'
        ])
        ->add('matriculeNom',
        EntityType::class,
        [
            'mapped' => false,
            'label' => 'Matricule et nom',
            'class' => Personnel::class,
            'choice_label' => function(Personnel $personnel): string {
                return $personnel->getMatricule() . ' ' . $personnel->getNom() . ' ' . $personnel->getPrenoms();
            }
        ])
        ->add('matricule',
        TextType::class,
        [
            'label' => 'Matricule',
            'attr' => [
                'readonly' => true
            ]
        ]
        )
        ->add('nom',
        TextType::class,
        [
            'label' => 'Nom'
        ])
        ->add('prenom',
        TextType::class,
        [
            'label' => 'Prénoms'
        ])
        ->add('cin',
        NumberType::class,
        [
            'label' => 'CIN'
        ])
        ;
        
        
    
    }



    
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => Dom::class,
            ]);
        }


}