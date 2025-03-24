<?php

namespace App\Form\ddp;

use App\Controller\Controller;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\ddp\DemandePaiement;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DemandePaiementType extends AbstractType
{
    private $agenceRepository;

    public function __construct()
    {
        $this->agenceRepository = Controller::getEntity()->getRepository(Agence::class);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('numeroFournisseur', TextType::class,
                [
                    'label' => 'Fournisseur *',
                    'attr' => [
                        'class' => 'autocomplete',
                        'autocomplete' => 'off',
                    ]
                ])
            ->add('numeroCommande', 
            ChoiceType::class,
            [
                'label'     => 'N° Commande *',
                'choices'   => [],
                'multiple'  => true,
                'expanded'  => false,
            ])
            ->add('numeroFacture',ChoiceType::class,
                [
                    'label' => 'N° Facture *',
                    'choices'   => [],
                    'multiple'  => true,
                    'expanded'  => false,
                    'attr'      => [
                        'disabled' => $options['id_type'] == 1,
                        'data-typeId' => $options['id_type'] 
                    ]
                ])
                ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    $form->add('numeroCommande', 
                    ChoiceType::class,
                    [
                        'label'     => 'N° Commande *',
                        'choices'   => $data['numeroCommande'],
                        'multiple'  => true,
                        'expanded'  => false,
                    ]);
                    $form->add('numeroFacture',ChoiceType::class,
                        [
                            'label' => 'N° Facture *',
                            'choices'   => $data['numeroFacture'],
                            'multiple'  => true,
                            'expanded'  => false
                        ]);
                })
            ->add('beneficiaire', TextType::class,
                [
                    'label' => 'Bénéficiaire *',
                    'attr' => [
                        'class' => 'autocomplete',
                        'autocomplete' => 'off',
                    ]
                ])
            ->add('motif', TextType::class,
                [
                    'label' => 'Motif',
                    'required' => false
                ])
                
            ->add('ribFournisseur', 
                TextType::class,
                [
                    'label' => 'RIB *'
                ])
            ->add('contact', 
                TextType::class,
                [
                    'label' => 'Contact',
                    'required' => false
                ])
            ->add('modePaiement', TextType::class,
            [
                'label' => 'Mode de paiement *'
            ])
            ->add('devise', TextType::class,
            [
                'label' => 'Devise *'
            ])
            ->add('montantAPayer', TextType::class,
            [
                'label' => 'Montant à payer *'
            ])
            ->add('pieceJoint01',
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
            ])

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
                        //'data' => $options['data']->getService(),
                        'attr' => ['class' => 'serviceDebiteur']
                    ]
                );
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();


                $agenceId = $data['agence'];

                $agence = $this->agenceRepository->find($agenceId);
                if($agence === null){
                    $services = [];
                } else {
                    $services = $agence->getServices();
                }
                

                $form->add('service', EntityType::class, [
                    'label' => 'Service Débiteur *',
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'choices' => $services,
                    'required' => false,
                    'attr' => [
                        'class' => 'serviceDebiteur',
                        'disabled' => false,
                    ]
                ]);
            })
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
                    //'data' => $options["data"]->getAgence() ?? null,
                    'query_builder' => function (AgenceRepository $agenceRepository) {
                        return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
                    },
                    'attr' => ['class' => 'agenceDebiteur']
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