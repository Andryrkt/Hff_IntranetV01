<?php

namespace App\Form\da;

use App\Entity\da\DemandeApproLR;
use App\Model\da\DaModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DemandeApproLRFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artDesi', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('numeroDemandeAppro', TextType::class,  [
                'label' => false,
                'required' => false,
            ])
            ->add('numeroLigneDem', TextType::class,  [
                'label' => false,
                'required' => false,
            ])
            ->add('qteDem', TextType::class,  [
                'label' => false,
                'required' => false,
            ])
            ->add('artConstp', TextType::class, [
                'label' => false,
                'required' => false,
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
            'data_class' => DemandeApproLR::class,
        ]);
    }
}
