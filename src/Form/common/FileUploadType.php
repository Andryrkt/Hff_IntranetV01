<?php

namespace App\Form\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FileUploadType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Pièces Jointes',
            'required' => false,
            'multiple' => false, // Par défaut multiple, mais peut être changé
            'data_class' => null,
            'mapped' => true,
            'max_size' => '5M',
            'allowed_mime_types' => [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/msword',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ],
            'accept' => null, // Attribut HTML accept pour filtrer dans le sélecteur de fichiers
            'constraints' => [],
        ]);

        // Permet de définir les types autorisés pour ces options
        $resolver->setAllowedTypes('multiple', 'bool');
        $resolver->setAllowedTypes('max_size', 'string');
        $resolver->setAllowedTypes('allowed_mime_types', 'array');
        $resolver->setAllowedTypes('accept', ['null', 'string']);
        $resolver->setAllowedTypes('required', 'bool');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxSize = $options['max_size'];
        $mimeTypes = $options['allowed_mime_types'];
        $isMultiple = $options['multiple'];

        // Ajouter automatiquement la contrainte de validation
        $options['constraints'][] = new Callback(function ($files, ExecutionContextInterface $context) use ($maxSize, $mimeTypes, $isMultiple) {
            // Si le champ n'est pas multiple, on convertit en tableau pour la validation
            $filesToValidate = $isMultiple ? $files : ($files ? [$files] : []);

            if ($filesToValidate) {
                foreach ($filesToValidate as $file) {
                    $fileConstraint = new File([
                        'maxSize' => $maxSize,
                        'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser ' . $maxSize . '.',
                        'mimeTypes' => $mimeTypes,
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (PDF, images, documents Office).',
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
        });
    }

    public function getParent(): string
    {
        return FileType::class;
    }
}
