<?php

namespace App\Form\magasin\bc;


use App\Model\magasin\bc\BcMagasinDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BcMagasinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroDevis', null, [
                'label' => 'Numéro de devis',
                'attr' => [
                    'readonly' => true,
                ]
            ])
            ->add('numeroBc', TextType::class, [
                'label' => 'Numéro BC client * ',
                'required' => true,
            ])
            ->add('montantBc', TextType::class, [
                'label' => 'Montant BC * ',
                'required' => true,
            ])
            ->add(
                'dateBc',
                DateType::class,
                [
                    'label' => 'Date BC *',
                    'widget' => 'single_text',
                    'html5' => true,
                    //'format' => 'dd/MM/yyyy', 
                ]
            )
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
            ->add(
                'pieceJoint01',
                FileType::class,
                [
                    'label' => 'Upload File',
                    'required' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuiller sélectionner le devis.', // Message d'erreur si le champ est vide
                        ]),
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
                    'label' => 'Pièces Jointes',
                    'required' => false,
                    'multiple' => true,
                    'data_class' => null,
                    'mapped' => true, // Indique que ce champ ne doit pas être lié à l'entité
                    'constraints' => [
                        new Callback([$this, 'validateFiles']),
                    ],
                ]
            )
            ->add('lignes', CollectionType::class, [
                'entry_type' => BcMagasinLigneType::class,
                'entry_options' => ['label' => false],
            ]);
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BcMagasinDto::class,
        ]);
    }
}
