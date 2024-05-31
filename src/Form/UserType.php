<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Model\LdapModel;
use App\Entity\Application;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RoleRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormEvent;

class UserType extends AbstractType
{
    private $ldap;
    public function __construct()
    {
        $this->ldap = new LdapModel();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $users = $this->ldap->infoUser($_SESSION['user'], $_SESSION['password']);

        $nom = [];
        foreach ($users as $key => $value) {
            $nom[]=$key;
        }

        $builder
        ->add('nom_utilisateur', 
        ChoiceType::class, 
        [
            'label' => "Nom d'utilisateur",
            'choices' => array_combine($nom, $nom),
            'placeholder' => '-- Choisir un nom d\'utilisateur --'
           
        ])
        ->add('matricule', 
            NumberType::class,
            [
                'label' => 'Numero Matricule',
                'required'=>false,
                // 'disabled' => true
            ])
        ->add('mail', 
            EmailType::class, [
                'label' => 'Email',
                'required' =>false,
                // 'disabled' => true
            ])
        ->add('role', 
            EntityType::class, [
                'label' => 'Role',
                'placeholder' => '-- Choisir une role --',
                'class' => Role::class,
                'choice_label' =>'role_name',
                'query_builder' => function(RoleRepository $roleRepository) {
                    return $roleRepository->createQueryBuilder('r')->orderBy('r.role_name', 'ASC');
                }
            ])
        ->add('applications',
            EntityType::class,
            [
                'label' => 'Applications',
                'class' => Application::class,
                'choice_label' => 'codeApp',
                'multiple' => true,
                'expanded' => true
            ])
        // ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event){
        //     $nomUtilisateur = $event->getData();
        //     dd($nomUtilisateur);
        // })
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }


}