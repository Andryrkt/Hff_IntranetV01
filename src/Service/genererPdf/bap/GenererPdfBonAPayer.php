<?php

namespace App\Service\genererPdf\bap;

use App\Controller\Traits\FormatageTrait;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\da\DemandeAppro;
use App\Service\genererPdf\da\PdfTableHistoriqueLivraisonBAP;
use App\Service\genererPdf\ddp\PdfTableHistoriqueDdpBAP;
use App\Service\genererPdf\GeneratePdf;
use App\Service\genererPdf\PdfTableGeneratorFlexible;
use TCPDF;

class GenererPdfBonAPayer extends GeneratePdf
{
    private const LARGEUR_GAUCHE = 0.6;
    private const LARGEUR_DROITE = 0.4;

    use FormatageTrait;

    /**
     * Fonction pour générer le PDF du bon à payer
     */
    public function genererPageDeGarde(
        array $infoValidationBC,
        array $infoMateriel,
        array $dataRecapOR,
        array $historiqueLivraison,
        DemandeAppro $demandeAppro,
        DaSoumissionFacBlDto $dto,
        array $infoFacBl,
        ?string $mail
    ): string {
        $infoBC = $dto->infoBc;
        $pdf = $this->initPDF();

        $this->renderHeader($pdf, $mail, $dto);
        $w100 = $this->getUsableWidth($pdf);

        $this->renderInfoBcAndMateriel($pdf, $w100, $infoBC, $infoValidationBC, $infoMateriel, $demandeAppro->getDaTypeId() === DemandeAppro::TYPE_DA_AVEC_DIT);
        $this->renderRecapOR($pdf, $dataRecapOR, $dto);
        $this->renderRecapDA($pdf, $w100, $demandeAppro);
        $this->renderInfoFACBL($pdf, $w100, $infoFacBl);
        $this->renderHistoriqueLivraison($pdf, $historiqueLivraison, $dto->devise);
        $this->renderHistoriqueDdp($pdf, $dto->demandePaiementDto->ddpRecap, $dto->devise);
        $this->renderMontantBap($pdf, $dto);

        // Sauvegarder le PDF
        return $this->savePDF($pdf, $demandeAppro->getNumeroDemandeAppro(), $infoBC["num_cde"], "I");
    }

    private function initPDF(): TCPDF
    {
        $pdf = new TCPDF();
        $pdf->setMargins(10, 10, 10);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        return $pdf;
    }

    private function renderHeader(TCPDF $pdf, ?string $userMail, DaSoumissionFacBlDto $dto): void
    {
        $logoPath =  $_ENV['BASE_PATH_LONG'] . '/Views/assets/logoHff.jpg';
        $pdf->setAbsY(11);
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(60);
        $pdf->setFont('helvetica', 'B', 22);
        $pdf->Cell(110, 12, 'BAP APPRO', 0, 0, 'C', false, '', 0, false, 'T', 'M');

        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 9);
        $pdf->SetY(4);
        $pdf->Cell(0, 6, "email : $userMail", 0, 0, 'R');

