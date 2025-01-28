<?php

namespace App\Form\admin\historisation\documentOperation;

use App\Entity\admin\historisation\documentOperation\HistoriqueOperationDocumentSearch;
use App\Entity\admin\historisation\documentOperation\TypeDocument;
use App\Entity\admin\historisation\documentOperation\TypeOperation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoriqueOperationDocumentSearchType extends AbstractType
{
    const TYPE_STATUT = [
        'SUCCES' => 'Succès',
        'ECHEC'  => 'Echec',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('utilisateur', TextType::class, [
                'label' => 'Utilisateur',
                'required' => false,
            ])
            ->add('numeroDocument', TextType::class, [
                'label' => 'Numéro de document',
                'required' => false,
            ])
            ->add('statutOperation', ChoiceType::class, [ // à changer
                'label' => 'Statut de l\'opération',
                'choices' => self::TYPE_STATUT,
                'placeholder' => '-- Choisir un statut d\'opération --',
                'required' => false,
            ])
            ->add('typeOperation', EntityType::class, [
                'label' => 'Type d\'opération',
                'class' => TypeOperation::class,
                'choice_label' => 'typeOperation',
                'placeholder' => '-- Choisir un type d\'opération --',
                'required' => false,
            ])
            ->add('typeDocument', EntityType::class, [
                'label' => 'Type de document',
                'class' => TypeDocument::class,
                'choice_label' => function (TypeDocument $typeDocument) {
                    return $typeDocument->getTypeDocument() . ' (' . $typeDocument->getLibelleDocument() . ')';
                },
                'placeholder' => '-- Choisir un type de document --',
                'required' => false,
            ])
            ->add('dateOperationDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Opération Début',
                'required' => false,
            ])
            ->add('dateOperationFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Opération Fin',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HistoriqueOperationDocumentSearch::class,
        ]);
    }
}
