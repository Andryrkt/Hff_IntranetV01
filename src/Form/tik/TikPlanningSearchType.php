<?php

namespace App\Form\tik;

use App\Entity\admin\utilisateur\User;
use Symfony\Component\Form\AbstractType;
use App\Entity\tik\TikPlanningSearch;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\admin\utilisateur\UserRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TikPlanningSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('demandeur', TextType::class, [
                'label' => 'Demandeur',
                'required' => false,
            ])
            ->add('nomIntervenant', EntityType::class, [
                'label'        => 'Intervenant',
                'placeholder'  => '-- Choisir un intervenant --',
                'class'        => User::class,
                'choice_label' => 'nom_utilisateur',
                'required'     => false,
                'query_builder' => function (UserRepository $userRepository) {
                    return $userRepository
                        ->createQueryBuilder('u')
                        ->innerJoin('u.roles', 'r')  // Jointure avec la table 'roles'
                        ->where('r.id = :roleId')  // Filtre sur l'id du rôle
                        ->setParameter('roleId', 8)
                        ->orderBy('u.nom_utilisateur', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TikPlanningSearch::class,
        ]);
    }
}
