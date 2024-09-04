<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\Permission;
use App\Entity\admin\Societte;
use App\Entity\TypeReparation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
        
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Societte::class,
        ]);
    }


}