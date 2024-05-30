<?php

namespace App\Form;

use App\Entity\Permission;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;


class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
       
        ->add('role_name', 
            TextType::class, 
            [
                'label' => 'Nom',
            ])
        ->add('permissions',
            EntityType::class,
            [
                'label' => 'permission',
                'class' => Permission::class,
                'choice_label' => 'permissionName',
                'multiple' => true,
                'expanded' => false
            ]
        )
    
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }


}