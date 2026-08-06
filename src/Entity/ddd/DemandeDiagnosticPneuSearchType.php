<?php

namespace App\Entity\ddd;

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
        $builder
            ->add('numeroDemande', TextType::class, [
                'label' => 'N° Demande',
                'required' => false,
            ])
            ->add('demandeur', TextType::class, [
                'label' => 'Demandeur',
                'required' => false,
            ])
            ->add('idChantier', EntityType::class, [
                'class' => Chantier::class,
                'choice_label' => 'nomChantier',
                'label' => 'Chantier',
                'required' => false,
                'placeholder' => 'Tous',
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'À traiter atelier' => 'a_traiter_atelier',
                    'Clôturée' => 'cloturee',
                ],
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
            ->add('numeroParcMateriel', TextType::class, [
                'label' => 'N° Parc matériel',
                'required' => false,
            ])
            // ⬇️ NOUVEAUX CHAMPS (ajoutés pour correspondre au template)
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeDiagnosticPneuSearch::class,
            'csrf_protection' => false,
        ]);
    }
}
