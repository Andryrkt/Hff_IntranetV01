<?php

// src/Form/SearchType.php
namespace App\Form;

use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\BadmSearch;
use App\Entity\StatutDemande;
use App\Entity\TypeMouvement;
use App\Controller\Controller;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\StatutDemandeRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BadmSearchType extends AbstractType
{
  private $em;

  public function __construct()
  {
    $this->em = Controller::getEntity();
  }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //dd($options);
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
                    ->setParameter('codeApp', 'BDM');
            },
        ])
            ->add('idMateriel', TextType::class, [
                'label' => 'Id Materiel',
                'required' => false,
            ])
            ->add('numParc', TextType::class, [
                'label' => "N° Parc",
                'required' => false
            ])
            ->add('numSerie', TextType::class, [
                'label' => "N° Serie",
                'required' => false
            ])
            ->add('typeMouvement', EntityType::class, [
                'label' => 'Type Mouvement',
                'class' => TypeMouvement::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir une type de mouvement--',
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Demande Début',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Demande Fin',
                'required' => false,
            ])
            ->add('agenceEmetteur', EntityType::class, [
                'label' => "Agence Emetteur",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence--',
                'required' => false,
                'attr' => [ 'class' => 'agenceEmetteur']
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
            
                if ($data && $data->getAgenceEmetteur()) {
                    $services = $data->getAgenceEmetteur()->getServices();
                } else {
                    $services = [];
                }
            
                $form->add('serviceEmetteur', EntityType::class, [
                    'label' => "Service Emetteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir une service--',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function(ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => [ 'class' => 'serviceEmetteur']
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
            
                if (isset($data['agenceEmetteur']) && $data['agenceEmetteur']) {
                    $agenceId = $data['agenceEmetteur'];
                    $agence = $this->em->getRepository(Agence::class)->find($agenceId);
            
                    if ($agence) {
                        $services = $agence->getServices();
                    } else {
                        $services = [];
                    }
                } else {
                    $services = [];
                }
            
                $form->add('serviceEmetteur', EntityType::class, [
                    'label' => "Service Emetteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir une service--',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function(ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => [ 'class' => 'serviceEmetteur']
                ]);
            })
            ->add('agenceDebiteur', EntityType::class, [
                'label' => "Agence Destinataire",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence--',
                'required' => false,
                'attr' => [ 'class' => 'agenceDebiteur']
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
            
                if ($data && $data->getAgenceDebiteur()) {
                    $services = $data->getAgenceDebiteur()->getServices();
                } else {
                    $services = [];
                }
            
                $form->add('serviceDebiteur', EntityType::class, [
                    'label' => "Service Destinataire",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir une service--',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function(ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => [ 'class' => 'serviceDebiteur']
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
            
                if (isset($data['agenceDebiteur']) && $data['agenceDebiteur']) {
                    $agenceId = $data['agenceDebiteur'];
                    $agence = $this->em->getRepository(Agence::class)->find($agenceId);
            
                    if ($agence) {
                        $services = $agence->getServices();
                    } else {
                        $services = [];
                    }
                } else {
                    $services = [];
                }
            
                $form->add('serviceDebiteur', EntityType::class, [
                    'label' => "Service Destiantaire",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir une service--',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function(ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => [ 'class' => 'serviceDebiteur']
                ]);
            }) 
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BadmSearch::class
        ]);
    }
}
