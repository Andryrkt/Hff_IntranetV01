<?php

namespace App\Form\da;

use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
use App\Controller\Traits\da\MarkupIconTrait;
use App\Entity\admin\Agence;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\Service;
use App\Entity\da\DaSearch;
use App\Entity\da\DemandeAppro;
use App\Repository\admin\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaSearchType extends  AbstractType
{
    use PrepareAgenceServiceTrait;
    use MarkupIconTrait;

    private $agenceRepository;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->agenceRepository = $this->em->getRepository(Agence::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statut_or = StatutOrConstant::STATUT_OR;
        ksort($statut_or);

        if ($options['estAppro']) {
            $statut_da = StatutDaConstant::STATUT_DA;
            $statut_bc = StatutBcConstant::STATUT_BC;
        } else {
            $statut_da = StatutDaConstant::STATUT_DA_PAS_APPRO_NI_ADMIN;
            $statut_bc = StatutBcConstant::STATUT_BC_PAS_APPRO_NI_ADMIN;
        }


        $type_achat = [
            'Demande d’approvisionnement via OR'      => DemandeAppro::TYPE_DA_AVEC_DIT,
            'Demande d’achat direct'                  => DemandeAppro::TYPE_DA_DIRECT,
            'Demande de réapprovisionnement mensuel'  => DemandeAppro::TYPE_DA_REAPPRO_MENSUEL,
            'Demande de réapprovisionnement ponctuel' => DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL
        ];

        $choices = $this->prepareAgenceServiceChoices($options['allAgenceServices']);

        $agenceChoices = $choices['agenceChoices'];
        $serviceChoices = $choices['serviceChoices'];
        $serviceAttr = $choices['serviceAttr'];

        $builder
            ->add('afficherCloturees', CheckboxType::class, [
                'label'    => 'Afficher aussi les demandes d\'approvisionnement clôturées',
                'required' => false
            ])
            ->add('numDit', TextType::class, [
                'label'         => 'N° OR/DIT',
                'required'      => false
            ])
            ->add('numDa', TextType::class, [
                'label'         => 'N° DAP',
                'required'      => false
            ])
            ->add('numCde', TextType::class, [
                'label' => 'N° Commande',
                'required' => false
            ])
            ->add('demandeur', TextType::class, [
                'label'         => 'Demandeur',
                'required'      => false
            ])
            ->add('statutDA', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut de la DA',
                'choices'       => $statut_da,
                'required'      => false
            ])
            ->add('statutOR', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut',
                'choices'       => $statut_or,
                'required'      => false
            ])
            ->add('statutBC', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut du BC',
                'choices'       => $statut_bc,
                'required'      => false
            ])
            ->add('sortNbJours', ChoiceType::class, [
                'placeholder'   => '-- Choisir un tri --',
                'label'         => 'Tri par Nbr Jour(s)',
                'choices'       => [
                    'Ordre croissant'   => 'asc',
                    'Ordre décroissant' => 'desc',
                ],
                'required'      => false
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
                    'mapped'   => false,
                    'label'    => 'Centrale rattachée à la DA',
                    'required' => false
                ]
            )
            ->add('idMateriel', TextType::class, [
                'label'         => "N° Matériel",
                'required'      => false
            ])
            ->add('typeAchat', ChoiceType::class, [
                'label'         => 'Type de la demande d\'achat',
                'placeholder'   => '-- Choisir le type de la DA --',
                'choices'       => $type_achat,
                'required'      => false
            ])
            ->add('niveauUrgence', EntityType::class, [
                'label'         => 'Niveau d\'urgence',
                'label_html'    => true,
                'class'         => WorNiveauUrgence::class,
                'choice_label'  => 'description',
                'choice_value'  => 'description',
                'placeholder'   => '-- Choisir un niveau --',
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.description', 'DESC');
                },
                'attr' => [
                    'class' => 'niveauUrgence'
                ]
            ])
            ->add('dateDebutCreation', DateType::class, [
                'widget'        => 'single_text',
                'label'         => 'Date création (début)',
                'required'      => false,
            ])
            ->add('dateFinCreation', DateType::class, [
                'widget'        => 'single_text',
                'label'         => 'Date création (fin)',
                'required'      => false,
            ])
            ->add('dateDebutfinSouhaite', DateType::class, [
                'widget'        => 'single_text',
                'label'         => 'Date fin souhaitée (début)',
                'required'      => false,
            ])
            ->add('dateFinFinSouhaite', DateType::class, [
                'widget'        => 'single_text',
                'label'         => 'Date fin souhaitée (fin)',
                'required'      => false,
            ])
            // --- agenceEmetteur : ChoiceType ---
            ->add('agenceEmetteur', ChoiceType::class, [
                'label'       => 'Agence émetteur',
                'placeholder' => '-- Choisir une agence --',
                'required'    => false,
                'choices'     => $agenceChoices
            ])
            // --- serviceEmetteur : ChoiceType ---
            ->add('serviceEmetteur', ChoiceType::class, [
                'label'       => 'Service émetteur',
                'placeholder' => '-- Choisir une service --',
                'required'    => false,
                'choices'     => $serviceChoices,
                'choice_label' => function ($value) use ($options) {
                    // Retrouver le bon item et afficher service_code . ' ' . service_libelle
                    $item = $options['allAgenceServices'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
            // --- agenceDebiteur : ChoiceType ---
            ->add('agenceDebiteur', ChoiceType::class, [
                'label'       => 'Agence débiteur',
                'placeholder' => '-- Choisir une agence --',
                'required'    => false,
                'choices'     => $agenceChoices
            ])
            // --- serviceDebiteur : ChoiceType ---
            ->add('serviceDebiteur', ChoiceType::class, [
                'label'       => 'Service débiteur',
                'placeholder' => '-- Choisir une service --',
                'required'    => false,
                'choices'     => $serviceChoices,
                'choice_label' => function ($value) use ($options) {
                    // Retrouver le bon item et afficher service_code . ' ' . service_libelle
                    $item = $options['allAgenceServices'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'             => DaSearch::class,
            'allAgenceServices' => [],
            'estAppro'   => false,
        ]);
    }
}
