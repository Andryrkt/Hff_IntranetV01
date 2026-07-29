<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\da\PrixFournisseurTrait;
use App\Entity\da\DemandeApproL;

class PdfTableMatriceGenerator
{
    use PrixFournisseurTrait;

    /**
     * @var array<string,float> pourcentage par colonne fixe
     */
    private const WIDTH_CONFIG = ['cst' => 7.0, 'ref' => 12.0, 'qte' => 7.0];

    /**
     * @var array{cst: float, ref: float, desi: float, qte: float, fournisseur: float}|null
     */
    private ?array $largeursColonnes = null;

    /**
     * Calcule les largeurs de colonnes : fixes + réparties dynamiquement entre "désignation" et les colonnes fournisseurs.
     *
     * @param array $listeFournisseurs
     * @return array{cst:float,ref:float,desi:float,qte:float,fournisseur:float}
     */
    private function calculerLargeursColonnes(array $listeFournisseurs): array
    {
        if ($this->largeursColonnes !== null) return $this->largeursColonnes;

        $largeurFixe = array_sum(self::WIDTH_CONFIG);
        $largeurRestante = max(0, 100 - $largeurFixe);

        // Nombre de colonnes "dynamiques" : DESIGNATION + une par fournisseur
        $nbColonnesDynamiques = 1 + count($listeFournisseurs);

        $largeurParColonneDynamique = $nbColonnesDynamiques > 0
            ? $largeurRestante / $nbColonnesDynamiques
            : 0;

        return $this->largeursColonnes = [
            'cst'         => self::WIDTH_CONFIG['cst'],
            'ref'         => self::WIDTH_CONFIG['ref'],
            'qte'         => self::WIDTH_CONFIG['qte'],
            'desi'        => $largeurParColonneDynamique,
            'fournisseur' => $largeurParColonneDynamique,
        ];
    }

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
        $w = $this->calculerLargeursColonnes($listeFournisseurs);
        $largeurTotaleFournisseurs = $w['fournisseur'] * count($listeFournisseurs);

        $html = '<thead>';

        // Ligne titre principale
        $html .= "<tr style=\"background-color: #dcdcdc;\">";
        $html .= "<th rowspan=\"2\" align=\"center\" valign=\"middle\" style=\"width:{$w['cst']}%;\">CST</th>";
        $html .= "<th rowspan=\"2\" align=\"center\" valign=\"middle\" style=\"width:{$w['ref']}%;\">REF</th>";
        $html .= "<th rowspan=\"2\" align=\"center\" valign=\"middle\" style=\"width:{$w['desi']}%;\">DESIGNATION</th>";
        $html .= "<th rowspan=\"2\" align=\"center\" valign=\"middle\" style=\"width:{$w['qte']}%;\">QTE</th>";
        $html .= "<td colspan=\"" . count($listeFournisseurs) . "\" align=\"center\" style=\"width:{$largeurTotaleFournisseurs}%; font-weight:bold;\">** FOURNISSEURS **</td>";
        $html .= "</tr>";

        // Ligne des colonnes fournisseurs
        $html .= '<tr style="background-color: #dcdcdc;">';
        foreach ($listeFournisseurs as $frn) {
            $html .= "<th align=\"center\" style=\"width:{$w['fournisseur']}%;\"><b>{$frn}</b></th>";
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
        $w = $this->calculerLargeursColonnes($listeFournisseurs);
        $html = '<tbody>';
        $totalGlobal = 0.0;

        foreach ($dals as $dal) {
            $cst   = $dal->getArtConstp();
            $ref   = $dal->getArtRefp();
            $desi  = $dal->getArtDesi();
            $qte   = $dal->getQteDem();
            $keyId = implode('_', array_map('trim', [$cst, $ref, $desi, $qte]));
            if (in_array($cst, ["ZDI", "CAR"]) && !$dal->getDemandeApproLR()->isEmpty()) {
                $ref = $dal->getDemandeApproLR()->first()->getArtRefp();
            }
            $html .= '<tr>';
            $html .= "<td align=\"center\" style=\"width:{$w['cst']}%;\">{$cst}</td>";
            $html .= "<td align=\"center\" style=\"width:{$w['ref']}%;\">{$ref}</td>";
            $html .= "<td style=\"width:{$w['desi']}%;\">" . htmlspecialchars($desi) . "</td>";
            $html .= "<td align=\"center\" style=\"width:{$w['qte']}%;\">{$qte}</td>";

            foreach ($listeFournisseurs as $frn) {
                $prix    = $fournisseurs[$frn][$keyId]['prix'] ?? '';
                $choix   = $fournisseurs[$frn][$keyId]['choix'] ?? false;
                $montant = $fournisseurs[$frn][$keyId]['montant'] ?? 0;
                $style   = "width:{$w['fournisseur']}%;" . ($choix ? ' background-color: #fbbb01;' : '');

                if ($prix === '' || $prix === null || $prix == 0) {
                    $contenu = "";
                } else {
                    if ($choix) $totalGlobal += $montant;
                    $contenu = "PU: $prix <br>MTT: {$this->formatPrix($montant)}";
                }

                $html .= "<td align=\"right\" style=\"{$style}\">{$contenu}</td>";
            }

            $html .= '</tr>';
        }

        // Ligne du total global
        $nbColonnes = 4 + count($listeFournisseurs) - 1;
        $largeurLibelleTotal = $w['cst'] + $w['ref'] + $w['desi'] + $w['qte'] + $w['fournisseur'] * (count($listeFournisseurs) - 1);

        $html .= "<tr>";
        $html .= "<td colspan=\"{$nbColonnes}\" align=\"right\" style=\"width:{$largeurLibelleTotal}%;\"><strong>Montant DA</strong></td>";
        $html .= "<td align=\"right\" style=\"width:{$w['fournisseur']}%;\"><strong>{$this->formatPrix($totalGlobal)}</strong></td>";
        $html .= "</tr>";

        return $html . '</tbody>';
    }
}
