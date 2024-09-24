<?php

namespace App\Form\admin\utilisateur;

use App\Entity\admin\AgenceServiceIrium;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AgenceServiceIriumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('idIrium', 
        TextType::class, 
        [
            'label' => "Id Irium",
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 4]),
            ],
        ])
    
        ->add('agencei100', 
            TextType::class,
            [
                'label' => 'Agence i100'
            ])

        ->add('nomagencei100', 
        TextType::class,
        [
            'label' => 'Nom Agence i100'
        ])    
        
        ->add('servicei100', 
        TextType::class,
        [
            'label' => 'Service i100'
        ])

        ->add('nomservicei100', 
        TextType::class,
        [
            'label' => 'Nom Service i100'
        ]) 

        ->add('agenceips', 
        TextType::class,
        [
            'label' => 'Agence ips'
        ])

        ->add('serviceips', 
        TextType::class,
        [
            'label' => 'Service ips'
        ])

        ->add('libelleserviceips', 
        TextType::class,
        [
            'label' => 'libelle service ips'
        ])

        ->add('societeios', 
        TextType::class,
        [
            'label' => 'societe ios'
        ])

        ->add('servicesagepaie', 
        TextType::class,
        [
            'label' => 'service sage paie'
        ])
        ;

    
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AgenceServiceIrium::class,
        ]);
    }
}