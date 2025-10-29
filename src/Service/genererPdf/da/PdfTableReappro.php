<?php

namespace App\Service\genererPdf\da;

use App\Entity\da\DemandeApproL;

class PdfTableReappro
{
    public function generateTableArticleDemandeReappro(iterable $dals)
    {
        $html = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; font-size: 8px;">';
        $html .= $this->generateHeaderArticleDemandeReappro();
        $html .= $this->generateBodyArticleDemandeReappro($dals);
        $html .= '</table>';
        return $html;
    }

    private function generateHeaderArticleDemandeReappro()
    {
        return '
            <thead>
                <tr style="background-color: #dcdcdc;">
                    <th align="center">Constructeur</th>
                    <th align="center">Référence</th>
                    <th align="center">Désignation</th>
                    <th align="right">PU</th>
                    <th align="center">Qté demandé</th>
                    <th align="right">Montant</th>
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
        } else {
            /** @var DemandeApproL $dal */
            foreach ($dals as $dal) {
                $html .= '<tr>';
                $html .= '<td align="center">' . $dal->getArtConstp() . '</td>';
                $html .= '<td align="center">' . $dal->getArtRefp() . '</td>';
                $html .= '<td align="left">' . $dal->getArtDesi() . '</td>';
                $html .= '<td align="right">' . $dal->getPUFormatted() . '</td>';
                $html .= '<td align="center">' . $dal->getQteDem() . '</td>';
                $html .= '<td align="right">' . $dal->getMontantFormatted() . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody>';
        return $html;
    }
}
