<?php

namespace App\Form\magasin\bc;

use Symfony\Component\Form\AbstractType;
use App\Model\magasin\bc\BcMagasinLigneDto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

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
            ])
            ->add('nouvelleQte', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'nouvelle-qte-input',
                    'style' => 'display: none; width: 37px;',
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
