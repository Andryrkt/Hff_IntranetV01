<?php

namespace App\Service\genererPdf;

class PdfTableGeneratorDaDirect
{
    private function generateHeaderForDA(array $headerConfig)
    {
        $html = '<thead><tr style="background-color: #5c5c5c; color: #fff; font-weight: bold;">';
        foreach ($headerConfig as $config) {
            $html .= '<th style="width: ' . $config['width'] . 'px; ' . $config['style'] . ' border:1px solid  #c4c4c4;">' . $config['label'] . '</th>';
        }
        $html .= '</tr></thead>';
        return $html;
    }

    public function generateTableBonAchatValide(array $headerConfig, iterable $rows)
    {
        $html = '<table cellpadding="4" align="center" style="font-size: 10px; border:1px solid  #c4c4c4; border-collapse: collapse;">';
        $html .= $this->generateHeaderForDA($headerConfig);
        $html .= $this->generateBodyForDA($headerConfig, $rows);
        $html .= '</table>';
        return $html;
    }

    public function generateTableAValiderDW(array $headerConfig, iterable $rows)
    {
        $html = '<table cellpadding="4" align="center" style="font-size: 10px; margin: 0 auto; border:1px solid  #c4c4c4; border-collapse: collapse;">';
        $html .= $this->generateHeaderForDA($headerConfig);
        $html .= $this->generateBodyForDaAValiderDW($headerConfig, $rows);
        $html .= '</table>';
        return $html;
    }

    private function generateBodyForDaAValiderDW(array $headerConfig, iterable $dals)
    {
        $html = '<tbody>';
        // Vérifier si le tableau $dals est vide
        if (empty($dals)) {
            $html .= '<tr><td colspan="' . count($headerConfig) . '" style="text-align: center; font-weight: bold; border:1px solid  #c4c4c4;">N/A</td></tr>';
            $html .= '</tbody>';
            return $html;
        }

        /** @var DemandeApproL $dal une demande appro L dans dals */
        foreach ($dals as $dal) {
            $html .= '<tr>';
            $row = [
                'designation' => $dal->getArtDesi(),
                'comms'       => $dal->getCommentaire(),
                'qte'         => $dal->getQteDem(),
            ];
            foreach ($headerConfig as $config) {
                $key = $config['key'];
                $value = $row[$key] ?? '';
                $style = str_replace('font-weight: bold;', '', $config['style']);
                $style .= 'background-color: #e9e9e9;';

                $html .= '<td style="width: ' . $config['width'] . 'px; border:1px solid  #c4c4c4; ' . $style . '">' . $value . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        return $html;
    }

    private function generateBodyForDA(array $headerConfig, iterable $dals)
    {
        $html = '<tbody>';
        $total = 0;
        // Vérifier si le tableau $dals est vide
        if (empty($dals)) {
            $html .= '<tr><td colspan="' . count($headerConfig) . '" style="text-align: center; font-weight: bold; border:1px solid  #c4c4c4;">N/A</td></tr>';
            $html .= '</tbody>';
            return $html;
        }

        /** @var DemandeApproL $dal une demande appro L dans dals */
        foreach ($dals as $dal) {
            $html .= '<tr>';
            $row = [
                'reference'   => $dal->getArtRefp(),
                'designation' => $dal->getArtDesi(),
                'pu1'         => $dal->getPrixUnitaire(),
                'qte'         => $dal->getQteDem(),
            ];
            $row['mttTotal'] = $row['pu1'] * $row['qte'];
            foreach ($headerConfig as $config) {
                $key = $config['key'];
                $value = $row[$key] ?? '';
                $style = str_replace('font-weight: bold;', '', $config['style']);
                if ($dal->getDemandeApproLR()->isEmpty()) {
                    $style .= 'background-color: #fbbb01;';
                }
                $value = $this->formatValueForDA($key, $value);

                $html .= '<td style="width: ' . $config['width'] . 'px; border:1px solid  #c4c4c4; ' . $style . '">' . $value . '</td>';
            }
            $html .= '</tr>';
            if ($dal->getDemandeApproLR()->isEmpty()) {
                $total += $row['mttTotal'];
                $html .= '<tr><td colspan="' . count($headerConfig) . '" style="text-align: center; font-weight: normal; font-size: 8px; background-color:#e9e9e9; border:1px solid  #c4c4c4;  color:#646464; border-left: 2px solid #5c5c5c;">Aucune proposition n’a été faite pour cet article.</td></tr>';
            } else {
                /** @var DemandeApproLR $dalr une demande appro LR dans dalrs */
                foreach ($dal->getDemandeApproLR() as $dalr) {
                    $html .= '<tr>';
                    $row = [
                        'reference' => $dalr->getArtRefp(),
                        'designation' => $dalr->getArtDesi(),
                        'pu1' => $dalr->getPrixUnitaire(),
                        'qte' => $dalr->getQteDem(),
                    ];
                    $row['mttTotal'] = $row['pu1'] * $row['qte'];
                    $total += $dalr->getChoix() ? $row['mttTotal'] : 0;
                    foreach ($headerConfig as $config) {
                        $key = $config['key'];
                        $value = $row[$key] ?? '';
                        $style = str_replace('font-weight: bold;', 'font-weight: normal;', $config['style']);
                        if ($dalr->getChoix()) {
                            $style .= 'background-color: #fbbb01;';
                        } else {
                            $style .= 'background-color: #e9e9e9;';
                        }

                        $value = $this->formatValueForDA($key, $value);
                        if ($config['key'] === 'reference') {
                            $style .= 'border-left: 2px solid #5c5c5c;';
                            $value = "   $value";
                        }

                        $html .= '<td style="width: ' . $config['width'] . 'px; font-size: 8px; border:1px solid #c4c4c4;  color: #646464; ' . $style . '">' . $value . '</td>';
                    }
                    $html .= '</tr>';
                }
            }
        }
        $html .= '</tbody>';
        $html .= $this->generateFooterForDA($total);

        return $html;
    }

    private function generateFooterForDA($total): string
    {
        $html = '<tfoot><tr style="background-color: #5c5c5c; color: #fff; font-weight: bold;">';
        $html .= '<th colspan="4" style="border:1px solid  #c4c4c4;">Total de montants des articles validés</th>';
        $html .= '<th style="border:1px solid  #c4c4c4; text-align: right;">' . number_format($total, 2, ',', '.') . '</th>';
        $html .= '</tr></tfoot>';
        return $html;
    }

    private function formatValueForDA(string $key, $value): string
    {
        // Vérifier si la clé concerne un montant
        if (in_array($key, ['mttTotal', 'mttPieces', 'mttMo', 'mttSt', 'mttLub', 'mttAutres', 'mttTotalAv', 'mttTotalAp', 'pu1', 'pu2', 'pu3']) || stripos($key, 'mtt') !== false) {
            // Vérifier si la valeur est un nombre
            if (is_numeric($value)) {
                return $value == 0 ? '' : number_format((float) $value, 2, ',', '.');
            }
            return '0.00'; // Retourner un montant par défaut si ce n'est pas un nombre
        }

        // Retourner la valeur non modifiée si aucune condition ne s'applique
        return (string) $value;
    }
}
