<?php

namespace App\Form\dw;

use App\Model\dw\docInterneModel;
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
        $model = new docInterneModel;

        $builder
            ->add('typeDocument', ChoiceType::class, [
                'placeholder' => '-- Choisir un type de document --',
                'label'    => 'Type de document',
                'choices'  => $model->getDistinctColumn('type_document'),
                'required' => false
            ])
            ->add('perimetre', ChoiceType::class, [
                'placeholder' => '-- Choisir un périmètre --',
                'label'    => 'Périmètre',
                'choices'  => $model->getDistinctColumn('perimetre'),
                'required' => false
            ])
            ->add('nomDocument', TextType::class, [
                'label'    => 'Nom du document',
                'required' => false
            ])
            ->add('processusLie', ChoiceType::class, [
                'placeholder' => '-- Choisir un processus --',
                'label'    => 'Processus lié',
                'choices'  => $model->getDistinctColumn('processus_lie'),
                'required' => false
            ])
            ->add('nomResponsable', TextType::class, [
                'label'    => 'Responsable Processus',
                'required' => false
            ])
            ->add('motCle', TextType::class, [
                'label'    => 'Rechercher par champ',
                'required' => false
            ])
            ->add('dateDocumentDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Début date de document',
                'required' => false,
            ])
            ->add('dateDocumentFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fin date de document',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
