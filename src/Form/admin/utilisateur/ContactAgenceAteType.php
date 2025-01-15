<?php

namespace App\Form\admin\utilisateur;

use App\Entity\admin\Agence;
use App\Entity\admin\Personnel;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\AgenceServiceIrium;
use Symfony\Component\Form\AbstractType;
use App\Repository\admin\PersonnelRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\admin\utilisateur\ContactAgenceAte;
use App\Repository\admin\utilisateur\UserRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ContactAgenceAteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('agence', EntityType::class,
        [

            'label' => 'Agence',
            'placeholder' => 'Choisir une agence',
            'class' => Agence::class,
            'choice_label' => function (Agence $service): string {
                return $service->getCodeAgence() . ' ' . $service->getLibelleAgence();
            },
        ])
        ->add('matricule', EntityType::class, 
        [
            'label' => 'N° matricule',
            'placeholder' => 'Choisir une matricule',
            'class' => User::class,
            'choice_label' => 'matricule',
            'query_builder' => function (UserRepository $userRepository) {
                return $userRepository->createQueryBuilder('u')->orderBy('u.matricule', 'ASC');
            },
            'attr' => [
                'class' => 'selecteur2'
            ]
        ])
        ->add('nom', EntityType::class, 
        [
            'label' => 'Nom',
            'placeholder' => 'Choisir un nom',
            'class' => User::class,
            'choice_label' => function (User $user) {
                if($user->getPersonnels() !== null) {
                    return $user->getPersonnels()->getNom();
                }
            },
            'query_builder' => function (UserRepository $er) {
                return $er->createQueryBuilder('u')
                        ->leftJoin('u.personnels', 'p') // Jointure si nécessaire
                        ->orderBy('p.Nom', 'ASC'); // Trier par le nom
            },
            'attr' => [
                'class' => 'selecteur2'
            ]
        ])

        ->add('email', EntityType::class,
        [
            'label' => 'E-mail',
            'placeholder' => 'Choisir une email',
            'class' => User::class,
            'choice_label' => 'mail',
            'query_builder' => function (UserRepository $userRepository) {
                return $userRepository->createQueryBuilder('u')->orderBy('u.mail', 'ASC');
            },
            'attr' => [
                'class' => 'selecteur2'
            ]
        ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContactAgenceAte::class,
        ]);
    }
}