<?php

namespace App\Form\cde;

use App\Entity\cde\CdefnrSoumisAValidation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CdeFnrSoumisAValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codeFournisseur', TextType::class, [
                'label' => 'Numéro fournisseur *',
                'required' => true,
                'attr' => [
                    'class' => 'autocomplete',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('libelleFournisseur', TextType::class, [
                'label' => 'Nom fournisseur *',
                'required' => true,
                'attr' => [
                    'class' => 'autocomplete',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('numCdeFournisseur', TextType::class, [
                'label' => 'Numéro commande *',
                'required' => true,
                'attr' => [
                    'class' => 'autocomplete',
                    'autocomplete' => 'off',
                ],
            ])
            ->add(
                'pieceJoint01',
                FileType::class,
                [
                    'label' => 'Bon de commande (PDF) *',
                    'required' => true,
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
                        ]),
                    ],
            ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CdefnrSoumisAValidation::class,
        ]);
    }
}
