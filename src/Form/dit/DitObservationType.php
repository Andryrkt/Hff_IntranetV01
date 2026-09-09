<?php

namespace App\Form\dit;

use App\Entity\dit\DitObservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DitObservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('observation', TextareaType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Ecrivez votre observation ...',
                    'rows'        => 1,
                    'class'       => 'message-input',
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
                    'attr' => [
                        'accept' => '.pdf,.jpg,.jpeg,.png'
                    ],
                    'constraints' => [
                        new All([
                            'constraints' => [
                                new File([
                                    'maxSize' => '5M',
                                    'mimeTypes' => [
                                        'application/pdf',
                                        'image/jpeg',
                                        'image/png',
                                    ],
                                    'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (PDF, JPG, PNG).',
                                ])
                            ]
                        ])
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DitObservation::class,
        ]);
    }
}
