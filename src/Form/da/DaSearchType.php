<?php

namespace App\Form\da;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DaSearchType extends  AbstractType
{
    private const STATUT_DA = [
        DemandeAppro::STATUT_VALIDE               => DemandeAppro::STATUT_VALIDE,
        DemandeAppro::STATUT_TERMINER             => DemandeAppro::STATUT_TERMINER,
        DemandeAppro::STATUT_SOUMIS_ATE           => DemandeAppro::STATUT_SOUMIS_ATE,
        DemandeAppro::STATUT_SOUMIS_APPRO         => DemandeAppro::STATUT_SOUMIS_APPRO,
        DemandeAppro::STATUT_AUTORISER_MODIF_ATE  => DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
    ];

    private const STATUT_BC = [
        DaSoumissionBc::STATUT_A_GENERER                    => DaSoumissionBc::STATUT_A_GENERER,
        DaSoumissionBc::STATUT_A_EDITER                     => DaSoumissionBc::STATUT_A_EDITER,
        DaSoumissionBc::STATUT_A_SOUMETTRE_A_VALIDATION     => DaSoumissionBc::STATUT_A_SOUMETTRE_A_VALIDATION,
        DaSoumissionBc::STATUT_A_VALIDER_DA                 => DaSoumissionBc::STATUT_A_VALIDER_DA,
        DaSoumissionBc::STATUT_A_ENVOYER_AU_FOURNISSEUR     => DaSoumissionBc::STATUT_A_ENVOYER_AU_FOURNISSEUR,
        DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR     => DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR
    ];

    private const TYPE_ACHAT = [
        'Tous'     => 'tous',
        'Avec DIT' => 'avec_dit',
        'Direct'   => 'direct',
    ];

    private $agenceRepository;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->agenceRepository = $this->em->getRepository(Agence::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('numDit', TextType::class, [
                'label' => 'n° DIT',
                'required' => false
            ])
            ->add('numDa', TextType::class, [
                'label' => 'n° DAP',
                'required' => false
            ])
            ->add('demandeur', TextType::class, [
                'label' => 'Demandeur',
                'required' => false
            ])
            ->add('statutDA', ChoiceType::class, [
                'placeholder' => '-- Choisir un statut --',
                'label' => 'Statut de la DA',
                'choices'  => self::STATUT_DA,
                'required' => false
            ])
            ->add('statutOR', ChoiceType::class, [
                'placeholder' => '-- Choisir un statut --',
                'label' => 'Statut de l\'OR',
                'choices'  => [],
                'required' => false
            ])
            ->add('statutBC', ChoiceType::class, [
                'placeholder' => '-- Choisir un statut --',
                'label' => 'Statut du BC',
                'choices'  => self::STATUT_BC,
                'required' => false
            ])
            ->add('idMateriel', TextType::class, [
                'label' => "N° Matériel",
                'required' => false
            ])
            ->add('typeAchat', ChoiceType::class, [
                'label' => 'Type de la demande d\'achat',
                'placeholder' => false,
                'choices' => self::TYPE_ACHAT,
                'required' => false
            ])
            ->add('niveauUrgence', EntityType::class, [
                'label' => 'Niveau d\'urgence',
                'label_html' => true,
                'class' => WorNiveauUrgence::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir un niveau--',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.description', 'DESC');
                },
                'attr' => [
                    'class' => 'niveauUrgence'
                ]
            ])
            ->add('dateDebutCreation', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date création (début)',
                'required' => false,
            ])
            ->add('dateFinCreation', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date création (fin)',
                'required' => false,
            ])
            ->add('dateDebutfinSouhaite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin souhaitée (début)',
                'required' => false,
            ])
            ->add('dateFinFinSouhaite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin souhaitée (fin)',
                'required' => false,
            ])
            ->add('agenceEmetteur', EntityType::class, [
                'label' => "Agence émetteur",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence--',
                'required' => false,
                'attr' => ['class' => 'agenceEmetteur']
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
                    'placeholder' => '-- Choisir un service--',
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
                    'placeholder' => '-- Choisir un service--',
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
                'placeholder' => '-- Choisir une agence--',
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
                    'placeholder' => '-- Choisir un service--',
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
                    'placeholder' => '-- Choisir un service--',
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
        $resolver->setDefaults([]);
    }
}
