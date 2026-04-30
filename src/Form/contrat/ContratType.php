<?php

namespace App\Form\contrat;

use App\Entity\contrat\Contrat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ Référence
            ->add('referenceSearch', TextType::class, [
                'label' => 'Référence',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Référence du contrat',
                    'class' => 'form-control'
                ]
            ])
            
            // Date d'enregistrement (début)
            ->add('date_enregistrement_debut', DateType::class, [
                'label' => 'Date début enregistrement',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            
            // Date d'enregistrement (fin)
            ->add('date_enregistrement_fin', DateType::class, [
                'label' => 'Date fin enregistrement',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            
            // Agence
            ->add('agenceSearch', ChoiceType::class, [
                'label' => 'Agence',
                'required' => false,
                'choices' => $options['agences'] ?? $this->getAgencesList(),
                'placeholder' => 'Toutes les agences',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'contrat_agenceSearch'
                ]
            ])
            
            // Service
            ->add('serviceSearch', ChoiceType::class, [
                'label' => 'Service',
                'required' => false,
                'choices' => $options['services'] ?? [],
                'placeholder' => 'Tous les services',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'contrat_serviceSearch'
                ]
            ])
            
            // Partenaire
            ->add('nom_partenaireSearch', TextType::class, [
                'label' => 'Partenaire',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom du partenaire',
                    'class' => 'form-control'
                ]
            ])
            
            // Type de tiers
            ->add('type_tiersSearch', ChoiceType::class, [
                'label' => 'Type tiers',
                'required' => false,
                'choices' => [
                    'CLIENT' => 'CLIENT',
                    'FOURNISSEUR' => 'FOURNISSEUR',
                ],
                'placeholder' => 'Tous les types',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            
            // Date de début de contrat
            ->add('date_debut_contrat', DateType::class, [
                'label' => 'Date début contrat',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            
            // Date de fin de contrat
            ->add('date_fin_contrat', DateType::class, [
                'label' => 'Date fin contrat',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            
            // Bouton de recherche
            ->add('search', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    private function getAgencesList(): array
    {
        // Liste statique des agences HFF (pour fallback si non fourni par le controller)
        // Format : 'Label affiché' => 'Valeur (code court)'
        return [
            '01-ANTANANARIVO' => '01',
            '02-ANTANANARIVO' => '02',
            '03-ANTANANARIVO' => '03',
            '04-ANTANANARIVO' => '04',
            '05-ANTANANARIVO' => '05',
            '06-ANTANANARIVO' => '06',
            '07-ANTANANARIVO' => '07',
            '08-ANTANANARIVO' => '08',
            '09-ANTANANARIVO' => '09',
            '10-ANTANANARIVO' => '10',
            '11-ANTANANARIVO' => '11',
            '12-ANTANANARIVO' => '12',
            '13-ANTANANARIVO' => '13',
            '14-ANTANANARIVO' => '14',
            '15-ANTANANARIVO' => '15',
            '16-ANTANANARIVO' => '16',
            '17-ANTANANARIVO' => '17',
            '18-ANTANANARIVO' => '18',
            '19-ANTANANARIVO' => '19',
            '20-ANTANANARIVO' => '20',
            '21-ANTANANARIVO' => '21',
            '22-ANTANANARIVO' => '22',
            '23-ANTANANARIVO' => '23',
            '24-ANTANANARIVO' => '24',
            '25-ANTANANARIVO' => '25',
            '26-ANTANANARIVO' => '26',
            '27-ANTANANARIVO' => '27',
            '28-ANTANANARIVO' => '28',
            '29-ANTANANARIVO' => '29',
            '30-ANTANANARIVO' => '30',
            '31-ANTANANARIVO' => '31',
            '32-ANTANANARIVO' => '32',
            '33-ANTANANARIVO' => '33',
            '34-ANTANANARIVO' => '34',
            '35-ANTANANARIVO' => '35',
            '36-ANTANANARIVO' => '36',
            '37-ANTANANARIVO' => '37',
            '38-ANTANANARIVO' => '38',
            '39-ANTANANARIVO' => '39',
            '40-ANTANANARIVO' => '40',
            '41-ANTANANARIVO' => '41',
            '42-ANTANANARIVO' => '42',
            '43-ANTANANARIVO' => '43',
            '44-ANTANANARIVO' => '44',
            '45-ANTANANARIVO' => '45',
            '46-ANTANANARIVO' => '46',
            '47-ANTANANARIVO' => '47',
            '48-ANTANANARIVO' => '48',
            '49-ANTANANARIVO' => '49',
            '50-ANTANANARIVO' => '50',
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contrat::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'agences' => [], // Option pour les agences dynamiques
            'services' => [], // Option pour les services dynamiques
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'contrat';
    }
}
