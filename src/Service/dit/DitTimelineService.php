<?php

namespace App\Service\dit;

use App\Entity\dit\DemandeIntervention;
use App\Traits\JoursOuvrablesTrait;

class DitTimelineService
{
    use JoursOuvrablesTrait;

    private const CSS_CLASS_MAP = [
        'Demande d’intervention'    => 'bg-demande-achat',
        'Validation'                => 'bg-en-cours-proposition',
        'Devis validé'              => 'bg-proposition-achat',
        'Clôturée'                  => 'bg-bon-achat-valide',
    ];

    /**
     * @param DemandeIntervention $dit
     *
     * @return array{numeroDit:string,DIT:array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>}
     */
    public function getTimelineData(DemandeIntervention $dit): array
    {
        $etapes = array_filter([
            $this->creerEtape('Demande d’intervention', $dit->getDateDemande()),
            $this->creerEtape('Validation', $dit->getDateValidation()),
            $this->creerEtapeOr($dit),
            $this->creerEtape('Devis validé', $dit->getDateValidationDevis()),
            $this->creerEtape('Clôturée', $dit->getDateCloture()),
        ], fn($etape) => $etape !== null);

        if (empty($etapes)) return ['numeroDit' => $dit->getNumeroDemandeIntervention(), 'DIT' => []];

        return [
            'numeroDit' => $dit->getNumeroDemandeIntervention(),
            'DIT'       => $this->construireEtapesAvecDurees($etapes, $dit->getDateCloture() !== null),
        ];
    }

    /**
     * @param string $statut
     * @param \DateTime|null $date
     *
     * @return array{statut:string,dotClass:string,date:\DateTime}|null
     */
    private function creerEtape(string $statut, ?\DateTime $date): ?array
    {
        if ($date === null) return null;

        return [
            'statut'   => $statut,
            'dotClass' => self::CSS_CLASS_MAP[$statut] ?? '',
            'date'     => $date,
        ];
    }

    /**
     * @param DemandeIntervention $dit
     *
     * @return array{statut:string,dotClass:string,date:\DateTime}|null
     */
    private function creerEtapeOr(DemandeIntervention $dit): ?array
    {
        if ($dit->getDateValidationOr() === null) return null;

        return [
            'statut'   => 'OR - ' . $dit->getStatutOr(),
            'dotClass' => 'bg-demande-devis',
            'date'     => $dit->getDateValidationOr(),
        ];
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

        if (!$isComplete) {
            $timeline[] = ['statut' => '', 'dotClass' => '', 'date' => 'Aujourd’hui', 'nbrJours' => ''];
        }

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
