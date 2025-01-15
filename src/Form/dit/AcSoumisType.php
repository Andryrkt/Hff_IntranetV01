<?php

namespace App\Form\dit;

use App\Entity\dit\AcSoumis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class AcSoumisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomClient', TextType::class, [
                'label' => 'Nom client *',
                'required' => true,
            ])
            ->add('numeroBc', TextType::class, [
                'label' => 'N° de bon de commande *',
                'required' => true,
            ])
            ->add('dateBc', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du bon de commande *',
                'required' => true,
            ])
            ->add('descriptionBc', TextareaType::class,
            [
                'label' => 'Description bon de commande *',
                'required' => true,
                'attr' => [
                    'rows' => 5,
                    'class' => 'detailDemande'
                ],
            ])
            ->add('emailClient', EmailType::class, [
                'label' => 'Adress email client *',
                'required' => true
            ])
            ->add('pieceJoint01',
                FileType::class,
                [
                    'label' => 'Bon de commande (PDF) *',
                    'required' => false,
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
            ])


            ->add('dateCreation', TextType::class,
            [
                'label' => 'Date',
                'data' => '',
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('numeroDevis', TextType::class,
            [
                'label' => 'N° devis',
                'data' => '',
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('statutDevis', TextType::class,
            [
                'label' => 'Statut devis',
                'data' => '',
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('numeroDit', TextType::class,
            [
                'label' => 'N° DIT',
                'data' => '',
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('dateDevis', TextType::class,
            [
                'label' => 'Date devis',
                'data' => '',
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('montantDevis', TextType::class,
            [
                'label' => 'Montant devis',
                'data' => '',
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('emailContactHff', TextType::class,
            [
                'label' => 'Adresse email contact HFF',
                'data' => '',
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('telephoneContactHff', TextType::class,
            [
                'label' => 'N° téléphone contact HFF',
                'data' => '',
                'attr' => [
                    'disabled' => true
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AcSoumis::class,
        ]);
    }


}