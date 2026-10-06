<?php

namespace App\Form\da;

use App\Entity\da\DemandeApproLR;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class DemandeApproLRFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroLigne', TextType::class,  [
                'label' => false,
                'required' => false,
            ])
            ->add('numeroFournisseur', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('nomFournisseur', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('artRefp', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('artDesi', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('qteDispo', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('prixUnitaire', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('total', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('conditionnement', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('motif', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('artFams1', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('artFams2', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('numLigneTableau', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('nomFicheTechnique', FileType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf'
                        ],
                        'mimeTypesMessage' => 'Veuillez envoyer un fichier valide PDF',
                    ])
                ],
            ])
            ->add(
                'fileNames',
                FileType::class,
                [
                    'label'      => false,
                    'required'   => false,
                    'multiple'   => true,
                    'data_class' => null,
                    // 'mapped'     => false, // Indique que ce champ ne doit pas être lié à l'entité
                    'constraints' => [
                        new All([
                            'constraints' => [
                                new File([
                                    'maxSize' => '5M',
                                    'mimeTypes' => [
                                        'application/pdf',
                                        'image/*',
                                    ],
                                    'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (PDF, image).',
                                ])
                            ]
                        ])
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeApproLR::class,
        ]);
    }
}
