<?php

namespace App\Form\magasin;


use App\Model\magasin\MagasinModel;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class MagasinListOrSearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
  
        $magasinModel = new MagasinModel();
        $constructeur = $magasinModel->recuperationConstructeur();
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
            'required' => false,
            'attr' => [
                'autocomplete' => 'off',
            ],
        ])
        ->add('orATraiter', CheckboxType::class, [
            'label' => 'OR à taiter',
            'required' => false
        ])
        ->add('qteReserve', CheckboxType::class, [
            'label' => 'Qté réservé > 0',
            'required' => false
        ])
        ->add('qteLivree', CheckboxType::class, [
            'label' => 'Qté livrée > 0',
            'required' => false
        ])
        ->add('qteReliquat', CheckboxType::class, [
            'label' => 'Qté reliquat > 0',
            'required' => false
        ])
        ->add('constructeur', ChoiceType::class, [
            'label' =>  'Constructeur',
            'required' => false,
            'choices' => $constructeur,
            'placeholder' => ' -- choisir une constructeur --'
        ])
        ->add('dateDebut', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de création OR (début)',
            'required' => false,
            'data' => $options['data']['monday'],
        ])
        ->add('dateFin', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de création OR (fin)',
            'required' => false,
            'data' => $options['data']['dateDay']
        ])
        ->add('numCommande', TextType::class, [
            'label' => 'N° de commande',
            'required' => false
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
       
    }
}
