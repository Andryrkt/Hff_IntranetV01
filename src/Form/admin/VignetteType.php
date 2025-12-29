<?php

namespace App\Form\admin;

use App\Entity\admin\Application;
use App\Entity\admin\Vignette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class VignetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'nom',
                TextType::class,
                [
                    'label' => 'Nom de la vignette',
                ]
            )
            ->add(
                'reference',
                TextType::class,
                [
                    'label' => 'Reference'
                ]
            )
            ->add('applications', EntityType::class, [
                'label'    => 'Applications associées',
                'class'    => Application::class,
                'choice_label' => 'codeApp',
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vignette::class,
        ]);
    }
}
