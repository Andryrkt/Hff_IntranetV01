<?php

namespace App\Form\da\daCdeFrn;


use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\FormatageTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDdpaDto;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DaSoumissionFacBlDdpaType extends AbstractType
{
    use FormatageTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroCde', TextType::class, [
                'label' => 'Numéro Commande',
                'attr'  => [
                    'class' => 'div-disabled',
                ]
            ])
            ->add('totalMontantCommande', TextType::class, [
                'label' => 'Total commande',
                'attr'  => [
                    'class' => 'div-disabled',
                ],
                'data' => $this->formatNumberGeneral($options['data']->totalMontantCommande)
            ])
            ->add(
                'pieceJoint1',
                FileType::class,
                [
                    'label' => 'FacBl à soumettre',
                    'attr' => ['data-field-name' => 'Pièce Jointe Facture / BL'],
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
                        ])
                    ],
                ]
            )
            ->add(
                'pieceJoint2',
                FileType::class,
                [
                    'label'       => 'Pièces Jointes',
                    'required'    => false,
                    'multiple'    => true,
                    'data_class'  => null,
                    'mapped'      => true,
                    'constraints' => [
                        new Callback([$this, 'validateFiles']),
                    ],
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
            'data_class' => DaSoumissionFacBlDdpaDto::class,
        ]);
    }
}
