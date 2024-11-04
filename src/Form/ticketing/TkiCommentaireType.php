<?php

namespace App\Form\ticketing;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TkiCommentairesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroTicket', TextType::class, [
                'label' => 'Numéro de Ticket',
            ])
            ->add('nomUtilisateur', TextType::class, [
                'label' => 'Nom de l\'Utilisateur',
            ])
            ->add('commentaires', TextareaType::class, [
                'label' => 'Commentaires',
            ])
            ->add('piecesJointes1', FileType::class, [
                'label' => 'Pièce Jointe 1',
                'required' => false,
            ])
            ->add('piecesJointes2', FileType::class, [
                'label' => 'Pièce Jointe 2',
                'required' => false,
            ])
            ->add('piecesJointes3', FileType::class, [
                'label' => 'Pièce Jointe 3',
                'required' => false,
            ])
            ->add('dateCommentaire', DateTimeType::class, [
                'label' => 'Date du Commentaire',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TkiCommentairesType::class,
        ]);
    }
}
?>