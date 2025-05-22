<?php

namespace App\Form\da;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DaSearchType extends  AbstractType
{
    private $agenceRepository;

    private $em;

    public function __construct()
    {
        $this->em = Controller::getEntity();
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
                'label' => 'n° DAL',
                'required' => false
            ])
            ->add('demandeur', TextType::class, [
                'label' => 'Demandeur',
                'required' => false
            ])
            ->add('statut', TextType::class, [
                'label' => 'Statut',
                'required' => false
            ])
            ->add('idMateriel', TextType::class, [
                'label' => "N° Matériel",
                'required' => false
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
