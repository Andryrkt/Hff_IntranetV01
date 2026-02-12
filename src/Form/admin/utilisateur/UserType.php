<?php

namespace App\Form\admin\utilisateur;

use App\Dto\admin\UserDTO;
use App\Model\LdapModel;
use App\Entity\admin\Personnel;
use App\Entity\admin\AgenceServiceIrium;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\utilisateur\Profil;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        global $container;
        $userInfo = $container->get('session')->get('user_info');
        $users = (new LdapModel())->infoUser($userInfo['username'], $userInfo['password']);
        $nom = array_keys($users);

        $builder
            ->add(
                'username',
                ChoiceType::class,
                [
                    'label'       => "Nom d'utilisateur *",
                    'choices'     => array_combine($nom, $nom),
                    'placeholder' => '-- Choisir un nom d\'utilisateur --',
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label'    => 'Email *',
                    'required' => true,

                ]
            )
            ->add(
                'agenceServiceIrium',
                EntityType::class,
                [
                    'label'        => 'Code Sage *',
                    'class'        => AgenceServiceIrium::class,
                    'choice_label' => 'service_sage_paie',
                    'placeholder'  => "-- choisir une code sage --",
                    'required'     => true,
                ]
            )
            ->add(
                'personnel',
                EntityType::class,
                [
                    'label'        => 'Matricule *',
                    'class'        => Personnel::class,
                    'choice_label' => 'Matricule',
                    'placeholder'  => '-- Choisir une matricule --',
                    'required'     => true,
                ]
            )
            ->add(
                'profils',
                EntityType::class,
                [
                    'label'        => 'Profil(s) *',
                    'class'        => Profil::class,
                    'choice_label' => function (Profil $profil): string {
                        return $profil->getReference() . ' - ' . $profil->getDesignation();
                    },
                    'multiple'     => true,
                    'expanded'     => false,
                    'required'     => true,

                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserDTO::class,
        ]);
    }
}
