<?php

namespace App\Form\da;

use App\Entity\da\DaHistoriqueDemandeModifDA;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoriqueModifDaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idDa', TextType::class, [
                'mapped' => false,
                'label' => false,
                'attr' => [
                    'class' => 'd-none',
                ],
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif du déverrouillage',
                'required' => true,
                'attr' => [
                    'maxlength' => 255,
                    'rows' => 4,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DaHistoriqueDemandeModifDA::class,
        ]);
    }
}
