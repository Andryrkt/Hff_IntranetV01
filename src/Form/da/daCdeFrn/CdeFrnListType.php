<?php

namespace App\Form\da\daCdeFrn;


use App\Constants\da\StatutBcConstant;
use App\Entity\admin\Agence;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\Service;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeAppro;
use App\Factory\da\CdeFrnDto\CdeFrnSearchDto;
use App\Repository\admin\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CdeFrnListType extends  AbstractType
{
    private $em;
    private $agenceRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->agenceRepository = $this->em->getRepository(Agence::class);
    }

    private const STATUT_DA = [
        DemandeAppro::STATUT_EN_COURS_CREATION    => DemandeAppro::STATUT_EN_COURS_CREATION,
        DemandeAppro::STATUT_SOUMIS_APPRO         => DemandeAppro::STATUT_SOUMIS_APPRO, // demande d'achat
        DemandeAppro::STATUT_DEMANDE_DEVIS        => DemandeAppro::STATUT_DEMANDE_DEVIS,
        DemandeAppro::STATUT_DEVIS_A_RELANCER     => DemandeAppro::STATUT_DEVIS_A_RELANCER,
        DemandeAppro::STATUT_AUTORISER_EMETTEUR   => DemandeAppro::STATUT_AUTORISER_EMETTEUR, // Création demande initiale
        DemandeAppro::STATUT_EN_COURS_PROPOSITION => DemandeAppro::STATUT_EN_COURS_PROPOSITION,
        DemandeAppro::STATUT_SOUMIS_ATE           => DemandeAppro::STATUT_SOUMIS_ATE, // proposition d'achat
        DemandeAppro::STATUT_VALIDE               => DemandeAppro::STATUT_VALIDE, // Bon d'achats validé
        DemandeAppro::STATUT_CLOTUREE             => DemandeAppro::STATUT_CLOTUREE,
        DemandeAppro::STATUT_CLOTUREE_HORS_DELAI  => DemandeAppro::STATUT_CLOTUREE_HORS_DELAI,
    ];


    private const TYPE_ACHAT = [
        'DA Avec DIT' => DemandeAppro::TYPE_DA_AVEC_DIT,
        'DA Direct'   => DemandeAppro::TYPE_DA_DIRECT,
        'DA reappro'  => DemandeAppro::TYPE_DA_REAPPRO_MENSUEL,
    ];

    private const TRI_NBR_JOURS =  [
        'Ordre croissant'   => 'asc',
        'Ordre décroissant' => 'desc',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statut_da = self::STATUT_DA;
        // ksort($statut_da);

        $builder
            ->add('afficherCloturees', CheckboxType::class, [
                'label'    => 'Afficher aussi les demandes d\'approvisionnement clôturées',
                'required' => false
            ])
            ->add('numDa', TextType::class, [
                'label'    => 'N° DA',
                'required' => false
            ])
            ->add('typeAchat', ChoiceType::class, [
                'label'       => 'Type de la demande d\'achat',
                'placeholder' => '-- Choisir le type de la DA --',
                'choices'     => self::TYPE_ACHAT,
                'required'    => false
            ])
            ->add('numDit', TextType::class, [
                'label' => 'N° OR/DIT',
                'required' => false
            ])
            ->add('numFrn', TextType::class, [
                'label' => 'N° Fournisseur',
                'required' => false
            ])
            ->add('frn', TextType::class, [
                'label' => 'Fournisseur',
                'required' => false
            ])
            ->add('numCde', TextType::class, [
                'label' => 'N° BC',
                'required' => false
            ])
            ->add('ref', TextType::class, [
                'label' => 'Réference',
                'required' => false
            ])
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
                'required' => false
            ])
            ->add('niveauUrgence', EntityType::class, [
                'label'         => 'Niveau d\'urgence',
                'label_html'    => true,
                'class'         => WorNiveauUrgence::class,
                'choice_label'  => 'description',
                'choice_value'  => 'description',
                'placeholder'   => '-- Choisir le niveau d\'urgence--',
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.description', 'DESC');
                },
                'attr'          => [
                    'class' => 'niveauUrgence'
                ]
            ])
            ->add(
                'statutBc',
                ChoiceType::class,
                [
                    'label' => "Statut BC",
                    'choices' => StatutBcConstant::STATUT_BC,
                    'placeholder' => '-- Choisir la statut --',
                    'required' => false,
                ]
            )
            ->add('statutDA', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut de la DA',
                'choices'       => $statut_da,
                'required'      => false
            ])
            ->add('dateDebutOR', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début planning OR',
                'required' => false,
            ])
            ->add('dateFinOR', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin planning OR',
                'required' => false,
            ])
            ->add('dateDebutfinSouhaite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début fin souhaité',
                'required' => false,
            ])
            ->add('dateFinFinSouhaite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin fin souhaité',
                'required' => false,
            ])
            ->add('sortNbJours', ChoiceType::class, [
                'placeholder'   => '-- Choisir un tri --',
                'label'         => 'Tri par Nbr Jour(s)',
                'choices'       => self::TRI_NBR_JOURS,
                'required'      => false
            ])
            ->add('demandeur', TextType::class, [
                'label'         => 'Demandeur',
                'required'      => false
            ])
            ->add('agenceEmetteur', EntityType::class, [
                'label'         => "Agence émetteur",
                'class'         => Agence::class,
                'choice_label'  => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder'   => '-- Choisir une agence --',
                'required'      => false,
                'attr'          => ['class' => 'agenceEmetteur']
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data && $data->getAgenceEmetteur()) {
                    $services = $data->getAgenceEmetteur()->getServices();
                } else {
                    $services = [];
                }

                $form->add('serviceEmetteur', EntityType::class, [
                    'label' => "Service émetteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir un service --',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceEmetteur']
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $services = [];
                if (isset($data['agenceEmetteur']) && $data['agenceEmetteur']) {
                    $agenceId = $data['agenceEmetteur'];
                    $agence = $this->agenceRepository->find($agenceId);

                    if ($agence) {
                        $services = $agence->getServices();
                    }
                }

                $form->add('serviceEmetteur', EntityType::class, [
                    'label' => "Service Emetteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir un service --',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceEmetteur']
                ]);
            })
            ->add('agenceDebiteur', EntityType::class, [
                'label' => "Agence débiteur",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence --',
                'required' => false,
                'attr' => ['class' => 'agenceDebiteur']
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data && $data->getAgenceDebiteur()) {
                    $services = $data->getAgenceDebiteur()->getServices();
                } else {
                    $services = [];
                }

                $form->add('serviceDebiteur', EntityType::class, [
                    'label' => "Service débiteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir un service --',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceDebiteur']
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
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
                    'label' => "Service débiteur",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir un service --',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceDebiteur']
                ]);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CdeFrnSearchDto::class,
            'em' => null,
        ]);
    }
}
