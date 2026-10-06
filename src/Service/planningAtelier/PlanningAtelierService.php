<?php

namespace App\Service\planningAtelier;

use App\Dto\PlanningAtelier\PlanningAtelierDto;
use App\Dto\PlanningAtelier\PresenceDto;
use App\Mapper\PlanningAtelier\PlanningAtelierMapper;
use DateTimeImmutable;

class PlanningAtelierService
{

    private PlanningAtelierMapper $mapper;

    public function __construct()
    {
        $this->mapper = new PlanningAtelierMapper();
    }

    /**
     * @return array{planning: array<string, PlanningAtelierDto>, dates: array<\DateTime>, filteredDates: array<string>}
     * @throws \Exception
     */
    public function process(array $dbResults, ?string $startDate = null, ?string $endDate = null): array
    {
        $planning = [];
        foreach ($dbResults as $item) {
            $currDto = $this->mapper->mapToDto($item);
            $key = $currDto->getKey();

            if (!isset($planning[$key])) {
                $planning[$key] = $currDto;
            }

            $planning[$key]->nbTotalJour += $currDto->nbJour;

            if (isset($item['hpointee']))
                $planning[$key]->totalHeures += (float) $item['hpointee'];

            $debut = new \DateTime($item['date_debut']);
            $dateStr = $debut->format('Y-m-d');

            if (!isset($planning[$key]->presences[$dateStr])) {
                $planning[$key]->presences[$dateStr] = new PresenceDto();
            }

            $this->applyPresenceCalculation($planning[$key]->presences[$dateStr], $item, $dateStr);
        }

        $calendar = $this->generateCalendarPeriod($startDate, $endDate);

        return [
            'planning' => $planning,
            'dates' => $calendar['dates'],
            'filteredDates' => $calendar['filteredDates'],
        ];
    }

    /**
     * @param array<PlanningAtelierDto> $data
     * @param array<DateTimeImmutable> $dates
     * @return array
     */
    public function processExcelData(array $data, array $dates): array
    {
        $results = [];

        foreach ($data as $item) {
            $row = [
                $item->agenceEm,
                $item->section,
                $item->intitule,
                $item->numeroOr,
                $item->itv,
                $item->ressource,
                $item->nbTotalJour,
                $item->totalHeures,
            ];

            foreach ($dates as $date) {
                $dateStr = $date->format('Y-m-d');
                if (isset($item->presences[$dateStr])) {
                    $row[] = $item->presences[$dateStr]->matin ? 'X' : '';
                    $row[] = $item->presences[$dateStr]->apm ? 'X' : '';
                } else {
                    $row[] = '';
                    $row[] = '';
                }
            }

            $results[] = $row;
        }

        [$headerRow1, $headerRow2] = $this->generateExcelRows($dates);

        array_unshift($results, $headerRow1);
        array_unshift($results, $headerRow2);

        return $results;
    }

    private function applyPresenceCalculation(PresenceDto $presence, array $item, string $dateStr)
    {
        $debut = new \DateTime($item['date_debut']);
        $fin = new \DateTime($item['date_fin']);

        $matin_debut = new \DateTime("$dateStr 08:00:00");
        $matin_fin   = new \DateTime("$dateStr 12:00:00");
        $aprem_debut = new \DateTime("$dateStr 13:30:00");
        $aprem_fin   = new \DateTime("$dateStr 17:30:00");

        if ($fin >= $matin_debut && $debut < $matin_fin) {
            $presence->matin = true;
        }
        if ($fin >= $aprem_debut && $debut < $aprem_fin) {
            $presence->apm = true;
        }

        // Résolution du cas limite (Edge-case des lignes sans pointage via LEFT JOIN)
        if (!isset($item['hpointee']) || empty($item['hpointee_debut']) || empty($item['hpointee_fin'])) {
            return;
        }

        $hpointee = (float) $item['hpointee'];
        $hdebut = new \DateTime($item['hpointee_debut']);
        $hfin = new \DateTime($item['hpointee_fin']);

        if ($presence->heure === null) {
            $presence->heure = 0.0;
        }
        $presence->heure += $hpointee;

        $overlapMatin = max(0, min($hfin->getTimestamp(), $matin_fin->getTimestamp()) - max($hdebut->getTimestamp(), $matin_debut->getTimestamp()));
        $overlapAprem = max(0, min($hfin->getTimestamp(), $aprem_fin->getTimestamp()) - max($hdebut->getTimestamp(), $aprem_debut->getTimestamp()));
        $totalOverlap = $overlapMatin + $overlapAprem;

        if ($totalOverlap > 0) {
            $presence->hmtn = ($presence->hmtn ?? 0.0) + ($hpointee * ($overlapMatin / $totalOverlap));
            $presence->hapm = ($presence->hapm ?? 0.0) + ($hpointee * ($overlapAprem / $totalOverlap));
        } else {
            $midBreak = new \DateTime("$dateStr 12:00:01");
            if ($hdebut < $midBreak) {
                $presence->hmtn = ($presence->hmtn ?? 0.0) + $hpointee;
            } else {
                $presence->hapm = ($presence->hapm ?? 0.0) + $hpointee;
            }
        }
    }

    private function generateCalendarPeriod(?string $startStr, ?string $endStr): array
    {
        $dates = [];
        $filteredDates = [];

        if (!$startStr || !$endStr) {
            return ['dates' => $dates, 'filteredDates' => $filteredDates];
        }

        $start = new \DateTime($startStr);
        $end = new \DateTime($endStr);
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, (clone $end)->modify('+1 day'));

        foreach ($period as $date) {
            $dates[] = $date;
            $filteredDates[] = $date->format('Y-m-d');
        }

        return ['dates' => $dates, 'filteredDates' => $filteredDates];
    }

    /**
     * @param array<DateTimeImmutable> $dates
     * @return array
     */
    private function generateExcelRows(array $dates): array
    {
        $fixedHeaders = ['Agence Travaux', 'Section', 'Intitulé Travaux', 'numOR', 'Itv', 'Ressource', 'Nb jour', 'Total heures'];
        $hr1 = $fixedHeaders;
        $hr2 = array_fill(0, count($fixedHeaders), '');

        foreach ($dates as $date) {
            $label = $date->format('l d/m');
            $hr1[] = $label;
            $hr1[] = '';
            $hr2[] = 'mtn';
            $hr2[] = 'apm';
        }

        return [$hr1, $hr2];
    }
}
