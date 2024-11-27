<?php

namespace App\Form\admin\utilisateur;

use App\Model\LdapModel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\admin\utilisateur\AgenceServiceAutoriser;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AgenceServiceAutoriserType extends AbstractType
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
        ->add('Session_Utilisateur', 
        ChoiceType::class, 
        [
            'label' => "Nom d'utilisateur",
            'choices' => array_combine($nom, $nom),
            'placeholder' => '-- Choisir un nom d\'utilisateur --'
        ])
    
        ->add('Code_AgenceService_IRIUM', 
            TextType::class,
            [
                'label' => 'Agence/Service'
            ])    
        ;

    
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AgenceServiceAutoriser::class,
        ]);
    }
}