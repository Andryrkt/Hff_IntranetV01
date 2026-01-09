<?php

namespace App\Form\da\daCdeFrn;


use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\da\DaAfficher;
use App\Factory\da\CdeFrnDto\CdeFrnSearchDto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CdeFrnListType extends  AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    private const STATUT_DA = [
        DemandeAppro::STATUT_VALIDE               => DemandeAppro::STATUT_VALIDE,
        DemandeAppro::STATUT_CLOTUREE             => DemandeAppro::STATUT_CLOTUREE,
        DemandeAppro::STATUT_SOUMIS_ATE           => DemandeAppro::STATUT_SOUMIS_ATE,
        DemandeAppro::STATUT_SOUMIS_APPRO         => DemandeAppro::STATUT_SOUMIS_APPRO,
        DemandeAppro::STATUT_DEMANDE_DEVIS        => DemandeAppro::STATUT_DEMANDE_DEVIS,
        DemandeAppro::STATUT_DEVIS_A_RELANCER     => DemandeAppro::STATUT_DEVIS_A_RELANCER,
        DemandeAppro::STATUT_EN_COURS_CREATION    => DemandeAppro::STATUT_EN_COURS_CREATION,
        DemandeAppro::STATUT_AUTORISER_EMETTEUR   => DemandeAppro::STATUT_AUTORISER_EMETTEUR,
        DemandeAppro::STATUT_EN_COURS_PROPOSITION => DemandeAppro::STATUT_EN_COURS_PROPOSITION,
    ];

    private function statutBc()
    {
        return $this->em->getRepository(DaAfficher::class)->getStatutsBc();
    }

    private const TYPE_ACHAT = [
        'DA Avec DIT' => DemandeAppro::TYPE_DA_AVEC_DIT,
        'DA Direct'   => DemandeAppro::TYPE_DA_DIRECT,
        'DA reappro'  => DemandeAppro::TYPE_DA_REAPPRO,
    ];

    private const TRI_NBR_JOURS =  [
        'Ordre croissant'   => 'asc',
        'Ordre décroissant' => 'desc',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statut_da = self::STATUT_DA;
        ksort($statut_da);

        $builder
            ->add('numDa', TextType::class, [
                'label'    => 'n° DA',
                'required' => false
            ])
            ->add('typeAchat', ChoiceType::class, [
                'label'       => 'Type de la demande d\'achat',
                'placeholder' => '-- Choisir le type de la DA --',
                'choices'     => self::TYPE_ACHAT,
                'required'    => false
            ])
            ->add('numDit', TextType::class, [
                'label' => 'n° DIT',
                'required' => false
            ])
            ->add('numOr', TextType::class, [
                'label' => 'n° OR',
                'required' => false
            ])
            ->add('numFrn', TextType::class, [
                'label' => 'n° Fournisseur',
                'required' => false
            ])
            ->add('frn', TextType::class, [
                'label' => 'Fournisseur',
                'required' => false
            ])
            ->add('numCde', TextType::class, [
                'label' => 'n° Commande',
                'required' => false
            ])
            ->add('ref', TextType::class, [
                'label' => 'Réference',
                'required' => false
            ])
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
                'required' => false
            ])
            ->add('niveauUrgence', EntityType::class, [
                'label'         => 'Niveau d\'urgence',
                'label_html'    => true,
                'class'         => WorNiveauUrgence::class,
                'choice_label'  => 'description',
                'choice_value'  => 'description',
                'placeholder'   => '-- Choisir le niveau d\'urgence--',
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.description', 'DESC');
                },
                'attr'          => [
                    'class' => 'niveauUrgence'
                ]
            ])
            ->add(
                'statutBc',
                ChoiceType::class,
                [
                    'label' => "Statut BC",
                    'choices' => $this->statutBc(),
                    'placeholder' => '-- Choisir la statut --',
                    'required' => false,
                ]
            )
            ->add('statutDA', ChoiceType::class, [
                'placeholder'   => '-- Choisir un statut --',
                'label'         => 'Statut de la DA',
                'choices'       => $statut_da,
                'required'      => false
            ])
            ->add('dateDebutOR', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début planning OR',
                'required' => false,
            ])
            ->add('dateFinOR', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin planning OR',
                'required' => false,
            ])
            ->add('dateDebutfinSouhaite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début fin souhaité',
                'required' => false,
            ])
            ->add('dateFinFinSouhaite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin fin souhaité',
                'required' => false,
            ])
            ->add('sortNbJours', ChoiceType::class, [
                'placeholder'   => '-- Choisir un tri --',
                'label'         => 'Tri par Nbr Jour(s)',
                'choices'       => self::TRI_NBR_JOURS,
                'required'      => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CdeFrnSearchDto::class,
            'em' => null,
        ]);
    }
}
