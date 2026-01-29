<?php

namespace App\Form\da\daCdeFrn;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class DaSoumissionType extends  AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('soumission', ChoiceType::class, [
                'choices'  => [
                    'Soumission BC à validation' => 'BC',
                    'Soumission Facture + BL (générer un BAP)' => 'Facture + BL',
                    'Soumettre BL Reappro' => 'BL Reappro'
                ],
                'expanded' => true, // pour afficher des boutons radio
                'multiple' => false, // un seul choix possible
                'required' => true,
                'label' => 'Document à soumettre',
                'data' => 'BC'
            ])
            ->add('commande_id', HiddenType::class)
            ->add('da_id', HiddenType::class)
            ->add('num_or', HiddenType::class)
            ->add('type_da', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
