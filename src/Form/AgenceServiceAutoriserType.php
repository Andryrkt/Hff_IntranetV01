<?php

namespace App\Form;

use App\Model\LdapModel;
use App\Entity\AgenceServiceAutoriser;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
        ->add('utilisateur', 
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