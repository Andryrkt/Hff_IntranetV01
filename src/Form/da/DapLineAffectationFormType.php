<?php

namespace App\Form\da;

use App\Entity\da\DemandeApproParentLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DapLineAffectationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artRefp', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('artDesi', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'autocomplete',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('dateFinSouhaite', DateType::class, [
                'label' => false,
                'required' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'la date ne doit pas être vide'])
                ]
            ])
            ->add('qteDem', TextType::class,  [
                'label' => false,
                'required' => false,
            ])
            ->add('commentaire', TextType::class, [
                'label' => false,
                'required' => false,
                'empty_data' => ''
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
                'required' => false
            ])
            ->add('estFicheTechnique', CheckboxType::class, [
                'required' => false,
                'label'    => false,
            ])
            ->add('articleStocke', CheckboxType::class, [
                'required' => false,
                'label'    => false,
            ])
            ->add('prixUnitaire', TextType::class, [
                'label' => false,
                'required' => false,
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
