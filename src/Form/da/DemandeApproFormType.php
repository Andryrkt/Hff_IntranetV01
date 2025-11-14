<?php

namespace App\Form\da;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Model\dit\DitModel;

class DemandeApproFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** 
         * @var DemandeIntervention le dit associé au DA
         */
        $dit = $options['data']->getDit();
        $ditModel = new DitModel;
        $dataModel = $ditModel->recupNumSerieParcPourDa($dit->getIdMateriel());

        $builder
            ->add('objetDal', TextType::class, [
                'label' => 'Objet de la demande *',
                'attr' => [
                    'autofocus' => true,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'l\'objet de la demande ne peut pas être vide .', // Message d'erreur si le champ est vide
                    ]),
                ],
            ])
            ->add('detailDal', TextareaType::class, [
                'label' => 'Détail de la demande *',
                'attr' => [
                    'rows' => 5,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'le detail de la demande ne peut pas être vide .', // Message d'erreur si le champ est vide
                    ]),
                ],
            ])
            ->add('dateFinSouhaite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin souhaitée *',
                'constraints' => [
                    new NotBlank(['message' => 'la date ne doit pas être vide'])
                ]
            ])
            ->add(
                'niveauUrgence',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Niveau d’urgence',
                    'disabled' => true,
                    'data' => $options["data"]->getNiveauUrgence()
                ]
            )
            ->add(
                'agenceEmetteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Agence *',
                    'disabled' => true,
                    'data' => $options["data"]->getAgenceEmetteur()->getCodeAgence() . ' ' . $options["data"]->getAgenceEmetteur()->getLibelleAgence()
                ]
            )
            ->add(
                'agenceDebiteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Agence Débiteur *',
                    'disabled' => true,
                    'data' => $options["data"]->getAgenceDebiteur()->getCodeAgence() . ' ' . $options["data"]->getAgenceDebiteur()->getLibelleAgence()
                ]
            )
            ->add(
                'serviceEmetteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Service *',
                    'disabled' => true,
                    'data' => $options["data"]->getServiceEmetteur()->getCodeService() . ' ' . $options["data"]->getServiceEmetteur()->getLibelleService()
                ]
            )
            ->add(
                'serviceDebiteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Service Débiteur *',
                    'disabled' => true,
                    'data' => $options["data"]->getServiceDebiteur()->getCodeService() . ' ' . $options["data"]->getServiceDebiteur()->getLibelleService()
                ]
            )
            ->add(
                'idMateriel',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Id Matériel',
                    'disabled' => true,
                    'data' => $dit->getIdMateriel()
                ]
            )
            ->add(
                'serie',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'N° Série',
                    'disabled' => true,
                    'data' => $dataModel[0]['num_serie']
                ]
            )
            ->add(
                'parc',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'N° Parc',
                    'disabled' => true,
                    'data' => $dataModel[0]['num_parc']
                ]
            )
            ->add(
                'dit',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'N° DIT',
                    'disabled' => true,
                    'data' => $dit->getNumeroDemandeIntervention()
                ]
            )
            ->add(
                'objetDit',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Objet du DIT',
                    'disabled' => true,
                    'data' => $dit->getObjetDemande()
                ]
            )
            ->add('DAL', CollectionType::class, [
                'label' => false,
                'entry_type' => DemandeApproLFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->add('observation', TextareaType::class, [
                'label' => 'Observation',
                'attr' => [
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
