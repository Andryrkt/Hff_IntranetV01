<?php

namespace App\Form\cas;

use App\Entity\admin\Agence;
use App\Entity\cas\Casier;
use App\Repository\admin\AgenceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CasierForm2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
        ->add(
            'dateDemande',
            DateTimeType::class,
            [
            'label' => 'Date',
            'mapped' => false,
                'widget' => 'single_text', // Utilisez le widget single_text pour une meilleure compatibilité
                'html5' => false, // Désactivez l'HTML5 si vous souhaitez un format spécifique
                'format' => 'dd/MM/yyyy',
            'attr' => [
                'disabled' => true,
            ],
            'data' => $options["data"]->getDateCreation(),
        ]
        )
        ->add(
            'agence',
            EntityType::class,
            [
            'label' => 'Agence rattacher',
            'placeholder' => '-- Choisir une agence  --',
            'class' => Agence::class,
            'choice_label' => function (Agence $agence): string {
                return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
            },
            'required' => false,
            'query_builder' => function (AgenceRepository $agenceRepository) {
                return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
            },
            'required' => true,
        ]
        )
        ->add(
            'motif',
            TextType::class,
            [
                'label' => 'Motif de création',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le champ motif ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le champ motif ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ]
        )
        ->add(
            'client',
            TextType::class,
            [
                'label' => 'Client',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le champ client ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'max' => 8,
                        'maxMessage' => 'Le champ client ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ]
        )
        ->add(
            'chantier',
            TextType::class,
            [
                'label' => 'Chantier',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le champ chantier ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'max' => 8,
                        'maxMessage' => 'Le champ chantier ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ]
        )
        ->add(
            'designation',
            TextType::class,
            [
                'label' => 'Désignation ',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getDesignation(),
            ]
        )
        ->add(
            'idMateriel',
            TextType::class,
            [
                'label' => 'ID matériel',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getIdMateriel(),
            ]
        )
        ->add(
            'numSerie',
            TextType::class,
            [
                'label' => 'N° Série ',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getNumSerie(),
            ]
        )
        ->add(
            'numParc',
            TextType::class,
            [
                'label' => 'N° Parc',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getNumParc(),
            ]
        )
        ->add(
            'groupe',
            TextType::class,
            [
                'label' => 'Groupe ',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getGroupe(),
            ]
        )
        ->add(
            'constructeur',
            TextType::class,
            [
                'label' => 'Constructeur',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getConstructeur(),
            ]
        )
        ->add(
            'modele',
            TextType::class,
            [
                'label' => 'Modèle',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getModele(),
            ]
        )
        ->add(
            'anneeDuModele',
            TextType::class,
            [
                'label' => 'Année du modèle',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getAnneeDuModele(),
            ]
        )
        ->add(
            'affectation',
            TextType::class,
            [
                'label' => 'Affectation',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getAffectation(),
            ]
        )
        ->add(
            'dateAchat',
            TextType::class,
            [
                'label' => 'Date d’achat ',
                'mapped' => false,
                'attr' => [
                    'disabled' => true,
                ],
                'data' => $options["data"]->getDateAchat(),
            ]
        )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Casier::class,
        ]);
    }
}
