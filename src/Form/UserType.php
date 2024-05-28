<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Model\LdapModel;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            'required'=>false
        ])
    ->add('mail', 
        EmailType::class, [
            'label' => 'Email',
        'required' =>false
        ])
        ->add('role', 
       EntityType::class, [
            'label' => 'Role',
            'placeholder' => '-- Choisir une role --',
            'class' => Role::class,
            'choice_label' =>'role_name'
        ])
    
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }


}