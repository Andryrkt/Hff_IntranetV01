<?php

namespace App\Service\dit;

use App\Model\dit\DitTimelineModel;
use App\Traits\JoursOuvrablesTrait;

class DitTimelineService
{
    use JoursOuvrablesTrait;

    private const LABELS_OR = [
        'date_soumission_or'        => "OR - Soumis à validation",
        'date_demande_modification' => "OR - Demande de modification",
        'date_validation_devis'     => "OR - Validation devis",
        'date_validation_ca'        => "OR - Validé Chef Atelier",
        'date_validation_dt'        => "OR - Validé Directeur Technique",
        'date_validation_ci'        => "OR - Validé Client Interne",
        'date_validation_fleet_m'   => "OR - Validé Fleet Manager",
        'date_validation_dg'        => "OR - Validé DG",
        'date_validation_ser_em'    => "OR - Validé service émetteur",
        'date_validation_ser_des'   => "OR - Validé service destinataire",
        'date_validation_compta'    => "OR - Validé compta",
        'date_validation_info'      => "OR - Validé info",
        'date_validation_mag'       => "OR - Validé magasin",
        'date_validation_finale_or' => "OR - Validation finale",
        'date_validation_section'   => "OR - Validation section",
    ];

    private const CSS_CLASS_MAP = [
        'date_soumission_or'        => "bg-or-soumis-validation",
        'date_demande_modification' => "bg-modif-demande-client",
        'date_validation_devis'     => "bg-info",
        'date_validation_ca'        => "bg-or-valider-ca",
        'date_validation_dt'        => "bg-or-valider-dt",
        'date_validation_ci'        => "bg-or-valider-ca",
        'date_validation_fleet_m'   => "bg-or-valider-dt",
        'date_validation_dg'        => "bg-or-valider-ca",
        'date_validation_ser_em'    => "bg-or-valider-dt",
        'date_validation_ser_des'   => "bg-or-valider-ca",
        'date_validation_compta'    => "bg-or-valider-dt",
        'date_validation_info'      => "bg-or-valider-ca",
        'date_validation_mag'       => "bg-or-valider-dt",
        'date_validation_finale_or' => "bg-or-valide",
        'date_validation_section'   => "bg-or-valider-ca",
    ];

    private DitTimelineModel $ditTimelineModel;

    public function __construct()
    {
        $this->ditTimelineModel = new DitTimelineModel;
    }

    /**
     * @param string $numDit
     *
     * @return array{numeroDit:string,DIT:array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>,OR:array<int,array{numeroOr:string,etapes:array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>}>}
     */
    public function getTimelineData(string $numDit): array
    {
        $allDatas = $this->ditTimelineModel->fetchTimelineDit($numDit);
        if (empty($allDatas)) return ['numeroDit' => $numDit, 'DIT' => [], 'OR' => []];

        $timelineDit = $this->buildTimelineDit($allDatas[0]);
        $lastDataDit = empty($timelineDit) ? null : end($timelineDit);
        $timelineOr  = $lastDataDit !== null ? $this->buildTimelineOR($allDatas, $lastDataDit) : [];

        // Aucun OR n'a encore progressé : le DIT reste le dernier jalon connu
        if (empty($timelineOr) && $lastDataDit !== null) {
            $timelineDit = $this->cloturerAvecAujourdhui($timelineDit);
        }

        return [
            'numeroDit' => $numDit,
            'DIT'       => $timelineDit,
            'OR'        => $timelineOr,
        ];
    }

    /**
     * @param array{date_demande:string,statut_debut:string} $premiereLigne
     *
     * @return array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>
     */
    private function buildTimelineDit(array $premiereLigne): array
    {
        $statut = $premiereLigne['statut_debut'];
        $etape  = $this->creerEtape("DIT - $statut", $premiereLigne['date_demande'], str_replace(" ", "_", strtolower($statut)));
        if ($etape === null) return [];

        return $this->construireEtapesAvecDurees([$etape], true);
    }

