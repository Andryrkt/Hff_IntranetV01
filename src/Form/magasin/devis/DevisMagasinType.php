<?php

namespace App\Form\magasin\devis;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class DevisMagasinType extends AbstractType
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
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
