<?php

namespace App\Form\da;

use App\Form\common\AgenceServiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\da\DemandeApproParent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\admin\dit\WorNiveauUrgenceRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DemandeApproAchatFormType extends AbstractType
{
    private EntityManagerInterface $em;
    private WorNiveauUrgenceRepository $niveauUrgenceRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->niveauUrgenceRepository = $em->getRepository(WorNiveauUrgence::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                'serviceEmetteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Service *',
                    'disabled' => true,
                    'data' => $options["data"]->getServiceEmetteur()->getCodeService() . ' ' . $options["data"]->getServiceEmetteur()->getLibelleService()
                ]
            )
            ->add('debiteur', AgenceServiceType::class, [
                'label'               => false,
                'agence_required'     => true,
                'service_required'    => true,
                'agence_label'        => 'Agence Debiteur (*)',
                'service_label'       => 'Service Debiteur (*)',
                'agence_placeholder'  => '-- Agence Debiteur --',
                'service_placeholder' => '-- Service Debiteur --',
                'data_agence'         => $options['data']->getAgenceDebiteur() ?? null,
                'data_service'        => $options['data']->getServiceDebiteur() ?? null,
                'em'                  => $this->em,
            ])
            ->add('niveauUrgence', ChoiceType::class, [
                'label'        => 'Niveau d\'urgence *',
                'choices'      => $this->niveauUrgenceRepository->createQueryBuilder('n')
                    ->orderBy('n.description', 'ASC')
                    ->getQuery()
                    ->getResult(),
                'choice_label' => 'description',
                'choice_value' => 'description',
                'placeholder'  => '-- Choisir un niveau d\'urgence --',
                'required'     => true,
                'data'         => $this->niveauUrgenceRepository->findOneBy(['description' => $options["data"]->getNiveauUrgence()]),
                'attr'         => ['class' => 'niveauUrgence'],
            ])
            ->add('demandeApproParentLines', CollectionType::class, [
                'label'        => false,
                'entry_type'   => DemandeApproParentLineFormType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
            ])
            ->add(
                'codeCentrale',
                TextType::class,
                [
                    'label'    => false,
                    'required' => false
                ]
            )
            ->add(
                'desiCentrale',
                TextType::class,
                [
                    'label'    => 'Centrale rattachée à la DA',
                    'required' => false
                ]
            )
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
            'data_class' => DemandeApproParent::class,
        ]);
    }
}
