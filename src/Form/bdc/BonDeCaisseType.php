<?php

namespace App\Form\bdc;

use App\Entity\bdc\BonDeCaisse;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\AgenceServiceIrium;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManagerInterface;

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
            ->add('dateDemande', DateType::class, [
                'required' => false,
                'label' => 'Date demande (début)',
                'widget' => 'single_text'
            ])
            ->add('dateDemandeFin', DateType::class, [
                'required' => false,
                'label' => 'Date demande (fin)',
                'widget' => 'single_text',
                'mapped' => false
            ])
            ->add('agenceDebiteur', ChoiceType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Agence',
                'choices' => array_unique($agences),
                'placeholder' => 'Toutes les agences',
                'attr' => ['id' => 'agence-select']
            ])
            ->add('service', ChoiceType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Service',
                'choices' => [],  // Sera rempli dynamiquement par JavaScript
                'placeholder' => 'Tous les services',
                'attr' => ['id' => 'service-select'],
                // Désactiver la validation des choix
                'invalid_message' => 'Service invalide',
                // Permettre les valeurs personnalisées
                'choice_loader' => null,
                'choice_value' => function ($value) {
                    return $value;
                },
                // Ajouter cette option pour contourner la validation des choix
                'constraints' => []
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
            ]);

        // PRE_SUBMIT : reconstruire les choices "service" selon l'agence postée
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($em) {
            $data = $event->getData() ?? [];
            $form = $event->getForm();

            // la valeur postée pour l'agence (ici on attend agence_ips)
            $agenceCode = $data['agence'] ?? null;
            $serviceValue = $data['service'] ?? null;

            $choices = [];

            if ($agenceCode) {
                // NOTE : bien utiliser les noms de propriétés de l'entité (snake_case)
                $services = $em->getRepository(AgenceServiceIrium::class)
                    ->createQueryBuilder('asi')
                    ->select('asi.service_ips AS code, asi.libelle_service_ips AS nom')
                    ->where('asi.agence_ips = :ag')->setParameter('ag', $agenceCode)
                    ->orderBy('asi.libelle_service_ips', 'ASC')
                    ->getQuery()
                    ->getArrayResult();

                foreach ($services as $row) {
                    // Format: "Code - Nom" (ex: "A102 - Service RH")
                    $choices[$row['code'] . ' ' . $row['nom']] = $row['code'];
                }
            }

            // si l'utilisateur a posté une valeur de service qui n'est pas dans la liste,
            // on l'ajoute pour éviter l'erreur "This value is not valid."
            if ($serviceValue && !in_array($serviceValue, $choices, true)) {
                $choices[$serviceValue] = $serviceValue;
            }

            $form->add('service', ChoiceType::class, [
                'required' => false,
                'mapped' => false,
                'placeholder' => 'Tous les services',
                'choices' => $choices,
                'attr' => ['id' => 'service-select'],
                // Désactiver la validation des choix
                'invalid_message' => 'Service invalide',
                // Permettre les valeurs personnalisées
                'choice_loader' => null,
                'choice_value' => function ($value) {
                    return $value;
                },
                // Ajouter cette option pour contourner la validation des choix
                'constraints' => []
            ]);
        });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BonDeCaisse::class,
            'method' => 'GET',
            'csrf_protection' => false
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
