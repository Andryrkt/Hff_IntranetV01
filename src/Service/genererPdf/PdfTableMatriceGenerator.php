<?php

namespace App\Service\genererPdf;

use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;

class PdfTableMatriceGenerator
{
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
        $html .= '<tr>
            <th rowspan="2" align="center" valign="middle">DESIGNATION</th>
			<th rowspan="2" align="center" valign="middle">QTE</th>
            <td colspan="' . count($listeFournisseurs) . '" align="center">FOURNISSEURS</td>
        </tr>';

        // Ligne des colonnes
        $html .= '<tr>';
        foreach ($listeFournisseurs as $frn) {
            $html .= "<th align=\"right\"><b> $frn </b></th>";
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
            $desi = $dal->getArtDesi();
            $qte  = $dal->getQteDem();
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($desi) . '</td>';
            $html .= '<td align="right">' . $qte . '</td>';

            foreach ($listeFournisseurs as $frn) {
                $prix = $fournisseurs[$frn][$desi]['prix'] ?? '';
                $choix = $fournisseurs[$frn][$desi]['choix'] ?? false;
                $style = $choix ? 'background-color: #fbbb01;' : '';
                $html .= '<td align="right" style="' . $style . '">' . $prix . '</td>';
            }

            $html .= '</tr>';
        }
        return $html . '</tbody>';
    }

    /**
     * Gérer la liste des fournisseurs et prix correspondant à partir des DAL
     * 
     * @param iterable<DemandeApproL> $dals la liste des DAL à afficher
     * 
     * @return array le tableau de fournisseurs avec prix
     */
    private function gererPrixFournisseurs(iterable $dals): array
    {
        $fournisseurs = [];
        foreach ($dals as $dal) {
            $designation = $dal->getArtDesi();
            /** @var iterable<DemandeApproLR> $dalrs la liste des DALR dans DAL */
            $dalrs       = $dal->getDemandeApproLR();
            if ($dalrs->isEmpty()) {
                $fournisseur = $dal->getNomFournisseur();
                $prix        = $this->formatPrix($dal->getPrixUnitaire());
                $fournisseurs[$fournisseur][$designation] = [
                    'prix'  => $prix,
                    'choix' => true,
                ];
            } else {
                foreach ($dalrs as $dalr) {
                    $frnDalr = $dalr->getNomFournisseur();
                    $prix    = $this->formatPrix($dalr->getPrixUnitaire());
                    $fournisseurs[$frnDalr][$designation] = [
                        'prix'  => $prix,
                        'choix' => $dalr->getChoix(),
                    ];
                }
            }
        }
        return $fournisseurs;
    }

    private function formatPrix($prix): string
    {
        if (is_numeric($prix)) {
            return $prix == 0 ? '' : number_format((float) $prix, 2, ',', ' ');
        }
        return '0,00'; // Retourner un montant par défaut si ce n'est pas un nombre
    }
}
