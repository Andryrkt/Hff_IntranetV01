<?php

namespace App\Form\da;


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
                    'BC' => true,
                    'Facture + BL' => false,
                ],
                'expanded' => true, // pour afficher des boutons radio
                'multiple' => false, // un seul choix possible
                'required' => true,
                'label' => 'Document à soumettre',
                'data' => true
            ])
            ->add('commande_id', HiddenType::class)
            ->add('da_id', HiddenType::class)
            ->add('num_or', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
