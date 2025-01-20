<?php

namespace App\Form\admin\utilisateur;

use App\Entity\admin\Agence;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\admin\utilisateur\ContactAgenceAte;
use App\Repository\admin\utilisateur\UserRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class ContactAgenceAteType extends AbstractType
{
    const REPARATION_REALISE = [
        'ATE TANA' => 'ATE TANA',
        'ATE STAR' => 'ATE STAR',
        'ATE MAS' => 'ATE MAS',
        'ATE TMV' => 'ATE TMV',
        'ATE FTU' => 'ATE FTU',
        'ATE ABV' => 'ATE ABV',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('agence', EntityType::class,
        [

            'label' => 'Agence *',
            'placeholder' => 'Choisir une agence *',
            'class' => Agence::class,
            'choice_label' => function (Agence $service): string {
                return $service->getCodeAgence() . ' ' . $service->getLibelleAgence();
            },
        ])
        ->add('matricule', EntityType::class, 
        [
            'label' => 'N° matricule',
            'placeholder' => 'Choisir une matricule *',
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
            'placeholder' => 'Choisir un nom *',
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
            'placeholder' => 'Choisir une email *',
            'class' => User::class,
            'choice_label' => 'mail',
            'query_builder' => function (UserRepository $userRepository) {
                return $userRepository->createQueryBuilder('u')->orderBy('u.mail', 'ASC');
            },
            'attr' => [
                'class' => 'selecteur2'
            ]
        ])
        ->add('telephone', TelType::class, [
            'label' => 'N° Telephone *',
            'data' => '+261',
        ])
        ->add('prenom', TextType::class, [
            'label' => 'Prénoms *'
        ])
        ->add('atelier', 
        ChoiceType::class,
        [
            'label' => "Atelier *",
            'choices' => self::REPARATION_REALISE,
            'placeholder' => '-- Choisir le répartion réalisé --',
            'required' => true,
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