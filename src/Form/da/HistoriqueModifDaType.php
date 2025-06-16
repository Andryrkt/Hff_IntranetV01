<?php

namespace App\Form\da;

use App\Entity\da\DaHistoriqueDemandeModifDA;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoriqueModifDaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('motif', TextType::class, [
                'label' => 'Motif du déverrouillage',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Entrez le motif',
                    'maxlength' => 255,
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
