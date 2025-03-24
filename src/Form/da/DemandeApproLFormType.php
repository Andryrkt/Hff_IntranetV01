<?php

namespace App\Form\da;

use App\Entity\da\DemandeApproL;
use App\Model\da\DaModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DemandeApproLFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $daModel = new DaModel;

        $builder
            ->add('fams1', ChoiceType::class, [
                'mapped' => false,
                'label' => false,
                'required' => false,
                'placeholder' => '-- Choisir une famille --',
                'choices' => $daModel->getAllFamille(),
            ])
            ->add('fams2', ChoiceType::class, [
                'mapped' => false,
                'label' => false,
                'required' => false,
                'placeholder' => '-- Choisir une sous-famille --',
                'choices' => $daModel->getAllSousFamille()
            ])
            ->add('artDesi', TextType::class, [
                'label' => false,
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
                'required' => false
            ])
            ->add('artConstp', TextType::class, [
                'label' => false,
                'required' => false,
                'data' => 'ZST',
            ])
            ->add('artRefp', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('artFams1', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('artFams2', TextType::class, [
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
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeApproL::class,
        ]);
    }
}
