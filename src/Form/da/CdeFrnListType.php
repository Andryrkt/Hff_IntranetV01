<?php

namespace App\Form\da;


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
        'A générer' => 'A générer',
        'A éditer' => 'A éditer',
        'A soumettre à validation' => 'A soumettre à validation',
        'A envoyer au fournisseur' => 'A envoyer au fournisseur'
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numDa', TextType::class, [
                'label' => 'n° DA',
                'required' => false
            ])
            ->add('achatDirect', ChoiceType::class, [
                'label' => 'Achat direct',
                'placeholder' => '-- Choisir le choix --',
                'choices' => ['NON' => 0, 'OUI' => 1],
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
            ->add('dateDebutDAL', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début fin souhaité',
                'required' => false,
            ])
            ->add('dateFinDAL', DateType::class, [
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
