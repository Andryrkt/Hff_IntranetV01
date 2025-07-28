<?php

namespace App\Form\dit;

use App\Entity\dit\DitCdeSoumisAValidation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class DitCdeSoumisAValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'pieceJoint01',
                FileType::class,
                [
                    'label' => 'Upload File',
                    'required' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuiller sélectionner le RI à soumettre .', // Message d'erreur si le champ est vide
                        ]),
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser 5 Mo.',
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide.',
                        ]),
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DitCdeSoumisAValidation::class,
        ]);
    }
}
