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
                    'Soumission BC' => true,
                    'Soumission facture + BL' => false,
                ],
                'expanded' => true, // pour afficher des boutons radio
                'multiple' => false, // un seul choix possible
                'required' => true,
                'label' => 'dossier à soumettre',
                'data' => true
            ])
            ->add('commande_id', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
