<?php

namespace App\Form\admin;

use App\Entity\admin\Application;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'nom',
                TextType::class,
                [
                    'label' => 'Nom de l\'application (255 caractères max)',
                ]
            )
            ->add(
                'codeApp',
                TextType::class,
                [
                    'label' => 'Code de l\'application (10 caractères max)',
                ]
            )
            ->add('pages', EntityType::class, [
                'label'    => 'Pages associées',
                'class'    => PageHff::class,
                'choice_label' => 'nom', // ou un autre champ de PageHff que tu veux afficher
                'multiple' => true,       // permet de choisir plusieurs pages
                'expanded' => false,      // false = select multiple, true = checkboxes
                'by_reference' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
