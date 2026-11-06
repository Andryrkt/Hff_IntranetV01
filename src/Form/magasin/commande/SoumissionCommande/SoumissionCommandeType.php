<?php

namespace App\Form\magasin\Commande\SoumissionCommande;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class SoumissionCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numCmde', TextType::class, [
                'label' => 'Nume de commande * :',
                'required' => false,
            ])
            ->add('piecesJointesPdf', FileType::class, [
                'label'       => 'Pièces jointes (PDF)',
                'required'    => false,
                'multiple'    => true,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize'          => '5M',
                            'maxSizeMessage'   => 'La taille du fichier ne doit pas dépasser 5 Mo.',
                            'mimeTypes'        => ['application/pdf'],
                            'mimeTypesMessage' => 'Seuls les fichiers PDF sont acceptés.',
                        ]),
                    ]),
                ],
            ])
            ->add('commentaire', TextareaType::class, [
                'label'    => 'Mettre les commentaires :',
                'required' => false,
            ])
            ->add('generationToken', HiddenType::class);
    }
}
