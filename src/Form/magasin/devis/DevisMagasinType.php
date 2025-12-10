<?php

namespace App\Form\magasin\devis;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DevisMagasinType extends AbstractType
{
    private const TACHE_VALIDATEUR = [
        'Vérification prix'                        => 'Vérification prix',
        'Insertion remise'                         => 'Insertion remise',
        'Verification de prix et insertion remise' => 'Verification de prix et insertion remise',
        'Modification entête'                      => 'Modification entête',
        'Modification statut'                      => 'Modification statut',
        'Modification tarif (type AMSA/COLAS)'     => 'Modification tarif (type AMSA/COLAS)',
        'Insertition ligne transport'              => 'Insertition ligne transport'
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fichier_initialise = $options['fichier_initialise'];
        $PJ1constraints = [];

        if (!$fichier_initialise) {
            $PJ1constraints[] = new NotBlank([
                'message' => 'Veuiller sélectionner le devis.', // Message d'erreur si le champ est vide
            ]);
        }

        $builder
            ->add('numeroDevis', null, [
                'label' => 'Numéro de devis',
                'attr'  => [
                    'readonly' => true,
                ]
            ])
            ->add(
                'pieceJoint01',
                FileType::class,
                [
                    'label'         => 'Upload File',
                    'required'      => !$fichier_initialise,
                    'constraints'   => [
                        ...$PJ1constraints,
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'Le fichier ne doit pas dépasser 5 Mo.', // Message personnalisé pour la taille
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide.',
                        ])
                    ],
                ]
            )
            ->add(
                'pieceJoint2',
                FileType::class,
                [
                    'label'         => 'Pièces Jointes',
                    'required'      => false,
                    'multiple'      => true,
                    'data_class'    => null,
                    'mapped'        => true, // Indique que ce champ ne doit pas être lié à l'entité
                    'constraints'   => [
                        new Callback([$this, 'validateFiles']),
                    ],
                ]
            )
            ->add(
                'pieceJointExcel',
                FileType::class,
                [
                    'label'         => 'Fichier Excel',
                    'required'      => false,
                    'constraints'   => [
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'Le fichier ne doit pas dépasser 5 Mo.', // Message personnalisé pour la taille
                            'mimeTypes' => [
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger un fichier Excel valide.',
                        ])
                    ],
                ]
            )
            ->add('tacheValidateur', ChoiceType::class, [
                'label' => 'Tâche du validateur',
                'choices' => self::TACHE_VALIDATEUR,
                'data' => ['Vérification prix'],
                'expanded' => true,
                'multiple' => true,
                'disabled' => $options['data']->constructeur == 'TOUS NEST PAS CAT' ? false : true,
            ])
            ->add('estValidationPm', ChoiceType::class, [
                'choices'       => [
                    'OUI' => true,
                    'NON' => false
                ],
                'expanded'      => true,
                'multiple'      => false,
                'label'         => 'Envoyer à validation au PM',
                'data'          => $options['data']->constructeur == 'TOUS NEST PAS CAT' ? true : false,
                'disabled'      => $options['data']->constructeur == 'TOUS NEST PAS CAT' ? true : false
            ])
            ->add(
                'observation',
                TextareaType::class,
                [
                    'label' => 'Observation',
                    'required' => false,
                    'attr' => [
                        'rows' => 5,
                    ],
                ]
            )
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $devisMagasin = $event->getData();
                $form = $event->getForm();
                $form->add('tacheValidateur', ChoiceType::class, [
                    'label' => 'Tâche du validateur',
                    'choices' => self::TACHE_VALIDATEUR,
                    'data' => isset($devisMagasin['tacheValidateur']) ? $devisMagasin['tacheValidateur'] : ['Vérification prix'],
                    'expanded' => true,
                    'multiple' => true,
                    'disabled' => false
                ]);
            })
        ;
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
        $resolver
            ->setDefaults([])
            ->setDefined("fichier_initialise");
    }
}
