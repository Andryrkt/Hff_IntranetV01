<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoixSocieteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Préparer les tableaux id => label pour ChoiceType
        $societeChoices = [];
        foreach ($options['societes'] as $societe) {
            $societeChoices[$societe->getNom()] = $societe->getId();
        }

        $profilChoices = [];
        $profilAttr    = [];
        foreach ($options['profils'] as $profil) {
            $profilChoices[$profil->getDesignation()] = $profil->getId();
            $profilAttr[$profil->getId()] = [
                'data-societe' => $profil->getSociete()->getId(),
            ];
        }

        $builder
            ->add('societe', ChoiceType::class, [
                'label'       => 'Choisissez une société',
                'placeholder' => '-- Choix de la société --',
                'required'    => true,
                'choices'     => $societeChoices,
            ])
            ->add('profil', ChoiceType::class, [
                'label'       => 'Choisissez un profil',
                'placeholder' => '-- Choix du profil --',
                'required'    => true,
                'choices'     => $profilChoices,
                'choice_attr' => function ($val) use ($profilAttr) {
                    return $profilAttr[$val] ?? [];
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'societes' => [],
            'profils'  => [],
        ]);
    }
}
