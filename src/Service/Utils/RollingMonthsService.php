<?php

namespace App\Service\Utils;

use DateTime;

/**
 * Service pour gérer les périodes de mois glissants
 */
class RollingMonthsService
{
    private const MONTHS_FR = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

    private const PERIOD_TYPES = [ // exemple si l'année en cours est 2024 et le mois en cours est novembre
        'NEXT_3_MONTHS' => 3,// Les 3 prochains mois (exemple: Fev. 2024 - Jan. 2025)
        'NEXT_6_MONTHS' => 6,// Les 6 prochains mois (exemple: Mai. 2024 - Avrl. 2025)
        //----------------------------------------
        'CURRENT_YEAR' => 9, // l'année en cours (exemple : 2024 => Janv. 2024 - Déc. 2024)
        'NEXT_YEAR' => 11, // l'année prochaine  (exemple : 2025 => Janv. 2025 - Déc. 2025)
        'PREVIOUS_YEAR' => 14, // L'année précédente (exemple: 2023 => Janv. 2024 - Déc. 2024)
        //----------------------------------
        'NEXT_12_MONTHS' => 12, // Les 12 prochains mois (exemple : Dec. 2024 - Nov. 2025)
        'PREVIOUS_12_MONTHS' => 13, // Les 12 derniers mois (exemple: DEC. 2023 - Nov. 2024)
    ];

    /**
     * Génère une liste de mois selon le type de période
     *
     * @param string $periodType Type de période (constantes PERIOD_TYPES)
     * @param DateTime|null $referenceDate Date de référence (aujourd'hui par défaut)
     * @return array Liste des mois avec leurs métadonnées
     */
    public function getMonthsForPeriod(string $periodType, ?DateTime $referenceDate = null): array
    {
        $referenceDate = $referenceDate ?? new DateTime();
        $currentMonth = (int)$referenceDate->format('n') - 1; // Index 0-11
        $currentYear = (int)$referenceDate->format('Y');

        $selectedOption = self::PERIOD_TYPES[$periodType] ?? null;

        if ($selectedOption === null) {
            throw new \InvalidArgumentException("Type de période invalide: {$periodType}");
        }

        return $this->buildMonthsList($currentMonth, $currentYear, $selectedOption);
    }

    /**
     * Filtre des données par période de mois
     *
     * @param array $data Données à filtrer
     * @param string $periodType Type de période
     * @param callable $monthExtractor Fonction pour extraire les mois d'un élément
     * @param DateTime|null $referenceDate Date de référence
     * @return array Données filtrées avec les mois sélectionnés
     */
    public function filterDataByPeriod(
        array $data,
        string $periodType,
        callable $monthExtractor,
        ?DateTime $referenceDate = null
    ): array {
        $selectedMonths = $this->getMonthsForPeriod($periodType, $referenceDate);
        $monthKeys = array_column($selectedMonths, 'key');

        $filteredData = array_map(function ($item) use ($monthExtractor, $monthKeys) {
            $monthDetails = $monthExtractor($item);

            $filteredMonths = array_values(array_filter(array_map(
                function ($detail) use ($monthKeys) {
                    return $this->filterMonthDetail($detail, $monthKeys);
                },
                $monthDetails
            )));

            return array_merge(
                $this->extractItemData($item),
                ['filteredMonths' => $filteredMonths]
            );
        }, $data);

        return [
            'data' => $filteredData,
            'months' => $selectedMonths,
        ];
    }

