<?php

namespace App\Form\da\reappro;

use App\Form\common\DateRangeType;
use App\Form\common\AgenceServiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportingIpsSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('debiteur', AgenceServiceType::class, [
                'label' => false,
                'required' => false,
                'agence_label' => 'Agence Debiteur',
                'service_label' => 'Service Debiteur',
                'agence_placeholder' => '-- Agence Debiteur --',
                'service_placeholder' => '-- Service Debiteur --',
            ])
            ->add('dateCreation', DateRangeType::class, [
                'label' => false,
                'debut_label' => 'Date création (début)',
                'fin_label' => 'Date création (fin)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
