<?php

namespace App\Form\bdc;

use App\Dto\bdc\BonDeCaisseDto;
use App\Entity\bdc\BonDeCaisse;
use App\Form\common\DateRangeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use App\Traits\PrepareAgenceServiceTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BonDeCaisseType extends AbstractType
{
    use PrepareAgenceServiceTrait;
    private $em;

    public function __construct(?EntityManagerInterface $em = null)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Utiliser l'EntityManager des options si celui du constructeur est null
        $em = $this->em ?? $options['em'] ?? null;

        if (!$em) {
            throw new \InvalidArgumentException('EntityManager is required');
        }

        $choices = $this->prepareAgenceServiceChoices($options['agenceServiceAutorises'], false);

        $agenceChoices = $choices['agenceChoices'];
        $serviceChoices = $choices['serviceChoices'];
        $serviceAttr = $choices['serviceAttr'];

        // Récupérer les statuts depuis la table Statut_demande
        $statuts = $this->getStatutChoicesFromDatabase($em);

        $builder
            ->add('numeroDemande', TextType::class, [
                'required' => false,
                'label' => 'Numéro demande'
            ])
            ->add('dateDemande', DateRangeType::class, [
                'label' => false,
                'debut_label' => 'Date demande (début)',
                'fin_label' => 'Date demande (fin)',
            ])
            ->add('statutDemande', ChoiceType::class, [
                'required' => false,
                'mapped' => true,
                'label' => 'Statut',
                'placeholder' => 'Tous les statuts',
                'choices' => $statuts,
                'choice_value' => function ($value) {
                    return $value; // Retourne la valeur telle quelle au lieu d'un indice
                }
            ])
            ->add('caisseRetrait', ChoiceType::class, [
                'required' => false,
                'label' => 'Caisse de retrait',
                'choices' => [
                    'Caisse principale' => 'CAISSE_PRINCIPALE',
                    'Caisse secondaire' => 'CAISSE_SECONDAIRE',
                    'Caisse annexe' => 'CAISSE_ANNEXE'
                ],
                'placeholder' => 'Toutes les caisses'
            ])
            ->add('typePaiement', ChoiceType::class, [
                'required' => false,
                'label' => 'Type de paiement',
                'choices' => [
                    'Espèces' => 'ESPECES',
                    'Chèque' => 'CHEQUE',
                    'Virement' => 'VIREMENT'
                ],
                'placeholder' => 'Tous les types'
            ])
            ->add('retraitLie', ChoiceType::class, [
                'required' => false,
                'label' => 'Retrait lié à',
                'choices' => [
                    'Avance' => 'AVANCE',
                    'Remboursement' => 'REMBOURSEMENT',
                    'Salaire' => 'SALAIRE',
                    'Autre' => 'AUTRE'
                ],
                'placeholder' => 'Tous les retraits'
            ])
            ->add('nomValidateurFinal', TextType::class, [
                'label' => 'Nom Validateur Final',
                'required' => false,
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
                    $item = $options['agenceServiceAutorises'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
            // --- agenceDebiteur : ChoiceType ---
            ->add('agenceDebiteur', ChoiceType::class, [
                'label'       => 'Agence Destinataire',
                'placeholder' => '-- Choisir une agence --',
                'required'    => false,
                'choices'     => $agenceChoices
            ])
            // --- serviceDebiteur : ChoiceType ---
            ->add('serviceDebiteur', ChoiceType::class, [
                'label'       => 'Service Destinataire',
                'placeholder' => '-- Choisir une service --',
                'required'    => false,
                'choices'     => $serviceChoices,
                'choice_label' => function ($value) use ($options) {
                    // Retrouver le bon item et afficher service_code . ' ' . service_libelle
                    $item = $options['agenceServiceAutorises'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BonDeCaisseDto::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'em' => null,
        ]);

        // Définir l'option 'em' pour permettre de passer l'EntityManager
        $resolver->setDefined(['em', 'agenceServiceAutorises']);
        $resolver->setAllowedTypes('em', ['null', EntityManagerInterface::class]);
    }

    private function getStatutChoicesFromDatabase(EntityManagerInterface $em): array
    {
        // Récupération des statuts depuis la table demande_bon_de_caisse
        $statuts = $em->getRepository(BonDeCaisse::class)->getStatut();
        $choices = [];
        $choices = array_column($statuts, 'statutDemande', 'statutDemande');

        return $choices;
    }
}
