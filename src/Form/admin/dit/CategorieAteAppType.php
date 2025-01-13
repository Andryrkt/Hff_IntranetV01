<?php

namespace App\Form\admin\dit;


use App\Entity\admin\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\admin\dit\CategorieAteApp;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;


class CategorieAteAppType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
       
        ->add('libelleCategorieAteApp', 
            TextType::class, 
            [
                'label' => 'libelle Categorie Ate App',
            ])
        ->add('applications',
            EntityType::class,
            [
                'label' => 'Applications',
                'class' => Application::class,
                'choice_label' => 'codeApp',
                'multiple' => true,
                'expanded' => true
            ]
        )
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CategorieAteApp::class,
        ]);
    }


}