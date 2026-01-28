<?php

namespace App\Service\genererPdf\da;

use App\Entity\da\DemandeApproL;

class PdfTableReappro
{
    public function generateTableArticleDemandeReappro(iterable $dals, bool $isPonctuel)
    {
        $html = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-size: 9px;">';
        $html .= $this->generateHeaderArticleDemandeReappro($isPonctuel);
        $html .= $this->generateBodyArticleDemandeReappro($dals, $isPonctuel);
        $html .= '</table>';
        return $html;
    }

    private function generateHeaderArticleDemandeReappro(bool $isPonctuel): string
    {
        $columns = [
            $this->createTableCell('center', '10%', 'Const'),
            $this->createTableCell('center', '15%', 'Référence'),
            $this->createTableCell('center', $isPonctuel ? '40%' : '30%', 'Désignation'),
            $this->createTableCell('right', '12%', 'PU'),
            $this->createTableCell('center', '10%', 'Qté demandé'),
        ];

        if (!$isPonctuel) $columns[] = $this->createTableCell('center', '10%', 'Qté Validée');

        $columns[] = $this->createTableCell('right', '13%', 'Montant');

        return sprintf(
            '<thead><tr style="background-color: #dcdcdc; font-weight: bold;">%s</tr></thead>',
            implode('', $columns)
        );
    }

    private function createTableCell(string $align, string $width, string $label, string $style = "", bool $header = true): string
    {
        $tag = $header ? 'th' : 'td';
        return sprintf('<%s align="%s" style="width:%s; %s">%s</%s>', $tag, $align, $width, $style, $label, $tag);
    }

    private function generateBodyArticleDemandeReappro(iterable $dals, bool $isPonctuel): string
    {
        $rows = [];
        $hasRows = false;

        foreach ($dals as $dal) {
            $hasRows = true;
            $rows[] = $this->createArticleRow($dal, $isPonctuel);
        }

        if (!$hasRows) {
            return '<tbody><tr><td colspan="7" align="center">Aucun article demandé</td></tr></tbody>';
        }

        return '<tbody>' . implode('', $rows) . '</tbody>';
    }

    private function createArticleRow(DemandeApproL $dal, bool $isPonctuel): string
    {
        $qteDem = $dal->getQteDem();
        $qteVal = $dal->getQteValAppro();
        $exces = $qteDem > $qteVal;
        $bgRed = !$isPonctuel && $exces ? "background-color: #dc3545; color: #fff; font-weight: bold;" : "";

        $cells = [
            $this->createTableCell('center', '10%', $dal->getArtConstp(), "", false),
            $this->createTableCell('center', '15%', $dal->getArtRefp(), "", false),
            $this->createTableCell('left', $isPonctuel ? '40%' : '30%', $dal->getArtDesi(), "", false),
            $this->createTableCell('right', '12%', $dal->getPUFormatted(), "", false),
            $this->createTableCell('center', '10%', $qteDem, $bgRed, false),
        ];

        if (!$isPonctuel) $cells[] = $this->createTableCell('center', '10%', $qteVal, "", false);

        $cells[] = $this->createTableCell('right', '13%', $dal->getMontantFormatted(), "", false);

        return '<tr>' . implode('', $cells) . '</tr>';
    }

    public function generateHistoriqueTable(array $monthsList, array $dataHistorique)
    {
        $widthConfig = [
            "cst"   => 4,
            "ref"   => 5,
            "desi"  => 10,
            "mois"  => 76 / 12,
            "total" => 5,
        ];
        $html = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; font-size: 8px;">';
        $html .= $this->generateHistoriqueHeader($monthsList, $widthConfig);
        $html .= $this->generateHistoriqueBody($monthsList, $dataHistorique, $widthConfig);
        $html .= '</table>';

        return $html;
    }

    private function generateHistoriqueHeader(array $monthsList, array $widthConfig)
    {
        $html = '<thead>';
        // Première ligne de l'en-tête
        $html .= '<tr style="background-color: #dcdcdc; font-weight: bold;">';
        $html .= '<th rowspan="2" align="center" style="width:' . $widthConfig['cst'] . '%;">Const</th>';
        $html .= '<th rowspan="2" align="center" style="width:' . $widthConfig['ref'] . '%;">Ref</th>';
        $html .= '<th rowspan="2" align="center" style="width:' . $widthConfig['desi'] . '%;">Désignation</th>';
        $html .= '<th colspan="13" align="center" style="width:' . ($widthConfig['total'] + 12 * $widthConfig['mois'])  . '%;">Quantités facturées sur les 12 derniers mois</th>';
        $html .= '</tr>';

        // Deuxième ligne avec les mois et le total
        $html .= '<tr style="background-color: #dcdcdc; font-weight: bold; font-size: 7.5px;">';
        foreach ($monthsList as $month) {
            $html .= '<th align="right" style="width:' . $widthConfig['mois'] . '%;">' . $month . '</th>';
        }
        $html .= '<th align="right" style="width:' . $widthConfig['total'] . '%;">Total qté</th>';
        $html .= '</tr>';

        $html .= '</thead>';
        return $html;
    }

    private function generateHistoriqueBody(array $monthsList, array $dataHistorique, array $widthConfig)
    {
        $html = '<tbody>';

        if (empty($dataHistorique["data"])) {
            $colspan = 3 + count($monthsList) + 1; // colonnes constructeur + réf + desi + mois + total
            $html .= '<tr><td colspan="' . $colspan . '" align="center">Aucune donnée d’historique de consommation</td></tr>';
            $html .= '</tbody>';
            return $html;
        }

        foreach ($dataHistorique["data"] as $data) {
            $html .= '<tr>';
            $html .= '<td align="center" style="width:' . $widthConfig['cst'] . '%;">' . $data['cst'] . '</td>';
            $html .= '<td align="center" style="width:' . $widthConfig['ref'] . '%;">' . $data['refp'] . '</td>';
            $html .= '<td align="left" style="width:' . $widthConfig['desi'] . '%;">' . $data['desi'] . '</td>';

            foreach ($monthsList as $month) {
                $html .= '<td align="right" style="width:' . $widthConfig['mois'] . '%;">' . $data['qte'][$month] . '</td>';
            }

            $html .= '<td align="right" style="width:' . $widthConfig['total'] . '%;">' . $data['qteTotal'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '<tfoot>';
        $html .= '<tr><td colspan="16"></td></tr>';
        $html .= '<tr style="font-weight:bold;"><td colspan="3" align="center">MONTANT en AR</td>';
        foreach ($monthsList as $month) {
            $html .= '<td align="right" style="width:' . $widthConfig['mois'] . '%; font-size: 7.2px;">' . str_replace(" ", ".", $dataHistorique["montants"][$month]) . '</td>';
        }
        $html .= '<td></td></tr>';
        $html .= '</tfoot>';
        return $html;
    }
}
