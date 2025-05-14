<?php

namespace App\Form\ddp;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\ddp\DemandePaiement;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use Symfony\Component\Form\AbstractType;
use App\Repository\admin\AgenceRepository;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Repository\admin\ddp\TypeDemandeRepository;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DdpSearchType extends AbstractType
{
    private $agenceRepository;
    private $serviceRepository;
    public function __construct()
    {
        
        $this->agenceRepository = Controller::getEntity()->getRepository(Agence::class);
        $this->serviceRepository = Controller::getEntity()->getRepository(Service::class);
  
     
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('Agence', EntityType::class, [
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

            if ($data && $data->getAgence()) {
                $services = $data->getAgence()->getServices();
            } else {
                $services = [];
            }

            $form->add('service', EntityType::class, [
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

            if (isset($data['Agence']) && $data['Agence']) {
                $agenceId = $data['Agence'];
                $agence = $this->agenceRepository->find($agenceId);

                if ($agence) {
                    $services = $agence->getServices();
                } else {
                    $services = [];
                }
            } else {
                $services = [];
            }

            $form->add('service', EntityType::class, [
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
        ->add('typeDemande', EntityType::class, [
            'label' => 'Type de Document',
            'class' => TypeDemande::class,
            'choice_label' => 'libelle',
            'placeholder' => '-- Choisir un type de demande--',
            'required' => false,
        ])
        ->add(
            'NumDdp',
            TextType::class,
            [
                'label' => 'N° demande',
                'required' => false
            ]
        )
        ->add(
            'numCommande',
            TextType::class,
            [
                'label' => 'N° Commande',
                'required' => false
            ]
        )
        ->add(
            'numFacture',
            TextType::class,
            [
                'label' => 'N° facture',
                'required' => false
            ]
        )
        ->add(
            'utilisateur',
            TextType::class,
            [
                'label' => 'Utilisateur',
                'required' => false
            ]
        )

            
            ;
    }
}
