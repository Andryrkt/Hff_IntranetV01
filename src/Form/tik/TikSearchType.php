<?php

namespace App\Form\tik;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\tik\TikSearch;
use App\Controller\Controller;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\StatutDemande;
use Symfony\Component\Form\FormEvent;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Repository\admin\ServiceRepository;
use App\Entity\admin\tik\TkiAutresCategorie;
use Symfony\Component\Form\FormBuilderInterface;
use App\Repository\admin\StatutDemandeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\admin\tik\TkiCategorieRepository;
use App\Repository\admin\utilisateur\UserRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\admin\tik\TkiSousCategorieRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Repository\admin\tik\TkiAutreCategorieRepository;

class TikSearchType extends AbstractType
{
    private $agenceRepository;
    private $sousCategorieRepository;
    private $categoriesRepository;

    public function __construct()
   {
        $this->agenceRepository = Controller::getEntity()->getRepository(Agence::class);
        $this->sousCategorieRepository = Controller::getEntity()->getRepository(TkiSousCategorie::class);
        $this->categoriesRepository = Controller::getEntity()->getRepository(TkiCategorie::class);
   }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('demandeur', TextType::class, [
                'label' => 'Demandeur',
                'required' => false,
            ])
            ->add('numParc', TextType::class, [
                'label' => 'Numéro parc PC',
                'required' => false
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
            ->add('numeroTicket', TextType::class, [
                'label' => 'Numéro ticket',
                'required' => false
            ])
            ->add('statut', EntityType::class, [
                'label' => 'Statut',
                'class' => StatutDemande::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir un statut --',
                'required' => false,
                'attr' => [
                    'class' => 'statut'
                ],
                'query_builder' => function (StatutDemandeRepository $er) {
                    return $er->createQueryBuilder('s')
                                ->where('s.codeApp = :codeApp')
                                ->setParameter('codeApp', 'TKI');
                },
            ])
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
            ->add('nomIntervenant', EntityType::class, [
                'label'        => 'Intervenant',
                'placeholder'  => '-- Choisir un intervenant --',
                'class'        => User::class,
                'choice_label' => 'nom_utilisateur',
                'required' => false,
                'query_builder' => function(UserRepository $userRepository) {
                    return $userRepository
                    ->createQueryBuilder('u')
                    ->innerJoin('u.roles', 'r')  // Jointure avec la table 'roles'
                    ->where('r.id = :roleId')  // Filtre sur l'id du rôle
                    ->setParameter('roleId', 8) 
                    ->orderBy('u.nom_utilisateur', 'ASC');
                }
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

            ->add('categorie', EntityType::class, [
                'label' => 'Catégorie',
                'class' => TkiCategorie::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir une categorie--',
                'required' => false,
                'query_builder' => function(EntityRepository $tkiCategorie) {
                    return $tkiCategorie->createQueryBuilder('c')->orderBy('c.description', 'ASC');
                },
                'attr' => ['class' => 'categorie']
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
            
                $sousCategorie = [];
                if ($data && $data->getCategorie()) {
                    $sousCategorie = $data->getCategorie()->getSousCategories();
                }
            
                $autresCategories = [];
                if($data && $data->getSousCategorie()) {
                    $autresCategories = $data->getSousCategorie()->getAutresCategories();
                }

                $form->add('sousCategorie', EntityType::class, [
                    'label' => 'Sous Catégorie',
                    'class' => TkiSousCategorie::class,
                    'choice_label' => 'description',
                    'placeholder' => '-- Choisir une sous categorie--',
                    'required' => false,
                    'choices' => $sousCategorie,
                    'query_builder' => function(EntityRepository $tkiCategorie) {
                        return $tkiCategorie->createQueryBuilder('sc')->orderBy('sc.description', 'ASC');
                    },
                    'attr' => ['class' => 'sous-categorie']
                ]);

                $form->add('autresCategories', EntityType::class, [
                    'label' => 'Autres Catégories',
                    'class' => TkiAutresCategorie::class,
                    'choice_label' => 'description',
                    'placeholder' => '-- Choisir une autre categorie--',
                    'required' => false,
                    'choices' => $autresCategories,
                    'query_builder' => function(EntityRepository $tkiCategorie) {
                        return $tkiCategorie->createQueryBuilder('ac')->orderBy('ac.description', 'ASC');
                    },
                    'attr' => ['class' => 'autres-categories']
                ]);
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                //souscategorie
                $sousCategories = [];
                    if (isset($data['categorie']) && $data['categorie']) {
                        $categorieId = $data['categorie'];
                        $categorie = $this->categoriesRepository->find($categorieId);
                
                        if ($categorie) {
                            $sousCategories = $categorie->getSousCategories();
                        } 
                    }

                    //autrecategorie
                $autresCategories = [];
                if (isset($data['sousCategorie']) && $data['sousCategorie']) {
                    $sousCategorieId = $data['sousCategorie'];
                    $sousCategorie = $this->sousCategorieRepository->find($sousCategorieId);
            
                    if ($categorie) {
                        $autresCategories = $sousCategorie->getAutresCategories();
                    } 
                }

                $form->add('sousCategorie', EntityType::class, [
                    'label' => 'Sous Catégorie',
                    'class' => TkiSousCategorie::class,
                    'choice_label' => 'description',
                    'placeholder' => '-- Choisir une sous categorie--',
                    'required' => false,
                    'choices' => $sousCategories,
                    'query_builder' => function(EntityRepository $tkiCategorie) {
                        return $tkiCategorie->createQueryBuilder('sc')->orderBy('sc.description', 'ASC');
                    },
                    'attr' => ['class' => 'sous-categorie']
                ]);

                $form->add('autresCategories', EntityType::class, [
                    'label' => 'Autres Catégories',
                    'class' => TkiAutresCategorie::class,
                    'choice_label' => 'description',
                    'placeholder' => '-- Choisir une autre categorie--',
                    'required' => false,
                    'choices' => $autresCategories,
                    'query_builder' => function(EntityRepository $tkiCategorie) {
                        return $tkiCategorie->createQueryBuilder('ac')->orderBy('ac.description', 'ASC');
                    },
                    'attr' => ['class' => 'autres-categories']
                ]);
            })
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TikSearch::class,
        ]);
    }
}
?>