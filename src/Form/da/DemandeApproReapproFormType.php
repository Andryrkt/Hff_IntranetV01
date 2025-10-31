<?php

namespace App\Form\da;

use App\Entity\da\DemandeAppro;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DemandeApproReapproFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objetDal', TextType::class, [
                'label' => 'Objet de la demande *',
                'attr'  => [
                    'autofocus' => true,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'l\'objet de la demande ne peut pas être vide .', // Message d'erreur si le champ est vide
                    ]),
                ],
            ])
            ->add('detailDal', TextareaType::class, [
                'label'    => 'Détail de la demande',
                'required' => false,
                'attr'     => [
                    'rows' => 5,
                ],
            ])
            ->add('dateFinSouhaite', DateType::class, [
                'widget'      => 'single_text',
                'label'       => 'Date fin souhaitée *',
                'constraints' => [
                    new NotBlank(['message' => 'la date ne doit pas être vide'])
                ]
            ])
            ->add(
                'agenceEmetteur',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'Agence *',
                    'disabled' => true,
                    'data'     => $options["data"]->getAgenceEmetteur()->getCodeAgence() . ' ' . $options["data"]->getAgenceEmetteur()->getLibelleAgence()
                ]
            )
            ->add(
                'agenceDebiteur',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'Agence Débiteur *',
                    'disabled' => true,
                    'data'     => $options["data"]->getAgenceDebiteur()->getCodeAgence() . ' ' . $options["data"]->getAgenceDebiteur()->getLibelleAgence()
                ]
            )
            ->add(
                'serviceEmetteur',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'Service *',
                    'disabled' => true,
                    'data'     => $options["data"]->getServiceEmetteur()->getCodeService() . ' ' . $options["data"]->getServiceEmetteur()->getLibelleService()
                ]
            )
            ->add(
                'serviceDebiteur',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'Service Débiteur *',
                    'disabled' => true,
                    'data'     => $options["data"]->getServiceDebiteur()->getCodeService() . ' ' . $options["data"]->getServiceDebiteur()->getLibelleService()
                ]
            )
            ->add('DAL', CollectionType::class, [
                'label'        => false,
                'entry_type'   => DemandeApproLReapproFormType::class,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
            ])
            ->add('observation', TextareaType::class, [
                'label'    => 'Observation',
                'attr'     => [
                    'rows' => 5,
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeAppro::class,
        ]);
    }
}
