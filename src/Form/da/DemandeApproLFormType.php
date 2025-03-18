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
            ->add('artFams1', TextType::class)
            ->add('artFams2', TextType::class)
            ->add('artDesi', TextType::class)
            ->add('dateFinSouhaite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin souhaitée *',
                'constraints' => [
                    new NotBlank(['message' => 'la date ne doit pas être vide'])
                ]
            ])
            ->add('qteDem', TextType::class)
            ->add('commentaire', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeApproL::class,
        ]);
    }
}
