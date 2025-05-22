<?php

namespace App\Form\dw;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class DocInterneSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('typeDocument', TextType::class, [
                'label'    => 'Type de document',
                'required' => false
            ])
            ->add('perimetre', TextType::class, [
                'label'    => 'Périmètre',
                'required' => false
            ])
            ->add('nomDocument', TextType::class, [
                'label'    => 'Nom du document',
                'required' => false
            ])
            ->add('processusLie', TextType::class, [
                'label'    => 'Processus lié',
                'required' => false
            ])
            ->add('nomResponsable', TextType::class, [
                'label'    => 'Responsable Processus',
                'required' => false
            ])
            ->add('dateDocument', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de document',
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
