<?php

// src/Form/SearchType.php
namespace App\Form;


use App\Entity\WorNiveauUrgence;
use App\Model\magasin\MagasinModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class MagasinSearchType extends AbstractType
{

    private function recupConstructeur()
    {
        $magasinModel = new MagasinModel();
       return  $magasinModel->recuperationConstructeur();
    }

    const OR_COMPLET_OU_NON = [
        'TOUTS LES OR' => 'TOUTS LES OR',
        'ORs COMPLET' => 'ORs COMPLET',
        'ORs PARTIELLEMNT COMPLETS' => 'ORs PARTIELLEMNT COMPLETS'
    ];

    const PIECE_MAGASIN_ACHATS_LOCAUX = [
        'TOUTS PIECES' => 'TOUTS PIECES',
        'PIECES MAGASIN' => 'PIECES MAGASIN',
        'LUB ET ACHATS LOCAUX' => 'LUB ET ACHATS LOCAUX'
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('niveauUrgence', EntityType::class, [
            'label' => 'Niveau d\'urgence',
            'class' => WorNiveauUrgence::class,
            'choice_label' => 'description',
            'placeholder' => '-- Choisir une niveau --',
            'required' => false,
        ])
        ->add('numDit', TextType::class, [
            'label' => 'n° DIT',
            'required' => false
        ])
        ->add('numOr', NumberType::class, [
            'label' => 'n° Or',
            'required' => false
        ])
        ->add('referencePiece', TextType::class, [
            'label' => 'Référence pièce',
            'required' => false
        ])
        ->add('designation', TextType::class, [
            'label' => 'Désignation',
            'required' => false
        ])
        
        ->add('constructeur', ChoiceType::class, [
            'label' =>  'Constructeur',
            'required' => false,
            'choices' => $this->recupConstructeur(),
            'placeholder' => ' -- choisir une constructeur --'
        ])
        ->add('dateDebut', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de création OR (début)',
            'required' => false,
        ])
        ->add('dateFin', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de création OR (fin)',
            'required' => false,
        ])
        ->add('orCompletNon',
        ChoiceType::class,
        [
            'label' => 'ORs Complet ou incomplet',
            'required' => false,
            'choices' => self::OR_COMPLET_OU_NON,
            'placeholder' => ' -- choisir une mode affichage --',
            'data' => 'ORs COMPLET'
        ])
        ->add('pieces',
        ChoiceType::class,
        [
            'label' => 'Pièces',
            'required' => false,
            'choices' => self::PIECE_MAGASIN_ACHATS_LOCAUX,
            'placeholder' => ' -- choisir une mode affichage --',
            'data' => 'PIECES MAGASIN'
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
