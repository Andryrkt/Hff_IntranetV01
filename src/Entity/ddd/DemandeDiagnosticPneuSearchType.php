<?php

namespace App\Entity\ddd;

use App\Form\pol\ddd\DemandeDiagnosticPneuType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeDiagnosticPneuSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $chantierChoices = [];
        foreach ($options['chantiers'] as $chantier) {
            $chantierChoices[$chantier->getNomChantier()] = $chantier->getId();
        }

        $builder
            ->add('numeroDemande', TextType::class, [
                'label' => 'N° Demande',
                'required' => false,
            ])
            ->add('demandeur', TextType::class, [
                'label' => 'Demandeur',
                'required' => false,
            ])
            ->add('idChantier', ChoiceType::class, [
                'label'       => 'Chantier',
                'choices'     => $chantierChoices,
                'required'    => false,
                'placeholder' => 'Tous',
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => DemandeDiagnosticPneu::STATUTS,
                'required' => false,
                'placeholder' => 'Tous',
            ])
            ->add('dateCreationDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date création (début)',
                'required' => false,
            ])
            ->add('dateCreationFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date création (fin)',
                'required' => false,
            ])
            ->add('dateDepartChantierDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date départ chantier (début)',
                'required' => false,
            ])
            ->add('dateDepartChantierFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date départ chantier (fin)',
                'required' => false,
            ])
            ->add('numeroParcMateriel', TextType::class, [
                'label' => 'N° Parc matériel',
                'required' => false,
            ])
            ->add('numeroDit', TextType::class, [
                'label' => 'N° DIT',
                'required' => false,
            ])
            ->add('numeroOr', TextType::class, [
                'label' => 'N° OR',
                'required' => false,
            ])
            ->add('livraison', ChoiceType::class, [
                'label' => 'Livraison',
                'choices' => [
                    'Machine' => 'MACHINE',
                    'Pneu'    => 'PNEU',
                ],
                'required' => false,
                'placeholder' => 'Tous',
            ])
            ->add('motifs', ChoiceType::class, [
                'label'    => 'Motifs',
                'choices'  => DemandeDiagnosticPneuType::MOTIFS, // or your constant class
                'multiple' => true,    // allow multiple selections
                'expanded' => true,    // render as checkboxes
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeDiagnosticPneuSearch::class,
            'csrf_protection' => false,
            'chantiers' => [],
        ]);
        $resolver->setAllowedTypes('chantiers', 'array');
    }
}
