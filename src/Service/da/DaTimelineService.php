<?php

namespace App\Service\da;

use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DaAfficherRepository;
use App\Traits\JoursOuvrablesTrait;

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
            DemandeAppro::STATUT_VALIDE               => 'bg-bon-achat-valide',
            DemandeAppro::STATUT_CLOTUREE             => 'bg-bon-achat-valide',
            DemandeAppro::STATUT_TERMINER             => 'bg-primary text-white',
            DemandeAppro::STATUT_SOUMIS_ATE           => 'bg-proposition-achat',
            DemandeAppro::STATUT_DW_A_VALIDE          => 'bg-soumis-validation',
            DemandeAppro::STATUT_SOUMIS_APPRO         => 'bg-demande-achat',
            DemandeAppro::STATUT_REFUSE_APPRO         => 'bg-refuse-appro',
            DemandeAppro::STATUT_DEMANDE_DEVIS        => 'bg-demande-devis',
            DemandeAppro::STATUT_DEVIS_A_RELANCER     => 'bg-devis-a-relancer',
            DemandeAppro::STATUT_EN_COURS_CREATION    => 'bg-en-cours-creation',
            DemandeAppro::STATUT_AUTORISER_EMETTEUR   => 'bg-creation-demande-initiale',
            DemandeAppro::STATUT_EN_COURS_PROPOSITION => 'bg-en-cours-proposition',
        ];
    }

    public function getTimelineData(string $numeroDa): array
    {
        $allDatas = $this->daAfficherRepository->getTimelineData($numeroDa);
        if (empty($allDatas)) return ['DA' => [], 'BC' => []];

        $this->initStyleStatuts();

        return [
            'DA' => $this->buildTimelineDA($allDatas),
            'BC' => $this->buildTimelineBC($numeroDa, $allDatas[count($allDatas) - 1]['dateCreation']),
        ];
    }

    private function buildTimelineDA(array $allDatas): array
    {
        $tabTemp = [];

        foreach ($allDatas as $key => $data) {
            // Ajouter le statut initial si nécessaire
            if ($key === 0 && $data['statutDal'] !== DemandeAppro::STATUT_SOUMIS_APPRO) {
                $tabTemp[] = $this->createTimelineEntry(
                    DemandeAppro::STATUT_SOUMIS_APPRO,
                    $data['dateDemande']
                );
            }

            // Déterminer le statut final
            $statutFinal = $this->getStatutFinal($data['statutOr'], $data['statutDal']);

            // Ajouter ou mettre à jour le statut
            $lastIndex = count($tabTemp) - 1;
            if ($lastIndex < 0 || $tabTemp[$lastIndex]['statut'] !== $statutFinal) {
                $tabTemp[] = $this->createTimelineEntry($statutFinal, $data['dateCreation']);
            } else {
                // Mettre à jour avec la date la plus récente
                $tabTemp[$lastIndex]['date'] = $data['dateCreation'];
            }
        }

        // Calculer les durées
        return $this->calculateDurations($tabTemp);
    }

    private function buildTimelineBC(string $numeroDa, \DateTime $lastDateDA): array
    {
        $allDatas = $this->daAfficherRepository->getTimelineDataForBC($numeroDa);
        $tabTemp = [];
        $today = new \DateTime();

        foreach ($allDatas as $data) {
            $numBC = $data['numeroCde'];
            $dateCreationBc = $data['dateCreationBc'];

            if (!$dateCreationBc) continue;

            // Lien DA → BC
            $tabTemp[$numBC][] = [
                'statut'   => '',
                'dotClass' => '',
                'date'     => '',
                'nbrJours' => $this->formatDuration(
                    $this->differenceJoursOuvrables($lastDateDA, $dateCreationBc)
                ),
            ];

            // Génération BC
            $dateValidation = $data['dateValidationBc'];
            $dateEnvoi = $data['dateEnvoiFournisseur'];

            $tabTemp[$numBC][] = [
                'statut'   => 'Génération BC',
                'dotClass' => 'bg-bc-a-generer',
                'date'     => $dateCreationBc->format('d/m/Y'),
                'nbrJours' => $this->formatDuration(
                    $this->differenceJoursOuvrables(
                        $dateCreationBc,
                        $dateValidation ?? $today
                    )
                ),
            ];

            if (!$dateValidation) {
                $tabTemp[$numBC][] = $this->createCurrentDateEntry($today);
                continue;
            }

            // Validation BC
            $tabTemp[$numBC][] = [
                'statut'   => 'Validation BC',
                'dotClass' => 'bg-bc-valide',
                'date'     => $dateValidation->format('d/m/Y'),
                'nbrJours' => $this->formatDuration(
                    $this->differenceJoursOuvrables(
                        $dateValidation,
                        $dateEnvoi ?? $today
                    )
                ),
            ];

            if ($dateEnvoi) {
                $tabTemp[$numBC][] = [
                    'statut'   => 'BC envoyé au fournisseur',
                    'dotClass' => 'bg-bc-envoye-au-fournisseur',
                    'date'     => $dateEnvoi->format('d/m/Y'),
                    'nbrJours' => '',
                ];
            } else {
                $tabTemp[$numBC][] = $this->createCurrentDateEntry($today);
            }
        }

        return $tabTemp;
    }

    private function getStatutFinal(?string $statutOr, string $statutDal): string
    {
        $estDaValide = ($statutOr === DemandeAppro::STATUT_DW_A_MODIFIER &&
            $statutDal === DemandeAppro::STATUT_EN_COURS_CREATION) ||
            $statutDal === DemandeAppro::STATUT_CLOTUREE;

        return $estDaValide ? DemandeAppro::STATUT_VALIDE : $statutDal;
    }

    private function createTimelineEntry(string $statut, \DateTime $date): array
    {
        return [
            'statut'   => $statut,
            'dotClass' => $this->styleStatutDA[$statut],
            'date'     => $date,
            'nbrJours' => 0,
        ];
    }

    private function createCurrentDateEntry(\DateTime $today): array
    {
        return [
            'statut'   => 'Date du jour',
            'dotClass' => '',
            'date'     => $today->format('d/m/Y'),
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
