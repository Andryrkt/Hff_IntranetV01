<?php

namespace App\Form\admin\utilisateur;

use App\Model\LdapModel;
use App\Entity\admin\Application;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\utilisateur\ProfilUser;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Service\SessionManagerService;

class ProfilUserType extends AbstractType
{
    private $ldap;
    private SessionManagerService $sessionService;

    public function __construct(SessionManagerService $sessionService)
    {
        $this->ldap = new LdapModel();
        $this->sessionService = $sessionService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $users = $this->ldap->infoUser($this->sessionService->get('user'), $this->sessionService->get('password'));

        $nom = [];
        foreach ($users as $key => $value) {
            $nom[] = $key;
        }

        $builder
            ->add(
                'utilisateur',
                ChoiceType::class,
                [
                    'label' => "Nom d'utilisateur",
                    'choices' => array_combine($nom, $nom),
                    'placeholder' => "-- Choisir un nom d'utilisateur --"

                ]
            )
            ->add(
                'profil',
                ChoiceType::class,
                [
                    'label' => 'Rôle',
                    'choices' => [
                        'utilisateur' => 'utilisateur',
                        'validateur' => 'validateur'
                    ],
                    'placeholder' => "-- Choisir une rôle --"
                ]
            )
            ->add(
                'app',
                EntityType::class,
                [
                    'label' => 'Applications',
                    'class' => Application::class,
                    'choice_label' => 'codeApp',
                    'placeholder' => '-- Choisir une Application --'
                ]
            )
            ->add(
                'matricule',
                NumberType::class,
                [
                    'label' => 'Numero Matricule',
                    'required' => false
                ]
            )
            ->add(
                'mail',
                EmailType::class,
                [
                    'label' => 'Email',
                    'required' => false
                ]
            )

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfilUser::class,
        ]);
    }
}
