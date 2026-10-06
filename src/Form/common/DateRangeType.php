<?php

namespace App\Form\common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('debut', DateType::class, [
                'widget' => 'single_text',
                'label' => $options['debut_label'],
                'required' => false,
                'data' => $options['data_date_debut'],
            ])
            ->add('fin', DateType::class, [
                'widget' => 'single_text',
                'label' => $options['fin_label'],
                'required' => false,
                'data' => $options['data_date_fin'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'debut_label' => 'Date (début)',
            'fin_label' => 'Date (fin)',
            'data_date_debut' => null,
            'data_date_fin' => null,
        ]);
    }
}
