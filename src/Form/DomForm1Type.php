<?php

namespace App\Form;

use App\Entity\Idemnity;
use App\Entity\Personnel;
use App\Entity\SousTypeDocument;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DomForm1Type extends AbstractType
{
    const SALARIE = [
        'PERMANENT' => 'PERMANENT',
        'TEMPORAIRE' => 'TEMPORAIRE',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('sousTypeDocument',
        EntityType::class,
        [
            'label' => 'Type de Mission',
            'class' => SousTypeDocument::class,
            'choice_label' => 'description'
        ])
        ->add('salarie',
        ChoiceType::class,
        [
            'mapped' => false,
            'label' => 'Salarié',
            'choices' => self::SALARIE,
            'data' => 'PERMANENT'
        ])
        ->add('categorie',
        EntityType::class,
        [
            'label' => 'Catégorie',
            'class' => Idemnity::class,
            'choice_label' => 'catg'
        ])
        ->add('matriculeNom',
        EntityType::class,
        [
            'mapped' => false,
            'label' => 'Matricule et nom',
            'class' => Personnel::class,
            'choice_label' => function(Personnel $personnel): string {
                return $personnel->getMatricule() . ' ' . $personnel->getNom() . ' ' . $personnel->getPrenoms();
            }
        ])
        ->add('nom',
        TextType::class,
        [
            'label' => 'Nom'
        ])
        ->add('prenom',
        TextType::class,
        [
            'label' => 'Prénoms'
        ])
        ->add('cin',
        NumberType::class,
        [
            'label' => 'CIN'
        ])
        ;
    }
}
