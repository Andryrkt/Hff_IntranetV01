<?php

namespace App\Service\autres;

class RecapitulationService
{
    /**
     * Génère un récapitulatif basé sur les données et une configuration de clés.
     *
     * @param array $data
     * @param array $keys
     * @return array
     */
    public function generateRecap(array $data, array $keys): array
    {
        $recap = [];
        foreach ($data as $item) {
            $row = [];
            foreach ($keys as $key => $method) {
                $row[$key] = $item->{$method}();
            }
            $recap[] = $row;
        }
        return $recap;
    }

    /**
     * Calcule les totaux pour les colonnes spécifiées dans les clés.
     *
     * @param array $recap
     * @param array $keys
     * @return array
     */
    public function calculateTotals(array $recap, array $keys): array
    {
        $totals = array_fill_keys(array_keys($keys), 0);
        foreach ($recap as $row) {
            foreach ($keys as $key => $method) {
                $totals[$key] += $row[$key] ?? 0;
            }
        }
        return $totals;
    }
}
