<?php

namespace App\Form\magasin\Planning;



use App\Dto\Magasin\Planning\PlanningMagasinSearchDto;
use App\Form\common\AgenceServiceType;
use App\Form\common\DateRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningMagasinSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Champ texte + autocomplétion JS (liste alimentée par
            // PlanningMagasinModel::recupListeFournissseur() via l'API) : recherche
            // par nom OU par code fournisseur dans un seul et même champ.
            ->add('fournisseur', TextType::class, [
                'label' => 'Fournisseur',
                'required' => false,
            ])
            ->add('numeroCommande', TextType::class, [
                'label' => 'Numéro Commande',
                'required' => false,
            ])
            ->add('agenceService', AgenceServiceType::class, [
                'label' => false,
                'required' => false,
                'agence_label' => 'Agence',
                'service_label' => 'Service',
                'agence_placeholder' => '-- Choisir une agence --',
                'service_placeholder' => '-- Choisir un service --',
                'em' => $options['em'],
            ])
            ->add('dateCommande', DateRangeType::class, [
                'debut_label' => 'Date commande (début)',
                'fin_label' => 'Date commande (fin)',
            ])
            ->add('months', ChoiceType::class, [
                'choices' => [
                    '3 mois suivant'    => 3,
                    '6 mois suivant'    => 6,
                    '12 mois suivant'   => 12,
                    '12 mois précédent' => 13,
                    'Année encours'     => 9,
                    'Année suivante'    => 11,
                    'Année précédente'  => 14,
                ],
                'expanded' => false, // Utiliser une liste déroulante
                'multiple' => false, // Sélectionner une seule valeur
                'label'    => 'Nombre de mois',
                'data'     => 3
            ]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'       => PlanningMagasinSearchDto::class,
            'em'               => null,
        ]);
    }
}
