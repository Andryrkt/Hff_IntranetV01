<?php

namespace App\Form\magasin\bc;

use App\Model\magasin\bc\BcMagasinLigneDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BcMagasinLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroLigne', HiddenType::class)
            ->add('ras', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'action-checkbox ras-checkbox',
                ],
            ])
            ->add('qteModifier', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'action-checkbox qty-checkbox',
                ],
            ])
            ->add('supprimer', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'action-checkbox delete-checkbox',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BcMagasinLigneDto::class,
        ]);
    }
}
