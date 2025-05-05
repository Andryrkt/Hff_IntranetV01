<?php

namespace App\Form\ddp;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\ddp\DemandePaiement;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use Symfony\Component\Form\AbstractType;
use App\Repository\admin\AgenceRepository;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DemandePaiementType extends AbstractType
{
    private $agenceRepository;
    private $serviceRepository;
    private $cdeFnrRepository;
    private $demandePaiementModel;
    private $em;
    public function __construct()
    {
        $this->em=Controller::getEntity();
        $this->agenceRepository = Controller::getEntity()->getRepository(Agence::class);
        $this->serviceRepository = Controller::getEntity()->getRepository(Service::class);
        $this->cdeFnrRepository = $this->em->getRepository(CdefnrSoumisAValidation::class);
        $this->demandePaiementModel = new DemandePaiementModel();
     
    }
    private function numeroFac($numeroFournisseur){
        $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur);
    
        $numCde = array_map(fn($el) => ['label' => $el, 'value' => $el], $numCdes);
        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
    
        $listeGcot = $this->demandePaiementModel->finListFacGcot($numeroFournisseur, $numCdesString);
            return array_combine($listeGcot, $listeGcot);
    }
    private function numeroCmd($numeroFournisseur){
        $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur);
        return array_combine($numCdes, $numCdes);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        $builder
            ->add(
                'numeroFournisseur',
                TextType::class,
                [
                    'label' => 'Fournisseur *',
                    'attr' => [
                        'class' => 'autocomplete',
                        'autocomplete' => 'off',
                    ]
                ]
            )
            ->add(
                'numeroCommande',
                ChoiceType::class,
                [
                    'label'     => 'N° Commande *',
                    'choices'   =>  array_key_exists('data',$options) ? $this->numeroCmd($options['data']->getNumeroFournisseur()): [],
                    'multiple'  => true,
                    'expanded'  => false,
                ]
            )
            ->add(
                'numeroFacture',
                ChoiceType::class,
                [
                    'label' => 'N° Facture *',
                    'required' => false,
                    'choices'   => array_key_exists('data',$options) ? $this->numeroFac($options['data']->getNumeroFournisseur()): [],
                    'multiple'  => true,
                    'expanded'  => false,
                    'attr'      => [
                        'disabled' => $options['id_type'] == 1,
                        'data-typeId' => $options['id_type']
                    ]
                ]
            )
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $form->add(
                    'numeroCommande',
                    ChoiceType::class,
                    [
                        'label'     => 'N° Commande *',
                        'choices'   => $data['numeroCommande'],
                        'multiple'  => true,
                        'expanded'  => false,
                    ]
                );
                $form->add(
                    'numeroFacture',
                    ChoiceType::class,
                    [
                        'label' => 'N° Facture *',
                        'choices'   => $data['numeroFacture'] ?? [],
                        'multiple'  => true,
                        'expanded'  => false,
                        'required' => false
                    ]
                );
            })
            ->add(
                'beneficiaire',
                TextType::class,
                [
                    'label' => 'Bénéficiaire *',
                    'attr' => [
                        'class' => 'autocomplete',
                        'autocomplete' => 'off',
                    ]
                ]
            )
            ->add(
                'motif',
                TextType::class,
                [
                    'label' => 'Motif',
                    'required' => false
                ]
            )

            ->add(
                'ribFournisseur',
                TextType::class,
                [
                    'label' => 'RIB *',
                    'attr' => [
                        'readOnly' => true
                    ]
                ]
            )
            ->add(
                'contact',
                TextType::class,
                [
                    'label' => 'Contact',
                    'required' => false
                ]
            )
            ->add(
                'modePaiement',
                TextType::class,
                [
                    'label' => 'Mode de paiement *',
                    'attr' => [
                        'readOnly' => true
                    ]
                ]
            )
            ->add(
                'devise',
                TextType::class,
                [
                    'label' => 'Devise *',
                    'attr' => [
                        'readOnly' => true
                    ]
                ]
            )
            ->add(
                'montantAPayer',
                TextType::class,
                [
                    'label' => 'Montant à payer *'
                ]
            )
            ->add(
                'pieceJoint01',
                FileType::class,
                [
                    'label' => 'Pièce Jointe 01 (PDF)',
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'application/pdf',
                                // 'image/jpeg',
                                // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                // 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid PDF file.',
                        ])
                    ],
                ]
            )

            ->add(
                'pieceJoint02',
                FileType::class,
                [
                    'label' => 'Pièce Jointe 02 (PDF)',
                    'required' => $options['id_type'] == 2,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'application/pdf',
                                // 'image/jpeg',
                                // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                // 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid PDF file.',
                        ])
                    ],
                ]
            )

            ->add(
                'pieceJoint03',
                FileType::class,
                [
                    'label' => 'Pièce Jointe 03 (PDF)',
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'application/pdf',
                                // 'image/jpeg',
                                // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                // 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid PDF file.',
                        ])
                    ],
                ]
            )

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();
                $services = null;

                if ($data instanceof DemandePaiement && $data->getAgence()) {
                    $services = $data->getAgence()->getServices();
                }
                //$services = $data->getAgence()->getServices();
                // $agence = $event->getData()->getAgence() ?? null;
                // $services = $agence->getServices();

                $form->add(
                    'service',
                    EntityType::class,
                    [
                        'label' => 'Service Débiteur *',
                        'class' => Service::class,
                        'choice_label' => function (Service $service): string {
                            return $service->getCodeService() . ' ' . $service->getLibelleService();
                        },
                        'choices' => $services,
                        // 'disabled' => $agence === null,
                        'required' => false,
                        'query_builder' => function (ServiceRepository $serviceRepository) {
                            return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                        },
                        'data' => $this->serviceRepository->find(1),
                        'attr' => [
                            'class' => 'serviceDebiteur',
                            'disabled' => true
                        ]
                    ]
                );
            })
            // ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            //     $form = $event->getForm();
            //     $data = $event->getData();


            //     $agenceId = $data['agence'];

            //     $agence = $this->agenceRepository->find($agenceId);
            //     if($agence === null){
            //         $services = [];
            //     } else {
            //         $services = $agence->getServices();
            //     }


            //     $form->add('service', EntityType::class, [
            //         'label' => 'Service Débiteur *',
            //         'class' => Service::class,
            //         'choice_label' => function (Service $service): string {
            //             return $service->getCodeService() . ' ' . $service->getLibelleService();
            //         },
            //         'choices' => $services,
            //         'required' => false,
            //         'attr' => [
            //             'class' => 'serviceDebiteur',
            //             'disabled' => true,
            //         ]
            //     ]);
            // })
            ->add(
                'agence',
                EntityType::class,
                [

                    'label' => 'Agence Debiteur *',
                    'placeholder' => '-- Choisir une agence Debiteur --',
                    'class' => Agence::class,
                    'choice_label' => function (Agence $agence): string {
                        return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                    },
                    'required' => false,
                    'data' => $this->agenceRepository->find(1),
                    'query_builder' => function (AgenceRepository $agenceRepository) {
                        return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
                    },

                    'attr' => [
                        'class' => 'agenceDebiteur',
                        'disabled' => true
                    ]
                ]
            )

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandePaiement::class,
        ]);

        // Ajoutez l'option 'id_type' pour éviter l'erreur
        $resolver->setDefined('id_type');
    }
}
