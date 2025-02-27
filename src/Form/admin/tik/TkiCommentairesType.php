<?php

namespace App\Form\admin\tik;

use App\Entity\admin\tik\TkiCommentaires;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TkiCommentairesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commentaires', TextareaType::class, [
                'label' => false,
                'attr'  => [
                    'row'         => 5,
                    'placeholder' => 'Entrer votre commentaire ici',
                    'minlength'   => '1',
                    'maxlength'   => '1500'
                ]
            ])
            ->add(
                'fileNames',
                FileType::class,
                [
                    'label'       => 'Pièces Jointes',
                    'required'    => false,
                    'multiple'    => true,
                    'data_class'  => null,
                    'mapped'      => false, // Indique que ce champ ne doit pas être lié à l'entité
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
                        $context
                            ->buildViolation($violation->getMessage())
                            ->addViolation();
                    }
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TkiCommentaires::class,
        ]);
    }
}
