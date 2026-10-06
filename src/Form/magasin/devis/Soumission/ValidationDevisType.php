<?php

namespace App\Form\magasin\devis\Soumission;

use App\Dto\Magasin\Devis\Soumission\SoumissionDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ValidationDevisType extends AbstractType
{
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
                    'attr'          => [
                        'data-error-message' => 'veillez ajouter un fichier PDF.', // Message d'erreur personnalis
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => SoumissionDto::class,
                "fichier_initialise" => false
            ]);
    }
}
