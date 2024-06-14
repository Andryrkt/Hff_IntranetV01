<?php

namespace App\Form;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\Societte;
use App\Entity\TypeReparation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;


class SocietteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
       
        ->add('nom', 
            TextType::class, 
            [
                'label' => 'Nom',
            ])
        ->add('codeSociete', 
            TextType::class, 
            [
                'label' => 'Code Societte',
            ])
        ->add('typeReparations',
            EntityType::class,
            [
                'label' => 'Type de Réparation',
                'class' => TypeReparation::class,
                'choice_label' => 'type',
                'multiple' => true,
                'expanded' => false
            ]
        )
    
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Societte::class,
        ]);
    }


}