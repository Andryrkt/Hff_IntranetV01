<?php

namespace App\Form\da;

use App\Entity\da\DemandeApproL;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DemandeApproLFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artFams1', TextType::class, [
                'label' => false
            ])
            ->add('artFams2', TextType::class, [
                'label' => false
            ])
            ->add('artDesi', TextType::class, [
                'label' => false
            ])
            ->add('dateFinSouhaite', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'la date ne doit pas être vide'])
                ]
            ])
            ->add('qteDem', TextType::class,  [
                'label' => false
            ])
            ->add('commentaire', TextType::class, [
                'label' => false
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
