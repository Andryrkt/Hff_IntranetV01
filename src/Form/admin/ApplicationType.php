<?php

namespace App\Form\admin;

use App\Entity\admin\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
       
        ->add('nom', 
            TextType::class, 
            [
                'label' => 'nom',
            ])
        ->add('codeApp',
            TextType::class,
            [
                'label' => 'Code Application'
            ]
        )
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }


}