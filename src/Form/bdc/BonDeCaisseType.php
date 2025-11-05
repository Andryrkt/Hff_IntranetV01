<?php

namespace App\Form\bdc;

use App\Dto\bdc\BonDeCaisseDto;
use App\Entity\bdc\BonDeCaisse;
use App\Form\common\DateRangeType;
use App\Entity\admin\StatutDemande;
use Symfony\Component\Form\FormEvent;
use App\Form\common\AgenceServiceType;
use Symfony\Component\Form\FormEvents;
use App\Entity\admin\AgenceServiceIrium;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BonDeCaisseType extends AbstractType
{
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

        // Récupérer les agences et services depuis AgenceServiceIrium
        $agencesServices = $em->getRepository(AgenceServiceIrium::class)->findBy(["societe_ios" => 'HF'], ["agence_ips" => "ASC"]);
        $agences = [];

        // Créer un tableau associatif pour les agences (libellé => code)
        foreach ($agencesServices as $as) {
            // Utiliser agence_ips au lieu de agence_i100
            // Format: "Code - Nom" (ex: "80 - Administration")
            $agences[$as->getAgenceips() . ' ' . $as->getNomagencei100()] = $as->getAgenceips();
        }

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
            ->add('emetteur', AgenceServiceType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'agence_label' => 'Agence Emetteur',
                'service_label' => 'Service Emetteur',
                'agence_placeholder' => '-- Agence Emetteur --',
                'service_placeholder' => '-- Service Emetteur --',
                'em' => $options['em'] ?? null,
            ])
            ->add('debiteur', AgenceServiceType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'agence_label' => 'Agence Debiteur',
                'service_label' => 'Service Debiteur',
                'agence_placeholder' => '-- Agence Debiteur --',
                'service_placeholder' => '-- Service Debiteur --',
                'em' => $options['em'] ?? null,
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
        $resolver->setDefined(['em']);
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
