<?php

namespace App\Form\tik;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use Symfony\Component\Form\FormEvent;
use App\Entity\admin\tik\TkiCategorie;
use Symfony\Component\Form\FormEvents;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\ServiceRepository;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
            ->add(
                'agenceEmetteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Agence Emetteur *',
                    'required' => false,
                    'attr' => [
                        'readonly' => true
                    ],
                    'data' => $options["data"] instanceof DemandeSupportInformatique ? $options["data"]->getAgenceEmetteur() : null
                ]
            )

            ->add(
                'serviceEmetteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Service Emetteur *',
                    'required' => false,
                    'attr' => [
                        'readonly' => true,
                        'disable' => true
                    ],
                    'data' => $options["data"] instanceof DemandeSupportInformatique ? $options["data"]->getServiceEmetteur() : null
                ]
            )


            ->add('dateFinSouhaitee', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin souhaitee *',
                'required' => true,
            ])
            ->add(
                'objetDemande',
                TextType::class,
                [
                    'label' => 'Objet de la demande *',
                    'required' => true,
                    'attr' => ['class' => 'noEntrer'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'l\'objet de la demande ne peut pas être vide .', // Message d'erreur si le champ est vide
                        ]),
                    ],
                ]
            )
            ->add(
                'detailDemande',
                TextareaType::class,
                [
                    'label' => 'Détail de la demande *',
                    'required' => true,
                    'attr' => [
                        'rows' => 5,
                        'class' => 'detailDemande',
                        'placeholder' => 'Veuillez décrire les détails de votre demande ici...', // Texte indicatif
                        'maxlength' => 5000, // Limite de caractères
                        'data-toggle' => 'tooltip', // Activer un tooltip (si Bootstrap est utilisé)
                        'title' => 'Indiquez tous les détails pertinents de la demande' // Texte d’aide pour le tooltip 
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'le detail de la demande ne peut pas être vide .', // Message d'erreur si le champ est vide
                        ]),
                        new Length([
                            'max' => 500,
                            'maxMessage' => 'Le détail de la demande ne peut pas dépasser {{ limit }} caractères.',
                        ])
                    ],
                ]
            )

            ->add(
                'fileNames',
                FileType::class,
                [
                    'label' => 'Pièces Jointes',
                    'required' => false,
                    'multiple' => true,
                    'data_class' => null,
                    'mapped' => false, // Indique que ce champ ne doit pas être lié à l'entité
                    'constraints' => [
                        new Callback([$this, 'validateFiles']),
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
                'label' => 'Parc informatique ',
                'required' => false,
            ])
            ->add('codeSociete', TextType::class, [
                'label' => 'Code Société',
                'disabled' => true,
                'data' => $options['data']->getCodeSociete()
            ])
            ->add(
                'agence',
                EntityType::class,
                [
                    'label' => 'Agence Débiteur *',
                    'placeholder' => '-- Choisir une agence Debiteur --',
                    'class' => Agence::class,
                    'choice_label' => function (Agence $agence): string {
                        return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                    },
                    'required' => false,
                    'data' => $options["data"]->getAgence() ?? null,
                    'query_builder' => function (AgenceRepository $agenceRepository) {
                        return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
                    },
                    'attr' => ['class' => 'agenceDebiteur']
                ]
            )


        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();
            $services = null;

            if ($data instanceof DemandeIntervention && $data->getAgence()) {
                $services = $data->getAgence()->getServices();
            }

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
                    'data' => $options['data']->getService(),
                    'attr' => ['class' => 'serviceDebiteur']
                ]
            );
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
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
        });
    }

    public function validateFiles($files, ExecutionContextInterface $context)
    {
        $maxSize = '5M';
        $mimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        if ($files) {
            foreach ($files as $file) {
                $fileConstraint = new File([
                    'maxSize' => $maxSize,
                    'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser 5 Mo.',
                    'mimeTypes' => $mimeTypes,
                    'mimeTypesMessage' => 'Veuillez télécharger un fichier valide.',
                ]);

                $violations = $context->getValidator()->validate($file, $fileConstraint);

                if (count($violations) > 0) {
                    foreach ($violations as $violation) {
                        $context->buildViolation($violation->getMessage())
                            ->addViolation();
                    }
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeSupportInformatique::class,
        ]);
    }
}
