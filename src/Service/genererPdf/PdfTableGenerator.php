<?php

namespace App\Service\genererPdf;

class PdfTableGenerator
{
    public function generateTable(array $headerConfig, array $rows, array $totals, bool $expre = false)
    {
        $html = '<table border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px;">';
        $html .= $this->generateHeader($headerConfig);
        $html .= $this->generateBody($headerConfig, $rows, $expre);
        $html .= $this->generateFooter($headerConfig, $totals);
        $html .= '</table>';
        return $html;
    }

    private function generateHeader(array $headerConfig)
    {
        $html = '<thead><tr style="background-color: #D3D3D3;">';
        foreach ($headerConfig as $config) {
            $html .= '<th style="width: ' . $config['width'] . 'px; ' . $config['style'] . '">' . $config['label'] . '</th>';
        }
        $html .= '</tr></thead>';
        return $html;
    }

    /**
     * Undocumented function
     *
     * @param array $headerConfig
     * @param array $rows
     * @param boolean $expre
     * @return void
     */
    private function generateBody(array $headerConfig, array $rows, bool $expre = false)
    {
        $html = '<tbody>';
        // Vérifier si le tableau $rows est vide

        if (empty($rows) && !$expre) {
            $html .= '<tr><td colspan="' . count($headerConfig) . '" style="text-align: center; font-weight: bold;">N/A</td></tr>';
            $html .= '</tbody>';
            return $html;
        }

        foreach ($rows as $row) {

            // Vérifier si tous les montants sont égaux à 0
            $montantsKeys = array_filter(array_keys($row), fn($key) => stripos($key, 'mtt') !== false);
            $allMontantsZero = array_reduce($montantsKeys, fn($acc, $key) => $acc && ((float) $row[$key] === 0), false);

            if ($allMontantsZero) {
                // Afficher "N/A" si tous les montants sont égaux à 0
                $html .= '<tr><td colspan="' . count($headerConfig) . '" style="text-align: center; font-weight: bold;">N/A</td></tr>';
                continue;
            }

            $html .= '<tr>';
            foreach ($headerConfig as $config) {
                $key = $config['key'];
                $value = $row[$key] ?? '';
                $style = str_replace('font-weight: bold;', '', $config['style']) . $this->getDynamicStyle($key, $value);
                $value = $this->formatValue($key, $value);

                $html .= '<td style="width: ' . $config['width'] . 'px; ' . $style . '">' . $value . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        return $html;
    }

    private function generateFooter(array $headerConfig, array $totals)
    {
        $html = '<tfoot><tr style="background-color: #D3D3D3;">';
        foreach ($headerConfig as $config) {
            $key = $config['key'];
            $style = $config['style'];
            $value = $totals[$key] ?? '';

            if (!empty($value)) {
                // Formater uniquement si la valeur existe
                $value = $this->formatValue($key, $value);
            }

            $html .= '<th style="width: ' . $config['width'] . 'px; ' . $style . '">' . $value . '</th>';
        }
        $html .= '</tr></tfoot>';
        return $html;
    }


    private function getDynamicStyle($key, $value)
    {
        $styles = '';
        if ($key === 'statut') {
            switch ($value) {
                case 'Supp':
                    $styles .= 'background-color: #FF0000;';
                    break;
                case 'Modif':
                    $styles .= 'background-color: #FFFF00;';
                    break;
                case 'Nouv':
                    $styles .= 'background-color: #00FF00;';
                    break;
            }
        }
        return $styles;
    }

    /**
     * Méthode qui formate les valeurs (nombre ou date) au format approprié.
     * Pour les montants, la clé du tableau doit contenir "mtt".
     * Pour les dates, la clé du tableau doit contenir "date".
     *
     * @param string $key La clé du tableau.
     * @param mixed $value La valeur associée à la clé.
     * @return string La valeur formatée.
     */
    private function formatValue(string $key, $value): string
    {
        // Vérifier si la clé concerne un montant
        if (in_array($key, ['mttTotal', 'mttPieces', 'mttMo', 'mttSt', 'mttLub', 'mttAutres', 'mttTotalAv', 'mttTotalAp', 'pu1', 'pu2', 'pu3']) || stripos($key, 'mtt') !== false) {
            // Vérifier si la valeur est un nombre
            if (is_numeric($value)) {
                return number_format((float) $value, 2, ',', '.');
            }
            return '0.00'; // Retourner un montant par défaut si ce n'est pas un nombre
        }

        // Vérifier si la clé concerne une date
        if (stripos($key, 'date') !== false) {
            // Vérifier si la valeur est une chaîne et non égale à '-'
            if (is_string($value) && $value !== '-') {
                try {
                    $date = new \DateTime($value);
                    return $date->format('d/m/Y');
                } catch (\Exception $e) {
                    // Si la date est invalide, retourner une valeur par défaut
                    return '-';
                }
            }
            return '-'; // Si la valeur n'est pas valide, retourner un séparateur par défaut
        }

        // Retourner la valeur non modifiée si aucune condition ne s'applique
        return (string) $value;
    }
}
