<?php

namespace App\Service\genererPdf\da;

use App\Entity\da\DemandeApproL;

class PdfTableReappro
{
    public function generateTableArticleDemandeReappro(iterable $dals)
    {
        $html = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-size: 9px;">';
        $html .= $this->generateHeaderArticleDemandeReappro();
        $html .= $this->generateBodyArticleDemandeReappro($dals);
        $html .= '</table>';
        return $html;
    }

    private function generateHeaderArticleDemandeReappro()
    {
        return '
            <thead>
                <tr style="background-color: #dcdcdc; font-weight: bold;">
                    <th align="center" style="width:10%;">Const</th>
                    <th align="center" style="width:15%;">Référence</th>
                    <th align="center" style="width:30%;">Désignation</th>
                    <th align="right" style="width:12%;">PU</th>
                    <th align="center" style="width:10%;">Qté demandé</th>
                    <th align="center" style="width:10%;">Qté Validée</th>
                    <th align="right" style="width:13%;">Montant</th>
                </tr>
            </thead>
        ';
    }

    private function generateBodyArticleDemandeReappro(iterable $dals)
    {
        $html = '<tbody>';

        // Si aucune ligne n’existe
        if (empty($dals)) {
            $html .= '<tr><td colspan="6" align="center">Aucun article demandé</td></tr>';
            $html .= '</tbody>';
            return $html;
        }

        /** @var DemandeApproL $dal */
        foreach ($dals as $dal) {
            $html .= '<tr>';
            $html .= '<td align="center" style="width:10%;">' . $dal->getArtConstp() . '</td>';
            $html .= '<td align="center" style="width:15%;">' . $dal->getArtRefp() . '</td>';
            $html .= '<td align="left" style="width:30%;">' . $dal->getArtDesi() . '</td>';
            $html .= '<td align="right" style="width:12%;">' . $dal->getPUFormatted() . '</td>';
            $html .= '<td align="center" style="width:10%;">' . $dal->getQteDem() . '</td>';
            $html .= '<td align="center" style="width:10%;">' . $dal->getQteValAppro() . '</td>';
            $html .= '<td align="right" style="width:13%;">' . $dal->getMontantFormatted() . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        return $html;
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
