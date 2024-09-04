<?php

namespace App\Form\dit;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\dit\DitSearch;
use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use App\Repository\admin\StatutDemandeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class DitSearchType extends AbstractType
{
    const INTERNE_EXTERNE = [
        'INTERNE' => 'INTERNE',
        'EXTERNE' => 'EXTERNE'
    ];

    private $agenceRepository;

    private $em;
    
    public function __construct()
   {
        $this->em = controller::getEntity();
        $this->agenceRepository = $this->em->getRepository(Agence::class);
   }

   private function statutOr()
   {
        $statutOr = $this->em->getRepository(DemandeIntervention::class)->findStatutOr();

        return array_combine($statutOr, $statutOr);
   }

   private function sectionAffectee()
   {
        $sectionAffecte = $this->em->getRepository(DemandeIntervention::class)->findSectionAffectee();
        $groupes = ['Chef section', 'Chef de section', 'Responsable section']; // Les groupes de mots à supprimer
        $sectionAffectee = str_replace($groupes, "", $sectionAffecte);
        return array_combine($sectionAffectee, $sectionAffectee);
   }
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
     
       
        $builder
        ->add('niveauUrgence', EntityType::class, [
            'label' => 'Niveau d\'urgence',
            'class' => WorNiveauUrgence::class,
            'choice_label' => 'description',
            'placeholder' => '-- Choisir une niveau--',
            'required' => false,
            'attr' => [
                'class' => 'niveauUrgence'
            ]
        ])
        ->add('statut', EntityType::class, [
            'label' => 'Statut',
            'class' => StatutDemande::class,
            'choice_label' => 'description',
            'placeholder' => '-- Choisir une niveau --',
            'required' => false,
            'attr' => [
                'class' => 'statut'
            ],
            'query_builder' => function (StatutDemandeRepository $er) {
                return $er->createQueryBuilder('s')
                          ->where('s.codeApp = :codeApp')
                          ->setParameter('codeApp', 'DIT');
            },
        ])
            ->add('idMateriel', NumberType::class, [
                'label' => 'Id Materiel',
                'required' => false,
            ])
            ->add('typeDocument', EntityType::class, [
                'label' => 'Type de Document',
                'class' => WorTypeDocument::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir une type de document--',
                'required' => false,
            ])
            ->add('internetExterne', 
            ChoiceType::class, 
            [
                'label' => "Interne et Externe",
                'choices' => self::INTERNE_EXTERNE,
                'placeholder' => '-- Choisir --',
               'required' => false,
               'attr' => [ 'class' => 'interneExterne']
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
            ->add('numParc', TextType::class, [
                'label' => "N° Parc",
                'required' => false
            ])
            ->add('numSerie', TextType::class, [
                'label' => "N° Serie",
                'required' => false
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
                    $agence = $this->agenceRepository->find($agenceId);
            
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
                'label' => "Agence Debiteur",
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
                    'label' => "Service Debiteur",
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
                    $agence = $this->agenceRepository->find($agenceId);
            
                    if ($agence) {
                        $services = $agence->getServices();
                    } else {
                        $services = [];
                    }
                } else {
                    $services = [];
                }
            
                $form->add('serviceDebiteur', EntityType::class, [
                    'label' => "Service Debiteur",
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
            ->add('numDit',
            TextType::class,
            [
                'label' => 'N° DIT',
                'required' => false
            ])
            ->add('numOr',
            NumberType::class,
            [
                'label' => 'N° Or',
                'required' => false
            ])
            ->add('statutOr',
            ChoiceType::class,
            [
                'label' => 'Statut Or',
                'required' => false,
                'choices' => $this->statutOr(),
                'placeholder' => '-- choisir une statut --'
            ])
            ->add('ditRattacherOr', 
            CheckboxType::class,
            [
                'label' => 'Dit rattaché Or',
                'required' => false
            ])
           
            ->add('categorie', 
            EntityType::class, 
            [
                'label' => 'Catégorie de demande',
                'placeholder' => '-- Choisir une catégorie --',
                'class' => CategorieAteApp::class,
                'choice_label' =>'libelleCategorieAteApp',
                'required' => false,
            ])
            ->add('utilisateur',
            TextType::class,
            [
                'label' => 'Utilisateur',
                'required' => false
            ])
            ->add('sectionAffectee',
            ChoiceType::class,
            [
                'label' => 'Section affectée',
                'required' => false,
                'choices' => $this->sectionAffectee(),
                'placeholder' => '-- choisir une section --'
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DitSearch::class,
        ]);
        $resolver->setDefined('idAgenceEmetteur');
    }
}
