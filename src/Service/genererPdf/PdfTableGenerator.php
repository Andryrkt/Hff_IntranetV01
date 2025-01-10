<?php

namespace App\Service\genererPdf;

class PdfTableGenerator
{
    public function generateTable(array $headerConfig, array $rows, array $totals)
    {
        $html = '<table border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px;">';
        $html .= $this->generateHeader($headerConfig);
        $html .= $this->generateBody($headerConfig, $rows);
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

    private function generateBody(array $headerConfig, array $rows)
    {
        $html = '<tbody>';
        foreach ($rows as $row) {
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

            // Vérifier si la clé correspond à "total"
            $value = $this->formatValue($key, $value);


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

    private function formatValue($key, $value)
    {
        // Vérifier si la clé concerne un montant
        if (in_array($key, ['mttTotal', 'mttPieces', 'mttMo', 'mttSt', 'mttLub', 'mttAutres', 'mttTotalAv', 'mttTotalAp'])) {
            // Vérifier si la valeur est un nombre
            if (is_numeric($value)) {
                return number_format((float) $value, 2, '.', ',');
            }
            return '0.00'; // Retourner un montant par défaut si ce n'est pas un nombre
        }
        return $value;
    }
}
