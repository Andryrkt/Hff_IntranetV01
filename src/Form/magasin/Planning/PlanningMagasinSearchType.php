<?php

namespace App\Form\magasin\Planning;



use App\Dto\Magasin\Planning\PlanningMagasinSearchDto;
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
            ->add('nomFournisseur', TextType::class, [
                'label' => 'Nom Fournisseur',
                'required' => false,
            ])
            ->add('codeFournisseur', TextType::class, [
                'label' => 'Code Fournisseur',
                'required' => false,
            ])
            ->add('numeroCommande', TextType::class, [
                'label' => 'Numéro Commande',
                'required' => false,
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
        ]);
    }
}
