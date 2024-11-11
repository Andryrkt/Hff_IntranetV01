<?php

namespace App\Form\admin\tik;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetInformatiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateCreation', DateType::class, [
                'label' => 'Date de Création',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('numeroProjet', TextType::class, [
                'label' => 'Numéro du Projet',
            ])
            ->add('utilisateurDemandeur', TextType::class, [
                'label' => 'Utilisateur Demandeur',
            ])
            ->add('mailDemandeur', EmailType::class, [
                'label' => 'Email du Demandeur',
            ])
            ->add('codeSociete', IntegerType::class, [
                'label' => 'Code Société',
            ])
            ->add('idTKICategorie', IntegerType::class, [
                'label' => 'ID TKI Catégorie',
            ])
            ->add('idTKISousCategorie', IntegerType::class, [
                'label' => 'ID TKI Sous Catégorie',
            ])
            ->add('agenceServiceEmetteur', TextType::class, [
                'label' => 'Agence/Service Emetteur',
            ])
            ->add('agenceServiceDebiteur', TextType::class, [
                'label' => 'Agence/Service Débiteur',
            ])
            ->add('nomIntervenant', TextType::class, [
                'label' => 'Nom Intervenant',
            ])
            ->add('mailIntervenant', EmailType::class, [
                'label' => 'Email Intervenant',
            ])
            ->add('objetDemande', TextType::class, [
                'label' => 'Objet de la Demande',
            ])
            ->add('detailDemande', TextareaType::class, [
                'label' => 'Détails de la Demande',
            ])
            ->add('pieceJointe1', TextType::class, [
                'label' => 'Pièce Jointe 1',
            ])
            ->add('pieceJointe2', TextType::class, [
                'label' => 'Pièce Jointe 2',
                'required' => false,
            ])
            ->add('pieceJointe3', TextType::class, [
                'label' => 'Pièce Jointe 3',
                'required' => false,
            ])
            ->add('dateDebPlanning', DateType::class, [
                'label' => 'Date de Début du Planning',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('dateFinPlanning', DateType::class, [
                'label' => 'Date de Fin du Planning',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('avancementProjet', IntegerType::class, [
                'label' => 'Avancement du Projet (%)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetInformatiqueType::class,
        ]);
    }
}
?>