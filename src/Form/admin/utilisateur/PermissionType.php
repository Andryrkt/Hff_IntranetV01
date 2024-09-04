<?php

namespace App\Form\admin\utilisateur;

use Symfony\Component\Form\AbstractType;
use App\Entity\admin\utilisateur\Permission;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class PermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
       
        ->add('permissionName', 
            TextType::class, 
            [
                'label' => 'Nom de permission',
            ])
    
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Permission::class,
        ]);
    }


}