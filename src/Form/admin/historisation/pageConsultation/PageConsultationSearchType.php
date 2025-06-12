<?php

namespace App\Form\admin\historisation\pageConsultation;

use App\Entity\admin\historisation\pageConsultation\PageConsultationSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageConsultationSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('utilisateur', TextType::class, [
                'label' => 'Utilisateur',
                'required' => false,
            ])
            ->add('nom_page', TextType::class, [
                'label' => 'Nom de page',
                'required' => false,
            ])
            ->add('machineUser', TextType::class, [
                'label' => 'Machine',
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Consultation Début',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Consultation Fin',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PageConsultationSearch::class,
        ]);
    }
}
