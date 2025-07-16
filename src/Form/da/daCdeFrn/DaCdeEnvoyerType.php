<?php

namespace App\Form\da\daCdeFrn;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class DaCdeEnvoyerType extends  AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateLivraisonPrevue', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date livraison prévue',
                'required' => true,
                'data' => $options['data']['dateDefault'] ?? null,
            ])
            ->add('estEnvoyer', CheckboxType::class, [
                'label' => 'BC envoyé au fournisseur',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
