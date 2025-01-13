<?php

namespace App\Form\dom;



use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\dom\DomSearch;
use App\Controller\Controller;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\StatutDemande;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dom\SousTypeDocument;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use App\Repository\admin\StatutDemandeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;



class DomSearchType extends AbstractType
{

    private $em;

    public function __construct()
    {
        $this->em = Controller::getEntity();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        ->add('statut', EntityType::class, [
            'label' => 'Statut',
            'class' => StatutDemande::class,
            'choice_label' => 'description',
            'placeholder' => '-- Choisir un statut --',
            'required' => false,
            'query_builder' => function (StatutDemandeRepository $er) {
                return $er->createQueryBuilder('s')
                    ->where('s.codeApp = :codeApp')
                    ->setParameter('codeApp', 'DOM');
            },
        ])
        ->add('sousTypeDocument', EntityType::class, [
            'label' => 'Sous Type',
            'class' => SousTypeDocument::class,
            'choice_label' => 'codeSousType',
            'placeholder' => '-- Choisir un type --',
            'required' => false,
        ])
        ->add('numDom', TextType::class, [
            'label' => "N° DOM",
            'required' => false
        ])
        ->add('matricule', TextType::class, [
            'label' => 'Matricule',
            'required' => false,
        ])
        ->add('dateDebut', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de création Début',
            'required' => false,
        ])
        ->add('dateFin', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de création Fin',
            'required' => false,
        ])
        ->add('dateMissionDebut', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date Mission Début',
            'required' => false,
        ])
        ->add('dateMissionFin', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date Mission Fin',
            'required' => false,
        ])
        ;
        
    }
    
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => DomSearch::class,
            ]);
            $resolver->setDefined('idAgenceEmetteur');
        }


}

