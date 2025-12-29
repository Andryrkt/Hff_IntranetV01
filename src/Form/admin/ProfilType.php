<?php

namespace App\Form\admin;

use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\Profil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'designation',
                TextType::class,
                [
                    'label' => 'Désignation du profil',
                ]
            )
            ->add(
                'reference',
                TextType::class,
                [
                    'label' => 'Référence du profil'
                ]
            )
            ->add('applications', EntityType::class, [
                'label'    => 'Applications autorisées',
                'class' => Application::class,
                'choice_label' => 'codeApp', // ou refVignette, ou un champ visible
                'multiple' => true,
                'expanded' => false, // true = checkboxes
                'mapped' => false, // on gère manuellement la synchronisation avec ApplicationProfil
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profil::class,
        ]);
    }
}