        $pdf->setAbsXY(170, 11);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $dto->numeroBap, 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $dto->dateDemande->format('d/m/Y'), 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7);
    }

    private function renderInfoBcAndMateriel(TCPDF $pdf, int $w100, array $infoBC, array $infoValidationBC, array $infoMateriel, bool $daViaOr)
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, 'RESUME DU BC', $w100 * self::LARGEUR_GAUCHE - 3, 0);
        $this->renderSectionTitle($pdf, 'INFORMATION VALIDATION BC', $w100 * self::LARGEUR_DROITE);

        $this->addInfoLine($pdf, 'Nom fournisseur', substr($infoBC["nom_fournisseur"] ?? "-", 0, 33), $w100 * self::LARGEUR_GAUCHE - 6, 35, 0);
        $this->addInfoLine($pdf, 'Nom Validateur', $infoValidationBC["validateur"] ?? "-", $w100 * self::LARGEUR_DROITE, 25, 1);

        $this->addInfoLine($pdf, 'N° fournisseur', $infoBC["num_fournisseur"] ?? "-", $w100 * self::LARGEUR_GAUCHE - 6, 35, 0);
        $dateValidation = isset($infoValidationBC["dateValidation"]) ? $infoValidationBC["dateValidation"]->format("d/m/Y") : "-";
        $this->addInfoLine($pdf, 'Date Validation', $dateValidation, $w100 * self::LARGEUR_DROITE, 25, 1);

        $pdf->ln(2);
        $this->addInfoLine($pdf, 'N° commande', $infoBC["num_cde"] ?? "-", $w100 * self::LARGEUR_GAUCHE - 6, 35, 0);
        $this->renderSectionTitle($pdf, 'LA COMMANDE CONCERNE LE MATÉRIEL SUIVANT :', $w100 * self::LARGEUR_DROITE, 0, $daViaOr);
        $pdf->Ln();

        $this->addInfoLine($pdf, 'N° demande appro', $infoBC["num_cde_ext"] ?? "-", $w100 * self::LARGEUR_GAUCHE - 6, 35, 0);
        $this->addInfoLine($pdf, '', $infoMateriel["designation"] ?? "-", $w100 * self::LARGEUR_DROITE, 25, 0, $daViaOr);
        $pdf->Ln();

        $this->addInfoLine($pdf, 'Référence commande', $infoBC["libelle_cde"] ?? "-", $w100 * self::LARGEUR_GAUCHE - 6, 35, 0);
        $this->addInfoLine($pdf, 'N° série', $infoMateriel["numserie"] ?? "-", $w100 * self::LARGEUR_DROITE, 25, 0, $daViaOr);
        $pdf->Ln();

        $this->addInfoLine($pdf, 'Date commande', $infoBC["date_cde"] ? date("d/m/Y", strtotime($infoBC["date_cde"])) : "-", $w100 * self::LARGEUR_GAUCHE - 6, 35, 0);
        $this->addInfoLine($pdf, 'Identité', $infoMateriel["identite"] ?? "-", $w100 * self::LARGEUR_DROITE, 25, 0, $daViaOr);
        $pdf->Ln();

        $fields = [
            'Succursale'         => $infoBC["succ_cde"] ?? "-",
            'Service'            => $infoBC["serv_cde"] ?? "-",
            'Opérateur'          => $infoBC["nom_ope"] ?? "-",
            'Montant HT'         => $this->formaterPrix($infoBC["mtn_cde"] ?? 0) . " " . ($infoBC["devise"] ?? ""),
            'Montant TTC'        => $this->formaterPrix($infoBC["ttc_cde"] ?? 0) . " " . ($infoBC["devise"] ?? ""),
            'Nature de l’achat'  => $infoBC["type_cde"] ?? "-"
        ];

        foreach ($fields as $label => $value) {
            $this->addInfoLine($pdf, $label, $value, $w100, 35, 1);
        }
    }

    private function renderRecapOR(TCPDF $pdf, array $dataRecapOR, DaSoumissionFacBlDto $dto)
    {
        $pdf->ln(2);
        if (empty($dataRecapOR)) return;

        $numOR = $dto->numeroOR;
        $numDIT = $dto->numeroDemandeDit;
        $numDIT = $numDIT ? "- $numDIT" : "";

        $this->renderSectionTitle($pdf, "RECAPITULATIF DE L’OR $numOR $numDIT", 0);
        $this->addInfoLine($pdf, 'Utilisateur Créateur', $dataRecapOR["createur_or"] ?? "-", 120, 30);
        $pdf->Ln(2);
        $tableGenerator = new PdfTableGeneratorFlexible();
        $tableGenerator->setOptions([
            'table_attributes' => 'border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px;"',
            'header_row_style' => 'background-color: #D3D3D3;',
            'footer_row_style' => 'background-color: #D3D3D3;'
        ]);

        $pdf->writeHTML(
            $tableGenerator->generateTable(
                $dataRecapOR["header"],
                $dataRecapOR["body"],
                $dataRecapOR["footer"]
            )
        );
    }

    private function renderRecapDA(TCPDF $pdf, int $w100, DemandeAppro $demandeAppro)
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, "RECAPITULATIF DE LA DA", $w100);
        $this->addInfoLine($pdf, 'N° DA', $demandeAppro->getNumeroDemandeAppro(), $w100, 25);
        $this->addInfoLine($pdf, 'Date de création', $demandeAppro->getDateCreation()->format('d/m/Y'), $w100, 25);
        $this->addInfoLine($pdf, 'Objet', $demandeAppro->getObjetDal(), $w100, 25);
        $this->addInfoLine($pdf, "Utilisateur demandeur", $demandeAppro->getDemandeur(), $w100, 39);
        $this->addInfoLine($pdf, 'Agence – service émetteur', $demandeAppro->getAgenceServiceEmetteur(), $w100, 39);
        $this->addInfoLine($pdf, 'Agence – service débiteur', $demandeAppro->getAgenceServiceDebiteur(), $w100, 39);
    }

    private function renderInfoFacBl(TCPDF $pdf, int $w100, array $infoFacBl)
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, "INFO BL / FAC FOURNISSEUR", $w100);
        $this->addInfoLine($pdf, 'Réf', $infoFacBl["refBlFac"] ?? "-", $w100 / 2, 15, 0);
        $this->addInfoLine($pdf, 'N° livraison IPS', $infoFacBl["numLivIPS"] ?? "-", $w100 / 2, 27, 1);
        $this->addInfoLine($pdf, 'Date', $infoFacBl["dateBlFac"] ? $infoFacBl["dateBlFac"]->format('d/m/Y') : "-", $w100 / 2, 15, 0);
        $this->addInfoLine($pdf, 'Date livraison IPS', $infoFacBl["dateLivIPS"] ? date("d/m/Y", strtotime($infoFacBl["dateLivIPS"])) : "-", $w100 / 2, 27, 1);
    }

    private function renderHistoriqueLivraison(TCPDF $pdf, array $historiqueLivraison, string $devise)
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, "RECAPITULATIF DES LIVRAISONS", 0);
        if (empty($historiqueLivraison)) {
            $pdf->Cell(0, 6, "Aucune livraison", 0, 1);
            $pdf->Ln(2);
        } else {
            $tableGenerator = new PdfTableHistoriqueLivraisonBAP();
            $pdf->writeHTML($tableGenerator->generateTable($historiqueLivraison, $devise));
        }
    }

    private function renderHistoriqueDdp(TCPDF $pdf, array $historiqueDdp, string $devise)
    {
        $pdf->ln(2);
        $this->renderSectionTitle($pdf, "RECAPITULATIF DES DEMANDES DE PAIEMENT", 0);
        if (empty($historiqueDdp)) {
            $pdf->Cell(0, 6, "Aucune demande de paiement", 0, 1);
            $pdf->Ln(2);
        } else {
            $tableGenerator = new PdfTableHistoriqueDdpBAP();
            $pdf->writeHTML($tableGenerator->generateTable($historiqueDdp, $devise));
        }
    }

    private function renderMontantBap(TCPDF $pdf, DaSoumissionFacBlDto $dto): void
    {
        $pdf->ln(2);
        // Afficher le montant de la BAP avec le pourcentage à payer en rouge
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(30, 6, 'MONTANT BAP : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->setFont('helvetica', '', 10);
        $pdf->SetTextColor(255, 0, 0); // Rouge pour le pourcentage
        $pdf->Cell(20, 6, '(' . number_format($dto->demandePaiementDto->pourcentageAPayer, 2, ',', '') . '%)', 0, 0, 'R', false, '', 0, false, 'T', 'M');

        $pdf->SetTextColor(0, 0, 0); // Noir pour le reste
        $pdf->Cell(0, 6, number_format($dto->demandePaiementDto->montantAPayer, 2, ',', '.') . ' ' . $dto->devise, 0, 0, 'L', false, '', 0, false, 'T', 'M');
    }

    /**
     * 
     *
     * @param TCPDF $pdf
     * @param string $numDa
     * @param string|null $numCde
     * @param string $dest
     * @return string
     */
    private function savePDF(TCPDF $pdf, string $numDa, ?string $numCde = null, string $dest = "F"): string
    {
        // Obtention du chemin absolu du répertoire de travail
        $Dossier = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa";

        // Vérification si le répertoire existe, sinon le créer
        if (!is_dir($Dossier)) {
            if (!mkdir($Dossier, 0777, true)) {
                throw new \RuntimeException("Impossible de créer le répertoire : $Dossier");
            }
        }

        $numCde = $numCde ?? date("Y-m-d_H-i-s");

        $fileName = "$Dossier/BAP_{$numDa}_{$numCde}.pdf";
        $pdf->Output($fileName, $dest);
        return $fileName;
    }

    private function getUsableWidth(TCPDF $pdf)
    {
        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        return $w_total - $margins['left'] - $margins['right'];
    }

    private function addInfoLine(TCPDF $pdf, string $label, string $value, int $wTotal, int $labelWidth = 35, int $endLine = 1, bool $display = true)
    {
        if (!$display) return;

        $pdf->Cell(3, 6, '-', 0, 0);

        if ($label !== '') {
            $pdf->Cell($labelWidth, 6, $label, 0, 0);
            $pdf->Cell($wTotal - $labelWidth, 6, ": $value", 0, $endLine);
        } else {
            $pdf->Cell($wTotal, 6, $value, 0, $endLine);
        }
    }

    private function renderSectionTitle(TCPDF $pdf, string $title, int $width, int $endline = 1, bool $display = true): void
    {
        if (!$display) return;

        $pdf->setFont('helvetica', 'B', 9);
        $pdf->Cell($width, 6, $title, 0, $endline);
        $pdf->setFont('helvetica', '', 9);
    }
}
