<?php

namespace App\Form;

use App\Entity\admin\Societte;
use App\Entity\admin\utilisateur\Profil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoixSocieteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('societe', EntityType::class, [
                'label'        => 'Choisissez une société',
                'placeholder'  => '-- Choix de la société --',
                'required'     => true,
                'class'        => Societte::class,
                'choices'      => $options['societes'],
                'choice_label' => 'nom',
            ])
            ->add('profil', EntityType::class, [
                'label'        => 'Choisissez un profil',
                'placeholder'  => '-- Choix du profil --',
                'required'     => true,
                'class'        => Profil::class,
                'choices'      => $options['profils'],
                'choice_label' => 'designation',
                'choice_attr'  => function (Profil $profil) {
                    return [
                        'data-societe' => $profil->getSociete()->getId(),
                    ];
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