    /**
     * @param array<int,array<string,string|null>> $allDatas
     * @param array{statut:string,dotClass:string,date:string,nbrJours:string} $lastDataDit Dernier jalon connu du DIT, point de départ de chaque bloc OR
     *
     * @return array<int,array{numeroOr:string,etapes:array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>}>
     */
    private function buildTimelineOR(array $allDatas, array $lastDataDit): array
    {
        $blocs = [];

        usort($allDatas, fn($a, $b) => ($a['num_version_or'] ?? 0) <=> ($b['num_version_or'] ?? 0));

        $etapeDit = [
            'statut'   => $lastDataDit['statut'],
            'dotClass' => $lastDataDit['dotClass'],
            'date'     => \DateTime::createFromFormat('d/m/Y', $lastDataDit['date']),
        ];

        foreach ($allDatas as $ligne) {
            $etapesOr = array_filter(array_map(
                fn($champ, $libelle) => $this->creerEtape($libelle, $ligne[$champ] ?? null, self::CSS_CLASS_MAP[$champ] ?? ''),
                array_keys(self::LABELS_OR),
                self::LABELS_OR
            ));

            // Si aucune date d'OR n'est renseignée, on ne construit pas de bloc pour cette ligne
            if (empty($etapesOr)) continue;

            $blocs[] = [
                'numeroOr' => $ligne['dodr_numero_or'] . '-' . $ligne['num_version_or'],
                'etapes'   => $this->construireEtapesAvecDurees(array_merge([$etapeDit], $etapesOr), false),
            ];
        }

        return $blocs;
    }

    /**
     * @param string $statut
     * @param string|null $date
     * @param string $dotClass
     *
     * @return array{statut:string,dotClass:string,date:\DateTime}|null
     */
    private function creerEtape(string $statut, ?string $date, string $dotClass = ''): ?array
    {
        if (empty($date)) return null;

        return [
            'statut'   => $statut,
            'dotClass' => $dotClass,
            'date'     => new \DateTime($date),
        ];
    }

    /**
     * @return array{statut:string,dotClass:string,date:string,nbrJours:string}
     */
    private function createCurrentDateEntry(): array
    {
        return [
            'statut'   => '',
            'dotClass' => '',
            'date'     => 'Aujourd’hui',
            'nbrJours' => '',
        ];
    }

    /**
     * Clôture une timeline non terminée en calculant la durée jusqu'à aujourd'hui et en ajoutant une entrée "Aujourd'hui".
     *
     * @param array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}> $timeline
     *
     * @return array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>
     */
    private function cloturerAvecAujourdhui(array $timeline): array
    {
        $lastEntry = end($timeline);
        $nbrJours  = $this->formatDuration(
            $this->differenceJoursOuvrables(
                \DateTime::createFromFormat('d/m/Y', $lastEntry['date']),
                new \DateTime()
            )
        );

        $timeline[array_key_last($timeline)]['nbrJours'] = $nbrJours;
        $timeline[] = $this->createCurrentDateEntry();

        return $timeline;
    }

    /**
     * Trie les étapes par date croissante et calcule la durée entre étapes consécutives (jours ouvrables), puis formate leur date.
     *
     * @param array<int,array{statut:string,dotClass:string,date:\DateTime}> $etapes
     * @param bool $isComplete Si $isComplete est faux, la dernière étape est comptée jusqu'à aujourd'hui et une entrée "Aujourd'hui" est ajoutée.
     *
     * @return array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>
     */
    private function construireEtapesAvecDurees(array $etapes, bool $isComplete): array
    {
        $etapes = array_values($etapes);
        $nbEtapes = count($etapes);
        $timeline = [];
        usort($etapes, fn($a, $b) => $a['date'] <=> $b['date']);

        foreach ($etapes as $index => $etape) {
            $isLastStep = $index === $nbEtapes - 1;
            $dateFin = !$isLastStep ? $etapes[$index + 1]['date'] : ($isComplete ? null : new \DateTime());

            $timeline[] = [
                'statut'   => $etape['statut'],
                'dotClass' => $etape['dotClass'],
                'date'     => $etape['date']->format('d/m/Y'),
                'nbrJours' => $dateFin
                    ? $this->formatDuration($this->differenceJoursOuvrables($etape['date'], $dateFin))
                    : '',
            ];
        }

        if (!$isComplete) $timeline[] = $this->createCurrentDateEntry();

        return $timeline;
    }

    /**
     * @param int $nbrJours
     *
     * @return string
     */
    private function formatDuration(int $nbrJours): string
    {
        return $nbrJours === 0 ? "< 1 jour" : $nbrJours . " jour(s)";
    }
}
