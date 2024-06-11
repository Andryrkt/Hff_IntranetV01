<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Model\LdapModel;
use App\Entity\Application;
use App\Entity\DemandeIntervention;
use App\Entity\WorTypeDocument;
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

class demandeInterventionType extends AbstractType
{
   

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
        ->add('typeDocument', 
            EntityType::class, [
                'label' => 'Role',
                'placeholder' => '-- Choisir une role --',
                'class' => WorTypeDocuments::class,
                'choice_label' =>'codeDocument',
                'query_builder' => function(RoleRepository $roleRepository) {
                    return $roleRepository->createQueryBuilder('r')->orderBy('r.codeDocument', 'ASC');
                }
            ])
            
        ->add('nom_utilisateur', 
        ChoiceType::class, 
        [
            'label' => "Nom d'utilisateur",
            'choices' => ,
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
            'data_class' => DemandeIntervention::class,
        ]);
    }


}