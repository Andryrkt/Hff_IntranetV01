<?php

namespace App\Form\bl;

use App\Dto\bl\BLSoumissionDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BLSoumissionType extends AbstractType
{
    const TYPE_BL = [
        "BL interne" => 1,
        "Facture - BL (clients)" => 2
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'pieceJoint1',
                FileType::class,
                [
                    'label' => 'Bc à soumettre',
                    'required' => true,
                    'mapped' => false, // Indique que ce champ ne doit pas être lié à l'entité
                    'constraints' => [
                        new Callback([$this, 'validateFiles']),
                    ],
                ]
            )
            ->add(
                'typeBl',
                ChoiceType::class,
                [
                    'label' => 'Type de BL',
                    'required' => true,
                    'choices' => self::TYPE_BL,
                    'placeholder' => " -- choisir le type de BL -- "
                ]
            )
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
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
