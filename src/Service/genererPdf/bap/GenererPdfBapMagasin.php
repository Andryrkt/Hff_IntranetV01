<?php

namespace App\Service\genererPdf\bap;

use App\Controller\Traits\FormatageTrait;
use App\Dto\ddp\DdpDto;
use App\Service\genererPdf\da\PdfTableHistoriqueLivraisonBAP;
use App\Service\genererPdf\GeneratePdf;
use TCPDF;

/**
 * Page de garde BAP MAGASIN (demande de paiement magasin après arrivage d'une
 * commande sans demande de paiement à l'avance) : même mise en page que le
 * BAP APPRO (GenererPdfBonAPayer), sans récapitulatif de l'OR ni de la DA.
 */
class GenererPdfBapMagasin extends GeneratePdf
{
    private const LARGEUR_GAUCHE = 0.6;
    private const LARGEUR_DROITE = 0.4;

    use FormatageTrait;

    /**
     * @param DdpDto $dto $dto->donneesBapMagasin = [
     *     'commandes' => [numCde => ['infoBc' => array, 'validation' => ?array{statut, dateSoumission, version},
     *                                'livraisons' => array, 'ddps' => array<array{date, numero, type, montant, ratio, statut}>]],
     *     'factures'  => array<array{Numero_Facture, Montant_Facture, Numero_PO}>,
     * ]
     */
    public function genererPdfDto(DdpDto $dto, string $cheminDeFichier): void
    {
        $pdf = new TCPDF();
        $pdf->setMargins(10, 10, 10);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $this->renderHeader($pdf, $dto);
        $w100 = $this->getUsableWidth($pdf);

        foreach ($dto->donneesBapMagasin['commandes'] ?? [] as $numCde => $commande) {
            $this->renderInfoBcEtValidation($pdf, $w100, (string) $numCde, $commande['infoBc'] ?? [], $commande['validation'] ?? null);
            $this->renderHistoriqueLivraison($pdf, $commande['livraisons'] ?? [], $dto->devise ?? '');
            $this->renderHistoriqueDdp($pdf, $commande['ddps'] ?? [], $dto->devise ?? '');
        }
        $this->renderFactures($pdf, $dto->donneesBapMagasin['factures'] ?? [], $dto->devise ?? '');
        $this->renderMontantBap($pdf, $dto);

        $directory = dirname($cheminDeFichier);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $pdf->Output($cheminDeFichier, 'F');
    }

    private function renderHeader(TCPDF $pdf, DdpDto $dto): void
    {
        $pdf->setAbsY(11);
        $pdf->Image("{$this->basePathAssets}/logoHff.jpg", '', '', 45, 12);
        $pdf->setAbsX(60);
        $pdf->setFont('helvetica', 'B', 22);
        $pdf->Cell(110, 12, 'BAP MAGASIN', 0, 0, 'C', false, '', 0, false, 'T', 'M');

        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 9);
        $pdf->SetY(4);
        $pdf->Cell(0, 6, "email : {$dto->adresseMailDemandeur}", 0, 0, 'R');

