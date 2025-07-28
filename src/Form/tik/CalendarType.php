<?php

namespace App\Form\tik;

use App\Entity\tik\TkiPlanning;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objetDemande', TextType::class, [
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('detailDemande', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('dateDebutPlanning', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('dateFinPlanning', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TkiPlanning::class,
        ]);
    }
}
