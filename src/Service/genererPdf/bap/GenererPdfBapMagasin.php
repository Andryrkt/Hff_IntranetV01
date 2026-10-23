<?php

namespace App\Service\genererPdf\bap;

use App\Controller\Traits\FormatageTrait;
use App\Dto\ddp\DdpDto;
use App\Service\genererPdf\GeneratePdf;
use TCPDF;

/**
 * Page de garde BAP MAGASIN (demande de paiement magasin après arrivage d'une
 * commande sans demande de paiement à l'avance) : même mise en page que le
 * BAP APPRO (GenererPdfBonAPayer), sans récapitulatif de l'OR ni de la DA.
 */
class GenererPdfBapMagasin extends GeneratePdf
{
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

        $commandes = $dto->donneesBapMagasin['commandes'] ?? [];
        $devise = $dto->devise ?? '';
        $this->renderResumeBc($pdf, $w100, $commandes);
        $this->renderHistoriqueLivraison($pdf, $commandes, $devise);
        $this->renderHistoriqueDdp($pdf, $commandes, $devise);
        $this->renderFactures($pdf, $dto->donneesBapMagasin['factures'] ?? [], $devise);
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

    /**
     * Fournisseur (commun à toutes les commandes) puis une ligne par BC avec son information de validation
     */
    private function renderResumeBc(TCPDF $pdf, int $w100, array $commandes): void
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, 'RESUME DES BC', 0);
        $infoFrn = $commandes ? (reset($commandes)['infoBc'] ?? []) : [];
        $this->addInfoLine($pdf, 'Nom fournisseur', (string) ($infoFrn['nom_fournisseur'] ?? '-'), $w100);
        $this->addInfoLine($pdf, 'N° fournisseur', (string) ($infoFrn['num_fournisseur'] ?? '-'), $w100);

        $lignes = [];
        foreach ($commandes as $numCde => $commande) {
            $infoBC = $commande['infoBc'] ?? [];
            $validation = $commande['validation'] ?? null;
            $dateSoumission = $validation['dateSoumission'] ?? null;
            $devise = $infoBC['devise'] ?? '';
            $lignes[] = [
                ['center', '9%', $infoBC['num_cde'] ?? $numCde],
                ['left', '15%', $infoBC['libelle_cde'] ?? '-'],
                ['center', '9%', !empty($infoBC['date_cde']) ? date('d/m/Y', strtotime($infoBC['date_cde'])) : '-'],
                ['center', '8%', ($infoBC['succ_cde'] ?? '-') . ' / ' . ($infoBC['serv_cde'] ?? '-')],
                ['center', '8%', $infoBC['nom_ope'] ?? '-'],
                ['center', '6%', $infoBC['type_cde'] ?? '-'],
                ['right', '12%', $this->formaterPrix($infoBC['mtn_cde'] ?? 0) . " $devise"],
                ['right', '12%', $this->formaterPrix($infoBC['ttc_cde'] ?? 0) . " $devise"],
                ['left', '13%', ($validation['statut'] ?? 'Non soumis à validation') . (isset($validation['version']) ? " (v{$validation['version']})" : '')],
                ['center', '8%', $dateSoumission instanceof \DateTimeInterface ? $dateSoumission->format('d/m/Y') : '-'],
            ];
        }

        $this->renderTable($pdf, null, 'Aucune commande', ['N° cde', 'Référence', 'Date cde', 'Succ / Serv', 'Opérateur', 'Nature', 'Montant HT', 'Montant TTC', 'Statut validation', 'Date soumis.'], $lignes);
    }

    private function renderHistoriqueLivraison(TCPDF $pdf, array $commandes, string $devise): void
    {
        $lignes = [];
        foreach ($commandes as $numCde => $commande) {
            foreach ($commande['livraisons'] ?? [] as $livraison) {
                $lignes[] = [
                    ['center', '12%', $numCde],
                    ['center', '13%', $livraison['num_liv'] ?? ''],
                    ['center', '15%', $livraison['numero_facture_ips'] ?? ''],
                    ['left', '22%', $livraison['ref_fac_bl'] ?? ''],
                    ['center', '15%', !empty($livraison['date_clot']) ? date('d/m/Y', strtotime($livraison['date_clot'])) : '-'],
                    ['right', '23%', $this->formaterPrix($livraison['montant_fac_bl'] ?? 0)],
                ];
            }
        }

        $this->renderTable($pdf, 'RECAPITULATIF DES LIVRAISONS', 'Aucune livraison', ['N° commande', 'N° Livraison IPS', 'N° Facture IPS', 'Référence', 'Date livraison IPS', "Montant ($devise)"], $lignes);
    }

    private function renderHistoriqueDdp(TCPDF $pdf, array $commandes, string $devise): void
    {
        $lignes = [];
        foreach ($commandes as $numCde => $commande) {
            foreach ($commande['ddps'] ?? [] as $ddp) {
                $lignes[] = [
                    ['center', '11%', $numCde],
                    ['center', '10%', $ddp['date']],
                    ['center', '14%', $ddp['numero']],
                    ['left', '24%', $ddp['type']],
                    ['center', '8%', $this->formaterPrix($ddp['ratio'])],
                    ['right', '14%', $this->formaterPrix($ddp['montant'])],
                    ['left', '19%', $ddp['statut']],
                ];
            }
        }

        $this->renderTable($pdf, 'RECAPITULATIF DES DEMANDES DE PAIEMENT', 'Aucune demande de paiement', ['N° commande', 'Date', 'Numéro', 'Type', '%', "Montant ($devise)", 'Statut'], $lignes);
    }

    private function renderFactures(TCPDF $pdf, array $factures, string $devise): void
    {
        $lignes = array_map(fn($facture) => [
            ['left', '40%', $facture['Numero_Facture'] ?? ''],
            ['left', '30%', $facture['Numero_PO'] ?? ''],
            ['right', '30%', $this->formaterPrix($facture['Montant_Facture'] ?? 0)],
        ], $factures);

        $this->renderTable($pdf, 'INFO FACTURE FOURNISSEUR', 'Aucune facture', ['N° facture', 'N° commande', "Montant facture ($devise)"], $lignes);
    }

    private function renderTable(TCPDF $pdf, ?string $titre, string $messageVide, array $entetes, array $lignes): void
    {
        if ($titre !== null) {
            $pdf->ln(2);
            $this->renderSectionTitle($pdf, $titre, 0);
        }
        if (empty($lignes)) {
            $pdf->Cell(0, 6, $messageVide, 0, 1);
            $pdf->Ln(2);
            return;
        }
        $pdf->writeHTML($this->tableHtml($entetes, $lignes));
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
        $html = '<table border="0" cellpadding="2" cellspacing="0" style="font-size: 7px;"><thead><tr style="background-color: #D3D3D3; font-weight: bold;">';
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
        $pdf->setFont('helvetica', 'B', 8);
        $pdf->Cell($width, 6, $title, 0, $endline);
        $pdf->setFont('helvetica', '', 8);
    }
}
