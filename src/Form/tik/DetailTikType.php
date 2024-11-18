<?php

namespace App\Form\tik;

use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Repository\admin\dit\WorNiveauUrgenceRepository;
use App\Repository\admin\tik\TkiAutreCategorieRepository;
use App\Repository\admin\tik\TkiCategorieRepository;
use App\Repository\admin\tik\TkiSousCategorieRepository;
use App\Repository\admin\utilisateur\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailTikType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateDebutPlanning', DateType::class, [
                'widget'     => 'single_text',
                'label'      => 'Date début planning ticket',
                'required'   => false,
            ])
            ->add('dateFinPlanning', DateType::class, [
                'widget'     => 'single_text',
                'label'      => 'Date fin planning ticket',
                'required'   => false,
            ])
            ->add('categorie', EntityType::class, [
                'label'        => 'Catégorie',
                'attr'         => ['class' => 'categorie'],
                'placeholder'  => '-- Choisir une catégorie --',
                'class'        => TkiCategorie::class,
                'query_builder'=> function(TkiCategorieRepository $TkiCategorieRepository) {
                    return $TkiCategorieRepository
                        ->createQueryBuilder('t')
                        ->orderBy('t.description', 'ASC');
                },
                'choice_label' => 'description',
                'required'     => true,
                'multiple'     => false,
                'expanded'     => false,
                'data'         => $options['data']->getCategorie()
            ])
            ->add('sousCategorie', EntityType::class, [
                'label'        => 'Sous-catégories',
                'attr'         => ['class' => 'sous-categorie'],
                'placeholder'  => '-- Choisir une sous-catégorie --',
                'class'        => TkiSousCategorie::class,
                'query_builder'=> function(TkiSousCategorieRepository $TkiSousCategorieRepository) {
                    return $TkiSousCategorieRepository
                        ->createQueryBuilder('t')
                        ->orderBy('t.description', 'ASC');
                },
                'choice_label' => 'description',
                'required'     => false,
                'multiple'     => false,
                'expanded'     => false
            ])
            ->add('autresCategorie', EntityType::class, [
                'label'        => 'Autres catégories',
                'attr'         => ['class' => 'autre-categorie'],
                'placeholder'  => '-- Choix d\'autre catégorie --',
                'class'        => TkiAutresCategorie::class,
                'query_builder'=> function(TkiAutreCategorieRepository $TkiAutreCategorieRepository) {
                    return $TkiAutreCategorieRepository
                        ->createQueryBuilder('t')
                        ->orderBy('t.description', 'ASC');
                },
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
                'query_builder'=> function(UserRepository $userRepository) {
                    return $userRepository
                        ->createQueryBuilder('u')
                        ->innerJoin('u.roles', 'r')  // Jointure avec la table 'roles'
                        ->where('r.id = :roleId')  // Filtre sur l'id du rôle
                        ->setParameter('roleId', 8) 
                        ->orderBy('u.nom_utilisateur', 'ASC');;
                },
                'multiple'     => false,
                'expanded'     => false
            ])
            ->add('niveauUrgence', EntityType::class, [
                'label'        => 'Niveau d\'urgence',
                'placeholder'  => '-- Choisir le niveau d\'urgence --',
                'class'        => WorNiveauUrgence::class,
                'query_builder'=> function(WorNiveauUrgenceRepository $WorNiveauUrgenceRepository) {
                    return $WorNiveauUrgenceRepository
                        ->createQueryBuilder('w')
                        ->orderBy('w.description', 'DESC');
                },
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
