<?php

namespace App\Form\dit;

use App\Controller\Traits\FormatageTrait;
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
    use FormatageTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomClient', TextType::class, [
                'label' => 'Client *',
                'required' => true,
                'attr' => [
                    'class' => 'autocomplete',
                    'autocomplete' => 'off',
                ]
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
            ])

            //====================================================================================================================================
            ->add('dateCreation', TextType::class,
            [
                'label' => 'Date',
                'data' => $options['data']->getDateCreation()->format('d/m/Y'),
                'required' => false,
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('numeroDevis', TextType::class,
            [
                'label' => 'N° devis',
                'data' => $options['data']->getNumeroDevis(),
                'required' => false,
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('statutDevis', TextType::class,
            [
                'label' => 'Statut devis',
                'data' => $options['data']->getStatutDevis(),
                'required' => false,
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('numeroDit', TextType::class,
            [
                'label' => 'N° DIT',
                'data' => $options['data']->getNumeroDit(),
                'required' => false,
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('dateDevis', TextType::class,
            [
                'label' => 'Date devis',
                'data' => $options['data']->getDateDevis()->format('d/m/Y'),
                'required' => false,
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('montantDevis', TextType::class,
            [
                'label' => 'Montant devis',
                'data' => $this->formatNumber($options['data']->getMontantDevis()),
                'required' => false,
                'attr' => [
                    'disabled' => false
                ]
            ])
            ->add('emailContactHff', TextareaType::class,
            [
                'label' => 'Adresse email contact HFF',
                'data' => $options['data']->getEmailContactHff()?: 'L\'adresse email du chef atelier <réalisé_par> est introuvable',
                'required' => false,
                'attr' => [
                    'disabled' => true,
                    'class' => $options['data']->getEmailContactHff() ? '' : 'text-danger'
                ]
            ])
            ->add('telephoneContactHff', TextareaType::class,
            [
                'label' => 'N° téléphone contact HFF',
                'data' => $options['data']->getTelephoneContactHff()?: 'Le téléphone du chef atelier <réalisé_par> est introuvable',
                'required' => false,
                'attr' => [
                    'disabled' => true,
                    'class' => $options['data']->getTelephoneContactHff() ? '' : 'text-danger'
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