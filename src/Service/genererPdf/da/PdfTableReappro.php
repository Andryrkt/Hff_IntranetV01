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
                    <th align="center" style="width:10%;">Constructeur</th>
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
        $html = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; font-size: 8px;">';
        $html .= $this->generateHistoriqueHeader($monthsList);
        $html .= $this->generateHistoriqueBody($monthsList, $dataHistorique);
        $html .= '</table>';

        return $html;
    }

    private function generateHistoriqueHeader(array $monthsList)
    {
        $html = '<thead>';
        // Première ligne de l'en-tête
        $html .= '<tr style="background-color: #dcdcdc; font-weight: bold;">';
        $html .= '<th rowspan="2" align="center" style="width:5%;">Constructeur</th>';
        $html .= '<th rowspan="2" align="center" style="width:7%;">Référence</th>';
        $html .= '<th rowspan="2" align="center" style="width:18%;">Désignation</th>';
        $html .= '<th colspan="13" align="center" style="width:72%;">Quantités facturées sur les 12 derniers mois</th>';
        $html .= '</tr>';

        // Deuxième ligne avec les mois et le total
        $html .= '<tr style="background-color: #dcdcdc; font-weight: bold; font-size: 7.5px;">';
        foreach ($monthsList as $month) {
            $html .= '<th align="center">' . $month . '</th>';
        }
        $html .= '<th align="center">Total qte</th>';
        $html .= '</tr>';

        $html .= '</thead>';
        return $html;
    }

    private function generateHistoriqueBody(array $monthsList, array $dataHistorique)
    {
        $html = '<tbody>';

        if (empty($dataHistorique)) {
            $colspan = 3 + count($monthsList) + 1; // colonnes constructeur + réf + desi + mois + total
            $html .= '<tr><td colspan="' . $colspan . '" align="center">Aucune donnée d’historique de consommation</td></tr>';
            $html .= '</tbody>';
            return $html;
        }

        foreach ($dataHistorique as $data) {
            $html .= '<tr>';
            $html .= '<td align="center" style="width:5%;">' . $data['cst'] . '</td>';
            $html .= '<td align="center" style="width:7%;">' . $data['refp'] . '</td>';
            $html .= '<td align="left" style="width:18%;">' . $data['desi'] . '</td>';

            foreach ($monthsList as $month) {
                $html .= '<td align="right" style="width:' . 72 / 13 . '%;">' . $data['qte'][$month] . '</td>';
            }

            $html .= '<td align="right" style="width:' . 72 / 13 . '%;">' . $data['qteTotal'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        return $html;
    }
}