    /**
     * Construit la liste des mois selon l'option sélectionnée
     *
     * @param int $currentMonth Index du mois actuel (0-11)
     * @param int $currentYear Année actuelle
     * @param int $selectedOption Option de période
     * @return array Liste des mois
     */
    private function buildMonthsList(int $currentMonth, int $currentYear, int $selectedOption): array
    {
        $selectedMonths = [];

        switch ($selectedOption) {
            case 3: // 3 mois suivants + complétion à 12
            case 6: // 6 mois suivants + complétion à 12
                $monthsCount = $selectedOption === 3 ? 4 : 7;

                // Ajouter les mois suivants
                for ($i = 0; $i < $monthsCount; $i++) {
                    $selectedMonths[] = $this->generateMonthData($currentMonth, $currentYear, $i);
                }

                // Compléter avec les mois précédents
                for ($i = -1; count($selectedMonths) < 12; $i--) {
                    array_unshift($selectedMonths, $this->generateMonthData($currentMonth, $currentYear, $i));
                }
                break;

            case 9: // Année en cours
                $selectedMonths = $this->generateYearMonths($currentYear);
                break;

            case 11: // Année suivante
                $selectedMonths = $this->generateYearMonths($currentYear + 1);
                break;

            case 12: // 12 mois suivants (à partir du mois courant)
                for ($i = 0; $i < 12; $i++) {
                    $selectedMonths[] = $this->generateMonthData($currentMonth, $currentYear, $i);
                }
                break;

            case 13: // 12 mois précédents (jusqu'au mois courant)
                for ($i = -11; $i <= 0; $i++) {
                    $selectedMonths[] = $this->generateMonthData($currentMonth, $currentYear, $i);
                }
                break;

            case 14: // Année précédente
                $selectedMonths = $this->generateYearMonths($currentYear - 1);
                break;
        }

        return $selectedMonths;
    }

    /**
     * Génère les données d'un mois avec offset
     *
     * @param int $currentMonth Index du mois actuel (0-11)
     * @param int $currentYear Année actuelle
     * @param int $offset Décalage en mois
     * @return array Données du mois
     */
    private function generateMonthData(int $currentMonth, int $currentYear, int $offset): array
    {
        $totalMonths = $currentMonth + $offset;
        $monthIndex = ($totalMonths % 12 + 12) % 12;
        $year = $currentYear + intdiv($totalMonths, 12);

        if ($totalMonths < 0 && $monthIndex > $currentMonth) {
            $year--;
        }

        return [
            'month' => self::MONTHS_FR[$monthIndex],
            'monthIndex' => $monthIndex,
            'year' => $year,
            'key' => sprintf('%04d-%02d', $year, $monthIndex + 1),
            'label' => self::MONTHS_FR[$monthIndex] . ' ' . $year,
        ];
    }

    /**
     * Génère tous les mois d'une année
     *
     * @param int $year Année
     * @return array Liste des mois
     */
    private function generateYearMonths(int $year): array
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $months[] = [
                'month' => self::MONTHS_FR[$i],
                'monthIndex' => $i,
                'year' => $year,
                'key' => sprintf('%04d-%02d', $year, $i + 1),
                'label' => self::MONTHS_FR[$i] . ' ' . $year,
            ];
        }
        return $months;
    }

    /**
     * Filtre un détail de mois
     *
     * @param mixed $detail Détail du mois
     * @param array $monthKeys Clés des mois à conserver
     * @return array|null Détail filtré ou null
     */
    private function filterMonthDetail($detail, array $monthKeys): ?array
    {
        if (!is_array($detail) || !isset($detail['mois'])) {
            return null;
        }

        $monthIndex = (int)($detail['mois'] ?? 0) - 1;
        $year = $detail['annee'] ?? '';
        $monthKey = sprintf('%04d-%02d', $year, $monthIndex + 1);

        if (!in_array($monthKey, $monthKeys, true)) {
            return null;
        }

        return [
            'month' => self::MONTHS_FR[$monthIndex] ?? '',
            'year' => $year,
            'monthKey' => $monthKey,
            'details' => $detail,
        ];
    }

    /**
     * Extrait les données d'un élément (à surcharger selon le besoin)
     *
     * @param mixed $item Élément
     * @return array Données extraites
     */
    protected function extractItemData($item): array
    {
        // Implémentation par défaut - peut être surchargée
        if (is_object($item) && method_exists($item, 'toArray')) {
            return $item->toArray();
        }

        if (is_array($item)) {
            return $item;
        }

        return ['item' => $item];
    }

    /**
     * Retourne les types de périodes disponibles
     *
     * @return array Types de périodes
     */
    public static function getAvailablePeriodTypes(): array
    {
        return array_keys(self::PERIOD_TYPES);
    }

    /**
     * Retourne les noms de mois en français
     *
     * @return array Noms des mois
     */
    public static function getMonthNames(): array
    {
        return self::MONTHS_FR;
    }
}
