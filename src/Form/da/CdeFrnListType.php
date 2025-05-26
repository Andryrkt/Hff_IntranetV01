<?php

namespace App\Form\da;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CdeFrnListType extends  AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numDa', TextType::class, [
                'label' => 'n° DA',
                'required' => false
            ])
            ->add('achatDirect', ChoiceType::class, [
                'label' => 'Achat direct',
                'placeholder' => '-- Choisir le choix --',
                'choices' => ['NON' => 0, 'OUI' => 1],
                'required' => false
            ])
            ->add('numDit', TextType::class, [
                'label' => 'n° DIT',
                'required' => false
            ])
            ->add('numOr', TextType::class, [
                'label' => 'n° OR',
                'required' => false
            ])
            ->add('numFrn', TextType::class, [
                'label' => 'n° Fournisseur',
                'required' => false
            ])
            ->add('frn', TextType::class, [
                'label' => 'Fournisseur',
                'required' => false
            ])
            ->add('numCde', TextType::class, [
                'label' => 'n° Commande',
                'required' => false
            ])
            ->add('statut', TextType::class, [
                'label' => 'Statut',
                'required' => false
            ])
            ->add('ref', TextType::class, [
                'label' => 'Réference',
                'required' => false
            ])
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
