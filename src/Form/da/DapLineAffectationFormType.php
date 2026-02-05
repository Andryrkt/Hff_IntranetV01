<?php

namespace App\Form\da;

use Symfony\Component\Form\AbstractType;
use App\Entity\da\DemandeApproParentLine;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class DapLineAffectationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artRefp', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'da-art-refp',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('artDesi', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'da-art-desi',
                ],
                'required' => false,
            ])
            ->add('artConstp', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('numeroFournisseur', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('nomFournisseur', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'da-nom-frn',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('articleStocke', CheckboxType::class, [
                'required' => false,
                'label'    => false,
            ])
            ->add('prixUnitaire', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('qteDem', TextType::class,  [
                'label' => false,
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeApproParentLine::class,
        ]);
    }
}
