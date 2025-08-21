<?php

namespace App\Form\da\daCdeFrn;


use App\Entity\da\DaSoumissionBc;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CdeFrnListType extends  AbstractType
{

    private const STATUT_BC = [
        DaSoumissionBc::STATUT_A_GENERER                    => DaSoumissionBc::STATUT_A_GENERER,
        DaSoumissionBc::STATUT_A_EDITER                     => DaSoumissionBc::STATUT_A_EDITER,
        DaSoumissionBc::STATUT_A_SOUMETTRE_A_VALIDATION     => DaSoumissionBc::STATUT_A_SOUMETTRE_A_VALIDATION,
        DaSoumissionBc::STATUT_A_VALIDER_DA                 => DaSoumissionBc::STATUT_A_VALIDER_DA,
        DaSoumissionBc::STATUT_A_ENVOYER_AU_FOURNISSEUR     => DaSoumissionBc::STATUT_A_ENVOYER_AU_FOURNISSEUR,
        DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR     => DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR
    ];

    private const TYPE_ACHAT = [
        'Tous' => 'tous',
        'Avec DIT' => 'avec_dit',
        'Direct' => 'direct',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numDa', TextType::class, [
                'label' => 'n° DA',
                'required' => false
            ])
            ->add('achatDirect', ChoiceType::class, [
                'label' => 'Type de la demande d\'achat',
                'placeholder' => '-- Choisir le choix --',
                'choices' => self::TYPE_ACHAT,
                'required' => false
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
                'label' => 'Niveau d\'urgence',
                'label_html' => true,
                'class' => WorNiveauUrgence::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir un niveau--',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.description', 'DESC');
                },
                'attr' => [
                    'class' => 'niveauUrgence'
                ]
            ])
            ->add(
                'statutBc',
                ChoiceType::class,
                [
                    'label' => "Statut BC",
                    'choices' => self::STATUT_BC,
                    'placeholder' => '-- Choisir --',
                    'required' => false,
                ]
            )
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
