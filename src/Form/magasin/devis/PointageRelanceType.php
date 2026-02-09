<?php

namespace App\Form\magasin\devis;

use App\Dto\Magasin\Devis\PointageRelanceDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PointageRelanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return $builder
            ->add('numeroDevis', null, [
                'label' => 'Numéro de devis',
                'attr' => [
                    'readonly' => true,
                ]
            ])
            ->add('dateDeRelance', DateType::class, [
                'label' => 'Date de relance *',
                'widget' => 'single_text',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PointageRelanceDto::class,
            'csrf_protection' => false,
        ]);
    }
}
