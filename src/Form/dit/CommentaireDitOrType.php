<?php

namespace App\Form\dit;


use Symfony\Component\Form\AbstractType;
use App\Entity\dit\CommentaireDitOr;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentaireDitOrType extends AbstractType
{
    const DIT_OR = [
        'sur l\'OR' => 'OR',
        'sur la DIT' => 'DIT'
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        
        
        $builder
            ->add('typeCommentaire',
            RadioType::class,
            [
                'label' => 'Choisissez une option :',
                'choices' => self::DIT_OR,
                'expanded' => true, // Pour afficher les options sous forme de boutons radio
                'multiple' => false, // Pour s'assurer qu'un seul choix est possible
                'required' => true, // Le champ est requis
            ])
            ->add('commentaire',
            TextareaType::class,
            [
                'label' => 'Votre Commentaire',
                'required' => true, // Définit si le champ est requis ou non
                'attr' => [
                    'placeholder' => 'Entrez votre commentaire ici...',
                    'rows' => 5, // Nombre de lignes visibles dans le textarea
                    'class' => 'custom-textarea-class', // Classe CSS pour le champ textarea
                ]
            ])
       ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommentaireDitOr::class,
        ]);
    }


}