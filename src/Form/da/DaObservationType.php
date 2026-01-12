<?php

namespace App\Form\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DaObservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $datypeId = $options['daTypeId'];

        if ($datypeId != DemandeAppro::TYPE_DA_REAPPRO) {
            if ($datypeId == DemandeAppro::TYPE_DA_DIRECT) $observationLabel = 'Autoriser le service à modifier';
            if ($datypeId == DemandeAppro::TYPE_DA_AVEC_DIT) $observationLabel = 'Autoriser l’ATELIER à modifier';
            $builder
                ->add('statutChange', CheckboxType::class, [
                    'label'    => $observationLabel,
                    'required' => false
                ]);
        }

        $builder
            ->add('observation', TextareaType::class, [
                'label' => 'Observation',
                'attr'  => [
                    'rows' => 5,
                ],
                'required' => true
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DaObservation::class,
            'daTypeId' => null, // valeur par défaut
        ]);
    }
}
