<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\da\PrixFournisseurTrait;
use App\Entity\da\DemandeApproL;

class PdfTableMatriceGenerator
{
    use PrixFournisseurTrait;

    /**
     * Générer le PDF complet avec le tableau
     * 
     * @param iterable<DemandeApproL> $dals la liste des DAL à afficher
     * 
     * @return string le code HTML du tableau
     */
    public function generer(iterable $dals): string
    {
        $fournisseurs = $this->gererPrixFournisseurs($dals);
        // Récupérer tous les noms de fournisseurs
        $listeFournisseurs = array_keys($fournisseurs);
        $html = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; font-size: 8px;">';
        $html .= $this->genererEntete($listeFournisseurs); // Générer l'entête
        $html .= $this->genererCorps($dals, $listeFournisseurs, $fournisseurs); // Générer le corps
        $html .= '</table>';
        return $html;
    }

    /**
     * Générer l'entête du tableau
     */
    private function genererEntete(array $listeFournisseurs): string
    {
        $html = '<thead>';

        // Ligne titre principale
        $html .= '<tr style="background-color: #dcdcdc;">
            <th rowspan="2" align="center" valign="middle">CST</th>
			<th rowspan="2" align="center" valign="middle">REF</th>
			<th rowspan="2" align="center" valign="middle">DESIGNATION</th>
			<th rowspan="2" align="center" valign="middle">QTE</th>
            <td colspan="' . count($listeFournisseurs) . '" align="center" style="font-weight:bold;">** FOURNISSEURS **</td>
        </tr>';

        // Ligne des colonnes
        $html .= '<tr style="background-color: #dcdcdc;">';
        foreach ($listeFournisseurs as $frn) {
            $html .= "<th align=\"center\"><b> $frn </b></th>";
        }
        $html .= '</tr></thead>';

        return $html;
    }

    /**
     * Générer le corps du tableau
     * 
     * @param iterable<DemandeApproL> $dals la liste des DAL à afficher
     * @param array $listeFournisseurs la liste des fournisseurs
     * @param array $fournisseurs le tableau des fournisseurs avec prix
     * 
     * @return string le code HTML du corps du tableau
     */
    private function genererCorps(iterable $dals, array $listeFournisseurs, array $fournisseurs): string
    {
        $html = '<tbody>';
        foreach ($dals as $dal) {
            $cst = $dal->getArtConstp();
            $ref = $dal->getArtRefp();
            $desi = $dal->getArtDesi();
            $qte  = $dal->getQteDem();
            $keyId = implode('_', array_map('trim', [$cst, $ref, $desi, $qte]));
            $html .= '<tr>';
            $html .= '<td>' . $cst . '</td>';
            $html .= '<td>' . $ref . '</td>';
            $html .= '<td>' . htmlspecialchars($desi) . '</td>';
            $html .= '<td align="right">' . $qte . '</td>';

            foreach ($listeFournisseurs as $frn) {
                $prix = $fournisseurs[$frn][$keyId]['prix'] ?? '';
                $choix = $fournisseurs[$frn][$keyId]['choix'] ?? false;
                $style = $choix ? 'background-color: #fbbb01;' : '';
                $html .= '<td align="right" style="' . $style . '">' . $prix . '</td>';
            }

            $html .= '</tr>';
        }
        return $html . '</tbody>';
    }
}
