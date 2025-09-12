<?php

namespace App\Form\magasin\devis;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DevisMagasinEnvoyerAuClientType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('numeroDevis', null, [
            'label' => 'Numéro de devis',
            'attr' => [
                'readonly' => true,
            ]
        ])
            ->add('dateEnvoiDevisAuClient', DateType::class, [
                'label' => 'Date envoi devis au client *',
                'widget' => 'single_text',
                'required' => true,
            ])
        ;
    }
}
