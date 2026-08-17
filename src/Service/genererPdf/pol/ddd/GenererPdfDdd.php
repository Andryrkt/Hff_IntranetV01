<?php

namespace App\Service\genererPdf\pol\ddd;

use TCPDF;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Service\genererPdf\GeneratePdf;
use App\Controller\Traits\FormatageTrait;

class GenererPdfDdd extends GeneratePdf
{
    use FormatageTrait;
    /**
     * Génère le PDF pour une demande de diagnostic pneu.
     *
     * @param DemandeDiagnosticPneu $demande
     * @param string                $filePath Chemin complet du fichier PDF à créer
     */
    public function genererPdfDiagnosticPneu(DemandeDiagnosticPneu $demande, string $filePath): void
    {
        $pdf = new TCPDF();

        // ------------------ PAGE 1 ------------------
        $pdf->AddPage();

        // --- En-tête (logo + titre + numéro + date) ---
        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath = $_ENV['BASE_PATH_LONG'] . '/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        $pdf->Cell(110, 6, 'DEMANDE DE DIAGNOSTIC PNEU', 0, 0, 'C', false, '', 0, false, 'T', 'M');

        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $demande->getNumeroDemande(), 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        $pdf->setFont('helvetica', 'B', 12);
        $pdf->setAbsX(55);
        // Sous-titre (par ex. "Diagnostic pneu")
        $pdf->cell(110, 6, 'Diagnostic pneu', 0, 0, 'C', false, '', 0, false, 'T', 'M');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $dateCreation = $demande->getDateCreation() ? $demande->getDateCreation()->format('d/m/Y') : '';
        $pdf->cell(35, 6, 'Le : ' . $dateCreation, 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        // --- Section "Demandeur & Chantier" ---
        $this->renderTextWithLine($pdf, 'Demandeur / Chantier');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Demandeur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->cell(60, 6, $demande->getDemandeur() ?? '-', 1, 0, '', false, '', 0, false, 'T', 'M');

        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(110);
        $pdf->cell(23, 6, 'Chantier :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $chantier = $demande->getChantier() ? $demande->getChantier()->getNomChantier() : '-';
        $pdf->cell(0, 6, $chantier, 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        // --- Section "Matériel" ---
        $this->renderTextWithLine($pdf, 'Matériel');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'N° Parc :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->cell(40, 6, $demande->getNumeroParcMateriel() ?? '-', 1, 0, '', false, '', 0, false, 'T', 'M');

        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(80);
        $pdf->cell(25, 6, 'Livraison :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->cell(20, 6, $demande->getLivraison() ?? '-', 1, 0, '', false, '', 0, false, 'T', 'M');

        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(140);
        $pdf->cell(18, 6, 'Départ :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $dateDepart = $demande->getDateDepartChantier() ? $demande->getDateDepartChantier()->format('d/m/Y') : '-';
        $pdf->cell(0, 6, $dateDepart, 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        // --- Motifs ---
        // --- Motifs (sans box) ---
        $this->renderTextWithLine($pdf, 'Motifs');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', '', 9);
        $motifs = $demande->getMotifs();
        if ($motifs && count($motifs) > 0) {
            $motifsListe = implode("\n", array_map(function ($motif) {
                return '- ' . $motif;
            }, $motifs));
        } else {
            $motifsListe = '-';
        }
        // MultiCell sans bordure (paramètre border = 0)
        $pdf->MultiCell(0, 6, $motifsListe, 0, 'L', false, 1);
        $pdf->Ln(4, true);
        // --- Nombre de pneus (résumé) ---
        $this->renderTextWithLine($pdf, 'Récapitulatif pneus');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(30, 6, 'Sur machine :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(20, 6, $demande->getNbPneuSurMachine() ?? 0, 1, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->cell(25, 6, 'Secours :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(20, 6, $demande->getNbPneuSecours() ?? 0, 1, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(30, 6, 'À diagnostiquer :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(20, 6, $demande->getNbPneuADiagnostiquer() ?? 0, 1, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        // --- Liste des pneus avec diagnostics ---
        $this->renderTextWithLine($pdf, 'Détail des pneus');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 9);
        // En-tête du tableau
        $pdf->cell(30, 6, 'N° Série', 1, 0, 'C');
        $pdf->cell(30, 6, 'Cote / Dim', 1, 0, 'C');  // Changé de 40 à 30 pour correspondre aux données
        $pdf->cell(40, 6, 'Position', 1, 0, 'C');
        $pdf->cell(40, 6, 'Diagnostic', 1, 0, 'C');
        $pdf->cell(0, 6, 'Observation', 1, 0, 'C');
        $pdf->Ln(6, true);

        $pdf->setFont('helvetica', '', 9);
        $pneus = $demande->getDiagnosticPneus();
        if (count($pneus) > 0) {
            foreach ($pneus as $pneu) {
                $position = ucwords(str_replace('_', ' ', $pneu->getPositionMachine() ?? '-'));
                $pdf->cell(30, 6, $pneu->getNumeroSerie() ?? '-', 1, 0, 'C');
                $pdf->cell(30, 6, $pneu->getCoteDim() ?? '-', 1, 0, 'C');
                $pdf->cell(40, 6, $position, 1, 0, 'C');
                $pdf->cell(40, 6, $pneu->getDiagnostic() ?? '-', 1, 0, 'C');
                $pdf->cell(0, 6, $pneu->getObservationAtelier() ?? '-', 1, 0, 'C');
                $pdf->Ln(6, true);
            }
        } else {
            $pdf->cell(0, 6, 'Aucun pneu renseigné', 1, 0, 'C');
            $pdf->Ln(6, true);
        }

        // --- Observation globale ---
        $obsGlobal = trim($demande->getObservationGlobalAtelier() ?? '');
        if (!empty($obsGlobal)) {
            $pdf->Ln(4, true);
            $this->renderTextWithLine($pdf, 'Observation globale atelier');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->setFont('helvetica', '', 9);
            $pdf->MultiCell(0, 6, $obsGlobal, 0, 'L', false, 1);
        }



        // Génération du fichier
        $pdf->Output($filePath, 'F');
    }
}
