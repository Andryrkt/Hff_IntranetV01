<?php

namespace App\Form\da;

use App\Entity\da\DemandeApproL;
use App\Model\da\DaModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class DemandeApproLFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $daModel = new DaModel;

        $builder
            ->add('codeFams1', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'placeholder' => '-- Choisir une famille --',
                'choices' => $daModel->getAllFamille(),
            ])
            ->add('codeFams2', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'placeholder' => '-- Choisir une sous-famille --',
                'choices' => $daModel->getAllSousFamille()
            ])
            ->add('artDesi', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'autocomplete',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('dateFinSouhaite', DateType::class, [
                'label' => false,
                'required' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'la date ne doit pas être vide'])
                ]
            ])
            ->add('qteDem', TextType::class,  [
                'label' => false,
                'required' => false,
            ])
            ->add('commentaire', TextType::class, [
                'label' => false,
                'required' => false,
                'empty_data' => ''
            ])
            ->add('artConstp', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('artRefp', TextType::class, [
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
            ->add('numeroFournisseur', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('nomFournisseur', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'autocomplete',
                    'autocomplete' => 'off',
                    'readonly' => true,
                ]
            ])
            ->add('estFicheTechnique', CheckboxType::class, [
                'required' => false,
                'label'    => false,
            ])
            ->add('catalogue', CheckboxType::class, [
                'required' => false,
                'label'    => false,
            ])
            ->add('deleted', CheckboxType::class, [
                'required' => false,
                'label'    => false,
            ])
            ->add('numeroLigne', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add(
                'fileNames',
                FileType::class,
                [
                    'label'      => false,
                    'required'   => false,
                    'multiple'   => true,
                    'data_class' => null,
                    'mapped'     => false, // Indique que ce champ ne doit pas être lié à l'entité
                    'constraints' => [
                        new All([
                            'constraints' => [
                                new File([
                                    'maxSize' => '5M',
                                    'mimeTypes' => [
                                        'application/pdf',
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
            'data_class' => DemandeApproL::class,
        ]);
    }
}
