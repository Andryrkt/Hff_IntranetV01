<?php

namespace App\Service\genererPdf;

class PdfTableMatriceGenerator
{
    /**
     * Générer le PDF complet avec le tableau
     */
    public function generer(array $donnees): string
    {
        $html = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; font-size: 8px;">';
        $html .= $this->genererEntete($fournisseurs); // Générer l'entête
        $html .= $this->genererCorps($donnees, $fournisseurs); // Générer le corps
        $html .= '</table>';
        return $html;
    }

    /**
     * Générer l'entête du tableau
     */
    private function genererEntete(array $fournisseurs): string
    {
        $html = '<thead style="vertical-align: middle; text-align: center;">';

        // Ligne titre principale
        $html .= '<tr>
            <th rowspan="2">DESIGNATION</th>
			<th rowspan="2">QTE</th>
            <td colspan="' . count($fournisseurs) . '" align="center">FOURNISSEURS</td>
        </tr>';

        // Ligne des colonnes
        $html .= '<tr>';
        foreach ($fournisseurs as $frn) {
            $html .= "<th><b> $frn </b></th>";
        }
        $html .= '</tr></thead>';

        return $html;
    }

    /**
     * Générer le corps du tableau
     */
    private function genererCorps(array $donnees, array $fournisseurs): string
    {
        $html = '<tbody>';
        foreach ($donnees as $article) {
            $html .= '<tr>';
            $html .= '<td>' . $article['designation'] . '</td>';
            $html .= '<td align="right">' . $article['qte'] . '</td>';
            foreach ($fournisseurs as $frnKey) {
                $html .= '<td align="right">' . ($article['prix'][$frnKey] ?? '') . '</td>';
            }
            $html .= '</tr>';
        }
        return $html . '</tbody>';
    }
}
