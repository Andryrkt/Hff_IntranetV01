<?php

namespace App\Service\genererPdf;

use TCPDF;
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
     * Largeur maximale (en %) autorisée pour une colonne fournisseur.
     */
    private const LARGEUR_MAX_FOURNISSEUR = 15.0;

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

        $nbFournisseurs = count($listeFournisseurs);
        $nbColonnesDynamiques = 1 + $nbFournisseurs;

        $largeurParColonneDynamique = $nbColonnesDynamiques > 0
            ? $largeurRestante / $nbColonnesDynamiques
            : 0;

        $largeurFournisseur = min($largeurParColonneDynamique, self::LARGEUR_MAX_FOURNISSEUR);
        $largeurDesi = $largeurRestante - ($largeurFournisseur * $nbFournisseurs);

        return $this->largeursColonnes = [
            'cst'         => self::WIDTH_CONFIG['cst'],
            'ref'         => self::WIDTH_CONFIG['ref'],
            'qte'         => self::WIDTH_CONFIG['qte'],
            'desi'        => $largeurDesi,
            'fournisseur' => $largeurFournisseur,
        ];
    }

    /**
     * Génère le tableau ET l'écrit directement dans le PDF, en garantissant
     * qu'aucune ligne <tr> n'est coupée entre deux pages.
     * 
     * @param iterable<DemandeApproL> $dals la liste des DAL à afficher
     * @param TCPDF $pdf
     */
    public function genererEtEcrire(iterable $dals, TCPDF $pdf): void
    {
        $fournisseurs = $this->gererPrixFournisseurs($dals);
        // Récupérer tous les noms de fournisseurs
        $listeFournisseurs = array_keys($fournisseurs);
        $w = $this->calculerLargeursColonnes($listeFournisseurs);

        $tableOpen  = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; font-size: 8px;">';
        $tableClose = '</table>';
        $enteteHtml = $tableOpen . $this->genererEntete($listeFournisseurs) . $tableClose;

        // Première impression de l'entête
        $pdf->writeHTML($enteteHtml, false, false, true, false, '');

        $totalGlobal = 0.0;

        foreach ($dals as $dal) {
            $ligneHtml = $tableOpen . $this->genererLigne($dal, $listeFournisseurs, $fournisseurs, $totalGlobal) . $tableClose;
            $this->ecrireLigneProtegee($pdf, $ligneHtml, $enteteHtml);
        }

        $totalHtml = $tableOpen . $this->genererLigneTotal($listeFournisseurs, $totalGlobal) . $tableClose;
        $this->ecrireLigneProtegee($pdf, $totalHtml, $enteteHtml);
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
        $html .= "<th rowspan=\"2\" align=\"center\" style=\"width:{$w['cst']}%;\">CST</th>";
        $html .= "<th rowspan=\"2\" align=\"center\" style=\"width:{$w['ref']}%;\">REF</th>";
        $html .= "<th rowspan=\"2\" align=\"center\" style=\"width:{$w['desi']}%;\">DESIGNATION</th>";
        $html .= "<th rowspan=\"2\" align=\"center\" style=\"width:{$w['qte']}%;\">QTE</th>";
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
     * Écrit une ligne HTML en évitant qu'elle soit coupée entre deux pages.
     * 
     * @param TCPDF $pdf
     */
    private function ecrireLigneProtegee(TCPDF &$pdf, string $ligneHtml, string $enteteHtml): void
    {
        $pdf->startTransaction();
        $pageAvant = $pdf->getPage();
        $pdf->writeHTML($ligneHtml, false, false, true, false, '');
        $pageApres = $pdf->getPage();

        if ($pageApres !== $pageAvant) {
            // la ligne a débordé / a été coupée -> on annule et on force une nouvelle page
            $pdf = $pdf->rollbackTransaction(true);
            $pdf->AddPage();
            $pdf->writeHTML($enteteHtml, false, false, true, false, '');
            $pdf->writeHTML($ligneHtml, false, false, true, false, '');
        } else {
            $pdf->commitTransaction();
        }
    }

    private function genererLigne(DemandeApproL $dal, array $listeFournisseurs, array $fournisseurs, float &$totalGlobal): string
    {
        $cst   = $dal->getArtConstp();
        $ref   = $dal->getArtRefp();
        $desi  = $dal->getArtDesi();
        $qte   = $dal->getQteDem();
        $keyId = implode('_', array_map('trim', [$cst, $ref, $desi, $qte]));
        if (in_array($cst, ["ZDI", "CAR"]) && !$dal->getDemandeApproLR()->isEmpty()) {
            $ref = $dal->getDemandeApproLR()->first()->getArtRefp();
        }

        $w = $this->calculerLargeursColonnes($listeFournisseurs);

        $html = '<tbody><tr>';
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

        return $html . '</tr></tbody>';
    }

    private function genererLigneTotal(array $listeFournisseurs, float $totalGlobal): string
    {
        $w = $this->calculerLargeursColonnes($listeFournisseurs);
        $nbColonnes = 4 + count($listeFournisseurs) - 1;
        $largeurLibelleTotal = $w['cst'] + $w['ref'] + $w['desi'] + $w['qte'] + $w['fournisseur'] * (count($listeFournisseurs) - 1);

        $html = '<tfoot><tr>';
        $html .= "<td colspan=\"{$nbColonnes}\" align=\"right\" style=\"width:{$largeurLibelleTotal}%;\"><strong>Montant Total pré-validé</strong></td>";
        $html .= "<td align=\"right\" style=\"width:{$w['fournisseur']}%; background-color: #fbbb01;\"><strong>{$this->formatPrix($totalGlobal)}</strong></td>";
        return $html . '</tr></tfoot>';
    }
}
