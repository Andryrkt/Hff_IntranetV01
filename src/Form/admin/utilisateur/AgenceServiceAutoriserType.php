<?php

namespace App\Form\admin\utilisateur;

use App\Model\LdapModel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\admin\utilisateur\AgenceServiceAutoriser;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Service\SessionManagerService;

class AgenceServiceAutoriserType extends AbstractType
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
                'Session_Utilisateur',
                ChoiceType::class,
                [
                    'label' => "Nom d'utilisateur",
                    'choices' => array_combine($nom, $nom),
                    'placeholder' => "-- Choisir un nom d'utilisateur --"
                ]
            )

            ->add(
                'Code_AgenceService_IRIUM',
                TextType::class,
                [
                    'label' => 'Agence/Service'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AgenceServiceAutoriser::class,
        ]);
    }
}
