<?php

namespace App\Form;


use App\Entity\Societte;
use App\Entity\Application;
use App\Entity\CategorieATEAPP;
use App\Entity\WorTypeDocument;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RoleRepository;
use App\Entity\DemandeIntervention;
use App\Entity\WorNiveauUrgence;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class demandeInterventionType extends AbstractType
{
   

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeReparation = [
            'EN COURS' => 'EN COURS',
            'DEJA EFFECTUEE' => 'DEJA EFFECTUEE',
            'A REALISER' => 'A REALISER'
        ];

        $reparationRealise = [
            'ATELIER' => 'ATELIER',
            'ENERGIE' => 'ENERGIE'
        ];

        $internetExterne = [
            'INTERNE' => 'INTERNE',
            'EXTERNE' => 'EXTERNE'
        ];

        $ouiNon = [
            'NON' => 'NON',
            'OUI' => 'OUI'
            
        ];
        
        $builder
        ->add('typeDocument', 
            EntityType::class, [
                'label' => 'type de document',
                'placeholder' => '-- Choisir un type de document --',
                'class' => WorTypeDocument::class,
                'choice_label' =>'codeDocument',
                'required' => false,
                // 'query_builder' => function(RoleRepository $roleRepository) {
                //     return $roleRepository->createQueryBuilder('r')->orderBy('r.codeDocument', 'ASC');
                // }
            ])
        ->add('codeSociete', 
        EntityType::class, [
            'label' => 'Société',
            'placeholder' => '-- Choisir une société --',
            'class' => Societte::class,
            'choice_label' =>'codeSociete',
            'required' => false,
        ])
        ->add('typeReparation', 
        ChoiceType::class, 
        [
            'label' => "Type de réparation",
            'choices' => $typeReparation,
            'placeholder' => '-- Choisir un type de réparation --',
            'required' => false,
           
        ])
        ->add('reparationRealise', 
        ChoiceType::class, 
        [
            'label' => "Type de réparation",
            'choices' => $reparationRealise,
            'placeholder' => '-- Choisir le répartion réalisé --',
            'required' => false,
           
        ])
        ->add('categorieDemande', 
        EntityType::class, [
            'label' => 'catégorie de demande',
            'placeholder' => '-- Choisir une catégorie --',
            'class' => CategorieATEAPP::class,
            'choice_label' =>'codeSociete',
            'required' => false,
        ])
        ->add('internetExterne', 
        ChoiceType::class, 
        [
            'label' => "Interne et Externe",
            'choices' => $internetExterne,
            'placeholder' => '-- Choisir --',
           'required' => false,
        ])
        ->add('nomClient',
        TextType::class,
        [
            'label' => 'Nom du client',
            'required' => false,
        ])
        ->add('numeroTel',
        TelType::class,
        [
            'label' => 'N° téléphone',
            'required' => false,
        ])
        ->add('dateOr', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date OR',
            'required' => false,
        ])
        ->add('heureOR',
        TextType::class,
        [
            'label' => 'Heure OR',
            'required' => false,
        ])
        ->add('datePrevueTravaux', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date prévue travaux',
            'required' => false,
        ])
        ->add('demandeDevis', 
        ChoiceType::class, 
        [
            'label' => "Demande de devis",
            'choices' => $ouiNon,
            'placeholder' => '-- Choisir --',
           'required' => false,
        ])
        ->add('idNiveauUrgence', 
        EntityType::class, [
            'label' => 'Niveau d\'urgence',
            'placeholder' => '-- Choisir une niveau --',
            'class' => WorNiveauUrgence::class,
            'choice_label' =>'codeSociete',
            'required' => false,
        ])
        ->add('avisRecouvrement', 
        ChoiceType::class, 
        [
            'label' => "Avis de recouvrement",
            'choices' => $ouiNon,
           'required' => false,
        ])
        ->add('clientSousContrat', 
        ChoiceType::class, 
        [
            'label' => "client sous contrat",
            'choices' => $ouiNon,
           'required' => false,
        ])
        ->add('objetDemande',
        TextType::class,
        [
            'label' => 'Objet de la demande',
            'required' => false,
        ])
        ->add('detailDemande',
        TextareaType::class,
        [
            'label' => 'Détail de la demande',
            'required' => false,
        ])
        ->add('livraisonPartiel', 
        ChoiceType::class, 
        [
            'label' => "livraison Partiel",
            'choices' => $ouiNon,
           'required' => false,
        ])
        ->add('mailDemandeur',
        EmailType::class,
        [
            'label' => 'Mail du demandeur',
            'required' => false,
        ])
        ->add('pieceJoint03',
        FileType::class, 
        )
        ->add('pieceJoint02',
        FileType::class, 
        )
        ->add('pieceJoint01',
        FileType::class, 
        )
        
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