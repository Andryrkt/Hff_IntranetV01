<?php

namespace App\Form\tik;

use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailTikType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('categorie', EntityType::class, [
                'label'        => 'Catégorie',
                'attr'         => ['id' => 'categorie'],
                'placeholder'  => '-- Choix de catégorie --',
                'class'        => TkiCategorie::class,
                'choice_label' => 'description',
                'required'     => true,
                'multiple'     => false,
                'expanded'     => false,
                'data'         => $options['data']->getCategorie()
            ])
            ->add('sousCategorie', EntityType::class, [
                'label'        => 'Sous-catégories',
                'attr'         => ['id' => 'sous-categorie'],
                'placeholder'  => '-- Choix de sous-catégorie --',
                'class'        => TkiSousCategorie::class,
                'choice_label' => 'description',
                'required'     => false,
                'multiple'     => false,
                'expanded'     => false
            ])
            ->add('autresCategorie', EntityType::class, [
                'label'        => 'Autres catégories',
                'attr'         => ['id' => 'autre-categorie'],
                'placeholder'  => '-- Choix d\'autre catégorie --',
                'class'        => TkiAutresCategorie::class,
                'choice_label' => 'description',
                'required'     => false,
                'multiple'     => false,
                'expanded'     => false
            ])
            ->add('nomIntervenant', EntityType::class, [
                'label'        => 'Intervenant',
                'placeholder'  => '-- Choisir un intervenant --',
                'class'        => User::class,
                'choice_label' => 'nom_utilisateur',
                'multiple'     => false,
                'expanded'     => false
            ])
            ->add('niveauUrgence', EntityType::class, [
                'label'        => 'Niveau d\'urgence',
                'placeholder'  => '-- Choisir le niveau d\'urgence --',
                'class'        => WorNiveauUrgence::class,
                'choice_label' => 'description',
                'multiple'     => false,
                'expanded'     => false
            ])
        ;   
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeSupportInformatique::class
        ]);        
    }
}
