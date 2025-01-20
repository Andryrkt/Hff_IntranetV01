<?php

namespace App\Form\admin\utilisateur;

use App\Entity\admin\Agence;
use App\Entity\admin\AgenceServiceIrium;
use App\Entity\admin\Personnel;
use App\Entity\admin\utilisateur\ContactAgenceAte;
use App\Entity\admin\utilisateur\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
            'choice_label' => 'matricule'
        ])
        ->add('nom', EntityType::class, 
        [
            'mapped' => false,
            'label' => 'Nom',
            'placeholder' => 'Choisir un nom',
            'class' => Personnel::class,
            'choice_label' => 'Nom'
        ])
        ->add('email', EntityType::class,
        [
            'mapped' => false,
            'label' => 'E-mail',
            'placeholder' => 'Choisir une email',
            'class' => User::class,
            'choice_label' => 'mail'
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