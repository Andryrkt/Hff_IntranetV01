<?php

namespace App\Form\tik;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\tik\TkiCategorie;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\ServiceRepository;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DemandeSupportInformatiqueType extends AbstractType
{
    private $agenceRepository;

    public function __construct()
   {
        $this->agenceRepository = Controller::getEntity()->getRepository(Agence::class);
   }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('agence', 
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
            'query_builder' => function(AgenceRepository $agenceRepository) {
                    return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
                },
                'attr' => [ 'class' => 'agenceDebiteur']
        ])
        ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options){
            $form = $event->getForm();
            $data = $event->getData();
            $services = null;

            if ($data instanceof DemandeIntervention && $data->getAgence()) {
                $services = $data->getAgence()->getServices();
            }
            //$services = $data->getAgence()->getServices();
            // $agence = $event->getData()->getAgence() ?? null;
            // $services = $agence->getServices();
    
            $form->add('service',
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
            'query_builder' => function(ServiceRepository $serviceRepository) {
                    return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                },
            //'data' => $options['data']->getService(),
                'attr' => [ 'class' => 'serviceDebiteur',
                'disabled' => true,]
            ]);
            
        })
        ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event)  {
            $form = $event->getForm();
            $data = $event->getData();
        
            
                $agenceId = $data['agence'];
                
                $agence = $this->agenceRepository->find($agenceId);
                $services = $agence->getServices();
                
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
        
        ->add('agenceEmetteur', 
        TextType::class,
        [
        'mapped' => false,
                    'label' => 'Agence *',
                    'required' => false,
                    'attr' => [
                        'readonly' => true
                    ],
                    'data' =>  null
        ])
    
        ->add('serviceEmetteur', 
        TextType::class,
        [
            'mapped' => false,
                    'label' => 'Service *',
                    'required' => false,
                    'attr' => [
                        'readonly' => true,
                        'disable' => true
                    ],
                    'data' =>  null
        ])
        
        
        ->add('dateFinSouhaitee', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date fin souhaitee *',
            'required' => true,
        ])
        ->add('objetDemande',
        TextType::class,
        [
            'label' => 'Objet de la demande *',
            'required' => true,
            'attr' => [ 'class' => 'noEntrer'],
            'constraints' => [
                new NotBlank([
                    'message' => 'l\'objet de la demande ne peut pas être vide .', // Message d'erreur si le champ est vide
                ]),
            ],
        ])
        ->add('detailDemande',
        TextareaType::class,
        [
            'label' => 'Détail de la demande *',
            'required' => true,
            'attr' => [
                'rows' => 5,
                'class' => 'detailDemande'  
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'le detail de la demande ne peut pas être vide .', // Message d'erreur si le champ est vide
                ]),
            ],
        ])
        ->add('pieceJoint03',
            FileType::class, 
            [
                'label' => 'Pièce Jointe 03',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid  file.',
                    ])
                ],
            ])
            ->add('pieceJoint02',
            FileType::class, 
            [
                'label' => 'Pièce Jointe 02',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid file.',
                    ])
                ],
            ]
            )
            ->add('pieceJoint01',
            FileType::class, 
            [
                'label' => 'Pièce Jointe 01',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid file.',
                    ])
                ],
            ]
            )

            ->add('categorie', EntityType::class, [
                'label' => 'Catégorie *',
                'placeholder' => ' -- Choisir une catégorie',
                'class' => TkiCategorie::class,
                'choice_label' => 'description'
            ])
            ->add('parcInformatique', TextType::class, [
                'label' => 'Parc informatique *'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeSupportInformatique::class,
        ]);
    }
}
?>