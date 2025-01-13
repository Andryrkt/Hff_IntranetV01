<?php

namespace App\Form\dit;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class  DocDansDwType extends AbstractType
{
    const DOC_DANS_DW = [
        'OR' => 'OR',
        'RI' => 'RI',
        'FACTURE' => 'FACTURE',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('docDansDW', 
            ChoiceType::class,
            [
                'label' => 'Docs à intégrer dans DW',
                'choices' => self::DOC_DANS_DW,
                'placeholder' => '--'
            ])
            ->add('numeroDit',
            HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
