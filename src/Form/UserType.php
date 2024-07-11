<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\Societte;
use App\Model\LdapModel;
use App\Entity\Personnel;
use App\Entity\Application;
use App\Controller\Controller;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use App\Repository\AgenceRepository;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class UserType extends AbstractType
{
    private $ldap;
    private $agenceRepository;

    public function __construct()
    {
        $this->ldap = new LdapModel();
        $this->agenceRepository = Controller::getEntity()->getRepository(Agence::class);
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
        ->add('roles', 
            EntityType::class, [
                'label' => 'Role',
                'placeholder' => '-- Choisir une role --',
                'class' => Role::class,
                'choice_label' =>'role_name',
                'query_builder' => function(RoleRepository $roleRepository) {
                    return $roleRepository->createQueryBuilder('r')->orderBy('r.role_name', 'ASC');
                },
                'multiple' => true,
                'expanded' => true
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
        ->add('societtes',
            EntityType::class,
            [
                'label' => 'Sociétes',
                'class' => Societte::class,
                'choice_label' => function (Societte $societte): string {
                    return $societte->getCodeSociete() . ' ' . $societte->getNom();
                },
                'multiple' => true,
                'expanded' => true
            ])
            ->add('personnels', 
            EntityType::class,
            [
                'label' => 'Nom Personnel',
                'class' => Personnel::class,
                'choice_label' => 'Matricule',
                'placeholder' => '-- Choisir un nom --',
            ])
            ->add('superieurs', EntityType::class, [
                'label' => 'Supérieurs',
                'class' => User::class,
                'choice_label' => 'nom_utilisateur',
                'multiple' => true,
                'expanded' => false,
                
            ])
        ->add('agences',
        EntityType::class,
        [
            'label' => 'agence de l\'utilisateur',
            'class' => Agence::class,
                'choice_label' => function (Agence $service): string {
                    return $service->getCodeAgence() . ' ' . $service->getLibelleAgence();
                },
            'attr' => [ 'class' => 'agenceDebiteur']
                
        ])
        ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options){
            $form = $event->getForm();
            $data = $event->getData();
            $services = null;
            
            if ($data instanceof User && $data->getAgences()) {
                $services = $data->getAgences()->getServices();
            }
            //$services = $data->getAgence()->getServices();
            // $agence = $event->getData()->getAgence() ?? null;
            // $services = $agence->getServices();
      
            $form->add('services',
            EntityType::class,
            [
            
            'label' => 'Service Debiteur',
            'class' => Service::class,
            'choice_label' => function (Service $service): string {
                return $service->getCodeService() . ' ' . $service->getLibelleService();
            },
            'choices' => $services,
            // 'disabled' => $agence === null,
            'required' => false,
            'query_builder' => function(ServiceRepository $serviceRepository) {
                    return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                },
            //'data' => $options['data']->getService(),
                'attr' => [ 'class' => 'serviceDebiteur'],
                'multiple' => true,
                'expanded' => false
            ]);

        })
        ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event)  {
            $form = $event->getForm();
            $data = $event->getData();
      
            $agenceId = $data['agences'] ?? null;

            if ($agenceId) {
               
                $agence = $this->agenceRepository->find($agenceId);
                $services = $agence ? $agence->getServices() : [];

                $form->add('services', EntityType::class, [
                    'label' => 'Service Debiteur',
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'choices' => $services,
                    'required' => false,
                    'attr' => ['class' => 'serviceDebiteur'],
                    'multiple' => true,
                'expanded' => false
                ]);
            //Ajouter des validations ou des traitements supplémentaires ici si nécessaire
        }})
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