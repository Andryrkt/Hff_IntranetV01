<?php

namespace App\Service\da;

use App\Constants\da\StatutDaConstant;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Repository\da\DaAfficherRepository;
use App\Traits\JoursOuvrablesTrait;
use Doctrine\ORM\EntityManagerInterface;

class DaTimelineService
{
    use JoursOuvrablesTrait;
    private DaAfficherRepository $daAfficherRepository;
    private $styleStatutDA = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
    }

    public function initStyleStatuts()
    {
        $this->styleStatutDA = [
            StatutDaConstant::STATUT_VALIDE               => 'bg-bon-achat-valide',
            StatutDaConstant::STATUT_CLOTUREE             => 'bg-bon-achat-valide',
            StatutDaConstant::STATUT_TERMINER             => 'bg-primary text-white',
            StatutDaConstant::STATUT_SOUMIS_ATE           => 'bg-proposition-achat',
            StatutDaConstant::STATUT_DW_A_VALIDE          => 'bg-soumis-validation',
            StatutDaConstant::STATUT_SOUMIS_APPRO         => 'bg-demande-achat',
            StatutDaConstant::STATUT_REFUSE_APPRO         => 'bg-refuse-appro',
            StatutDaConstant::STATUT_DEMANDE_DEVIS        => 'bg-demande-devis',
            StatutDaConstant::STATUT_DEVIS_A_RELANCER     => 'bg-devis-a-relancer',
            StatutDaConstant::STATUT_EN_COURS_CREATION    => 'bg-en-cours-creation',
            StatutDaConstant::STATUT_AUTORISER_EMETTEUR   => 'bg-creation-demande-initiale',
            StatutDaConstant::STATUT_EN_COURS_PROPOSITION => 'bg-en-cours-proposition',
        ];
    }

    public function getTimelineData(string $numeroDa): array
    {
        $allDatas = $this->daAfficherRepository->getTimelineData($numeroDa);
        if (empty($allDatas)) return ['DA' => [], 'BC' => []];

        $this->initStyleStatuts();

        $timelineDa = $this->buildTimelineDA($allDatas);
        $timelineBc = $this->buildTimelineBC($numeroDa, end($allDatas));

        if (empty($timelineBc)) {
            $lastEntryData = end($allDatas);
            $nbrJours = $this->formatDuration(
                $this->differenceJoursOuvrables(
                    $lastEntryData['dateCreation'],
                    new \DateTime()
                )
            );
            $timelineDa[array_key_last($timelineDa)]['nbrJours'] = $nbrJours;
            $timelineDa[] = $this->createCurrentDateEntry();
        }

        return [
            'DA' => $timelineDa,
            'BC' => $timelineBc,
        ];
    }

    private function buildTimelineDA(array $allDatas): array
    {
        $tabTemp = [];

        foreach ($allDatas as $key => $data) {
            // Ajouter le statut initial si nécessaire
            if ($key === 0 && $data['statutDal'] !== StatutDaConstant::STATUT_SOUMIS_APPRO) {
                $tabTemp[] = $this->createTimelineEntry(
                    StatutDaConstant::STATUT_SOUMIS_APPRO,
                    null,
                    $data['dateDemande']
                );
            }

            // Déterminer le statut final
            $statutFinal = $this->getStatutFinal($data['statutOr'], $data['statutDal']);

            // Ajouter ou mettre à jour le statut
            $lastIndex = count($tabTemp) - 1;
            if ($lastIndex < 0 || $tabTemp[$lastIndex]['statut'] !== $statutFinal) {
                $tabTemp[] = $this->createTimelineEntry($statutFinal, $data['dateCreation'], $data['dateDemande']);
            } else {
                // Mettre à jour avec la date la plus récente
                $tabTemp[$lastIndex]['date'] = $data['dateCreation'];
            }
        }

        // Calculer les durées
        return $this->calculateDurations($tabTemp);
    }

    private function buildTimelineBC(string $numeroDa, array $lastDataDA): array
    {
        $allDatas = $this->daAfficherRepository->getAllNumCdeAndVmax($numeroDa);
        $tabTemp = [];
        $today = new \DateTime();

        foreach ($allDatas as $data) {
            $numBC = $data['numeroCde'];
            $numeroVersion = $data['numeroVersion'];

            // Récupération de toutes les dates possibles
            $dateValidationDA     = $lastDataDA['dateCreation'];
            $dateCreationBc       = $this->daAfficherRepository->getDateCreationBc($numeroDa, $numeroVersion, $numBC);       // Génération BC
            $dateValidation       = $this->daAfficherRepository->getDateValidationBc($numeroDa, $numeroVersion, $numBC);     // Validation BC
            $dateEnvoi            = $this->daAfficherRepository->getDateEnvoiFournisseur($numeroDa, $numeroVersion, $numBC); // Envoi au fournisseur
            $dateReceptionArticle = $this->daAfficherRepository->getDateReceptionArticle($numeroDa, $numeroVersion, $numBC); // Réception des articles
            $dateLivraisonArticle = $this->daAfficherRepository->getDateLivraisonArticle($numeroDa, $numeroVersion, $numBC); // Livraison des articles

            // Définition de toutes les étapes possibles
            $etapes = [
                [
                    'statut'   => $lastDataDA['statutDal'],
                    'dotClass' => $this->styleStatutDA[$lastDataDA['statutDal']],
                    'date'     => $dateValidationDA
                ],
                [
                    'statut'   => 'Génération BC',
                    'dotClass' => 'bg-bc-a-generer',
                    'date'     => $dateCreationBc
                ],
                [
                    'statut'   => 'Validation BC',
                    'dotClass' => 'bg-bc-valide',
                    'date'     => $dateValidation
                ],
                [
                    'statut'   => 'BC envoyé au fournisseur',
                    'dotClass' => 'bg-bc-envoye-au-fournisseur',
                    'date'     => $dateEnvoi
                ],
                [
                    'statut'   => 'Réception des articles',
                    'dotClass' => 'partiellement-livre',
                    'date'     => $dateReceptionArticle
                ],
                [
                    'statut'   => 'Livraison des articles',
                    'dotClass' => 'tout-livre',
                    'date'     => $dateLivraisonArticle
                ]
            ];

            // Filtrer les étapes qui ont une date
            $etapesValides = array_filter($etapes, fn($etape) => $etape['date'] !== null);

            // S'il n'y a aucune étape valide, passer au suivant
            if (empty($etapesValides)) continue;

            // Trier les étapes par date (du plus ancien au plus récent)
            usort($etapesValides, fn($a, $b) => $a['date'] <=> $b['date']);

            // Construire le tableau avec calcul automatique des durées
            $nbEtapes = count($etapesValides);
            foreach ($etapesValides as $index => $etape) {
                // Déterminer la date de fin pour le calcul
                // Si c'est la dernière étape, pas de durée
                // Sinon, la date de fin est la date de l'étape suivante
                $isLastStep = ($index === $nbEtapes - 1);
                $dateFinCalcul = !$isLastStep ? $etapesValides[$index + 1]['date'] : ($dateLivraisonArticle ? NULL : $today);

                $tabTemp[$numBC][] = [
                    'statut'   => $etape['statut'],
                    'dotClass' => $etape['dotClass'],
                    'date'     => $etape['date']->format('d/m/Y'),
                    'nbrJours' => $dateFinCalcul
                        ? $this->formatDuration($this->differenceJoursOuvrables($etape['date'], $dateFinCalcul))
                        : ''
                ];
            }

            // Ajouter la date actuelle si le processus n'est pas terminé
            if (!$dateLivraisonArticle) $tabTemp[$numBC][] = $this->createCurrentDateEntry($today);
        }

        return $tabTemp;
    }

    private function getStatutFinal(?string $statutOr, string $statutDal): string
    {
        $estDaValide = ($statutOr === StatutDaConstant::STATUT_DW_A_MODIFIER &&
            $statutDal === StatutDaConstant::STATUT_EN_COURS_CREATION) ||
            $statutDal === StatutDaConstant::STATUT_CLOTUREE;

        return $estDaValide ? StatutDaConstant::STATUT_VALIDE : $statutDal;
    }

    private function createTimelineEntry(string $statut, ?\DateTime $date, ?\DateTime $dateDemande): array
    {
        return [
            'statut'   => $statut,
            'dotClass' => $this->styleStatutDA[$statut],
            'date'     => $statut === DemandeAppro::STATUT_SOUMIS_APPRO ? $dateDemande : $date,
            'nbrJours' => 0,
        ];
    }

    private function createCurrentDateEntry(): array
    {
        return [
            'statut'   => '',
            'dotClass' => '',
            'date'     => 'Aujourd’hui',
            'nbrJours' => '',
        ];
    }

    private function calculateDurations(array $timeline): array
    {
        for ($i = 0; $i < count($timeline); $i++) {
            if ($i < count($timeline) - 1) {
                $nbrJours = $this->differenceJoursOuvrables(
                    $timeline[$i + 1]['date'],
                    $timeline[$i]['date']
                );
                $timeline[$i]['nbrJours'] = $this->formatDuration($nbrJours);
            } else {
                $timeline[$i]['nbrJours'] = '';
            }
            $timeline[$i]['date'] = $timeline[$i]['date']->format('d/m/Y');
        }

        return $timeline;
    }

    private function formatDuration(int $nbrJours): string
    {
        return $nbrJours === 0 ? "< 1 jour" : $nbrJours . " jour(s)";
    }
}