        $pdf->setAbsXY(170, 11);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $dto->numeroDdp, 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6);

        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $dto->dateDemandeFormater(), 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7);
    }

    private function renderInfoBcEtValidation(TCPDF $pdf, int $w100, string $numCde, array $infoBC, ?array $validation): void
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, "RESUME DU BC $numCde", $w100 * self::LARGEUR_GAUCHE - 3, 0);
        $this->renderSectionTitle($pdf, 'INFORMATION VALIDATION BC', $w100 * self::LARGEUR_DROITE);

        $dateSoumission = $validation['dateSoumission'] ?? null;
        $lignes = [
            ['Nom fournisseur', substr($infoBC["nom_fournisseur"] ?? "-", 0, 33), 'Statut', $validation['statut'] ?? "Non soumis à validation"],
            ['N° fournisseur', $infoBC["num_fournisseur"] ?? "-", 'Date soumission', $dateSoumission instanceof \DateTimeInterface ? $dateSoumission->format('d/m/Y') : "-"],
            ['N° commande', $infoBC["num_cde"] ?? $numCde, 'Version', isset($validation['version']) ? (string) $validation['version'] : "-"],
            ['Référence commande', $infoBC["libelle_cde"] ?? "-", '', ''],
            ['Date commande', !empty($infoBC["date_cde"]) ? date("d/m/Y", strtotime($infoBC["date_cde"])) : "-", '', ''],
            ['Succursale', $infoBC["succ_cde"] ?? "-", '', ''],
            ['Service', $infoBC["serv_cde"] ?? "-", '', ''],
            ['Opérateur', $infoBC["nom_ope"] ?? "-", '', ''],
            ['Montant HT', $this->formaterPrix($infoBC["mtn_cde"] ?? 0) . " " . ($infoBC["devise"] ?? ""), '', ''],
            ['Montant TTC', $this->formaterPrix($infoBC["ttc_cde"] ?? 0) . " " . ($infoBC["devise"] ?? ""), '', ''],
            ['Nature de l’achat', $infoBC["type_cde"] ?? "-", '', ''],
        ];

        foreach ($lignes as [$labelGauche, $valeurGauche, $labelDroite, $valeurDroite]) {
            $this->addInfoLine($pdf, $labelGauche, (string) $valeurGauche, $w100 * self::LARGEUR_GAUCHE - 6, 35, $labelDroite === '' ? 1 : 0);
            if ($labelDroite !== '') {
                $this->addInfoLine($pdf, $labelDroite, (string) $valeurDroite, $w100 * self::LARGEUR_DROITE, 27, 1);
            }
        }
    }

    private function renderHistoriqueLivraison(TCPDF $pdf, array $historiqueLivraison, string $devise): void
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, "RECAPITULATIF DES LIVRAISONS", 0);
        if (empty($historiqueLivraison)) {
            $pdf->Cell(0, 6, "Aucune livraison", 0, 1);
            $pdf->Ln(2);
        } else {
            $pdf->writeHTML((new PdfTableHistoriqueLivraisonBAP())->generateTable($historiqueLivraison, $devise));
        }
    }

    private function renderHistoriqueDdp(TCPDF $pdf, array $ddps, string $devise): void
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, "RECAPITULATIF DES DEMANDES DE PAIEMENT", 0);
        if (empty($ddps)) {
            $pdf->Cell(0, 6, "Aucune demande de paiement", 0, 1);
            $pdf->Ln(2);
            return;
        }

        $lignes = array_map(fn($ddp) => [
            ['center', '12%', $ddp['date']],
            ['center', '15%', $ddp['numero']],
            ['left', '28%', $ddp['type']],
            ['center', '10%', $this->formaterPrix($ddp['ratio'])],
            ['right', '15%', $this->formaterPrix($ddp['montant'])],
            ['left', '20%', $ddp['statut']],
        ], $ddps);

        $pdf->writeHTML($this->tableHtml(['Date', 'Numéro', 'Type', '%', "Montant ($devise)", 'Statut'], $lignes));
    }

    private function renderFactures(TCPDF $pdf, array $factures, string $devise): void
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, "INFO FACTURE FOURNISSEUR", 0);
        if (empty($factures)) {
            $pdf->Cell(0, 6, "Aucune facture", 0, 1);
            $pdf->Ln(2);
            return;
        }

        $lignes = array_map(fn($facture) => [
            ['left', '40%', $facture['Numero_Facture'] ?? ''],
            ['left', '30%', $facture['Numero_PO'] ?? ''],
            ['right', '30%', $this->formaterPrix($facture['Montant_Facture'] ?? 0)],
        ], $factures);

        $pdf->writeHTML($this->tableHtml(['N° facture', 'N° commande', "Montant facture ($devise)"], $lignes));
    }

    private function renderMontantBap(TCPDF $pdf, DdpDto $dto): void
    {
        $pdf->ln(2);
        // Montant de la BAP avec le pourcentage à payer en rouge
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(30, 6, 'MONTANT BAP : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->setFont('helvetica', '', 10);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(20, 6, '(' . number_format($dto->ratioMontantPayer, 2, ',', '') . '%)', 0, 0, 'R', false, '', 0, false, 'T', 'M');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 6, number_format($dto->montantAPayer(), 2, ',', '.') . ' ' . $dto->devise, 0, 1, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Cell(0, 6, 'Solde avance : ' . number_format(array_sum($dto->soldesAvance), 2, ',', '.') . ' ' . $dto->devise, 0, 1, 'L');
    }

    /**
     * @param string[] $entetes
     * @param array<array<array{0: string, 1: string, 2: string}>> $lignes cellules [alignement, largeur, valeur]
     */
    private function tableHtml(array $entetes, array $lignes): string
    {
        $html = '<table border="0" cellpadding="2" cellspacing="0" style="font-size: 9px;"><thead><tr style="background-color: #D3D3D3; font-weight: bold;">';
        foreach ($entetes as $i => $entete) {
            [$align, $largeur] = $lignes[0][$i];
            $html .= sprintf('<th align="%s" style="width:%s;">%s</th>', $align, $largeur, htmlspecialchars($entete));
        }
        $html .= '</tr></thead><tbody>';
        foreach ($lignes as $ligne) {
            $html .= '<tr>';
            foreach ($ligne as [$align, $largeur, $valeur]) {
                $html .= sprintf('<td align="%s" style="width:%s;">%s</td>', $align, $largeur, htmlspecialchars((string) $valeur));
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    }

    private function getUsableWidth(TCPDF $pdf)
    {
        $margins = $pdf->GetMargins();
        return $pdf->GetPageWidth() - $margins['left'] - $margins['right'];
    }

    private function addInfoLine(TCPDF $pdf, string $label, string $value, int $wTotal, int $labelWidth = 35, int $endLine = 1): void
    {
        $pdf->Cell(3, 6, '-', 0, 0);
        $pdf->Cell($labelWidth, 6, $label, 0, 0);
        $pdf->Cell($wTotal - $labelWidth, 6, ": $value", 0, $endLine);
    }

    private function renderSectionTitle(TCPDF $pdf, string $title, int $width, int $endline = 1): void
    {
        $pdf->setFont('helvetica', 'B', 9);
        $pdf->Cell($width, 6, $title, 0, $endline);
        $pdf->setFont('helvetica', '', 9);
    }
}
