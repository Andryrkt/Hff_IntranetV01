<?php

namespace App\Form;

use App\Entity\Application;
use App\Entity\ProfilUser;
use App\Entity\ProfilUserEntity;
use App\Model\LdapModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilUserType extends AbstractType
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
        ->add('utilisateur', 
        ChoiceType::class, 
        [
            'label' => "Nom d'utilisateur",
            'choices' => array_combine($nom, $nom),
            'placeholder' => '-- Choisir un nom d\'utilisateur --'
           
        ])
    ->add('profil', 
        ChoiceType::class, 
        [
            'label' => 'Rôle',
            'choices' => [
                'utilisateur' => 'utilisateur',
                'validateur' => 'validateur'
            ],
            'placeholder' => '-- Choisir une rôle --'
        ])
    ->add('app', 
        EntityType::class, 
        [
            'label' => 'Applications',
            'class' => Application::class,
            'choice_label' => 'codeApp',
            'placeholder' => '-- Choisir une Application --'
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
    
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfilUser::class,
        ]);
    }


}