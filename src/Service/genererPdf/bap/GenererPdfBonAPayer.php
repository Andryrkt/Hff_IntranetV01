<?php

namespace App\Service\genererPdf\bap;

use App\Controller\Traits\FormatageTrait;
use TCPDF;
use App\Entity\da\DemandeAppro;
use App\Service\genererPdf\GeneratePdf;
use App\Service\genererPdf\PdfTableGeneratorFlexible;

class GenererPdfBonAPayer extends GeneratePdf
{
    use FormatageTrait;

    /**
     * Fonction pour générer le PDF du bon à payer
     */
    public function genererPageDeGarde(array $infoBC, array $infoValidationBC, array $infoMateriel, array $dataRecapOR, DemandeAppro $demandeAppro, array $infoFacBl): string
    {
        $pdf = $this->initPDF();
        $w100 = $this->getUsableWidth($pdf);

        $this->renderInfoBCAndValidation($pdf, $w100, $infoBC, $infoValidationBC);
        $this->renderInfoMateriel($pdf, $w100, $infoMateriel);
        $this->renderRecapOR($pdf, $dataRecapOR);
        $this->renderRecapDA($pdf, $w100, $demandeAppro);
        $this->renderInfoFACBL($pdf, $w100, $infoFacBl);

        // Sauvegarder le PDF
        return $this->savePDF($pdf, $demandeAppro->getNumeroDemandeAppro(), $infoBC["num_cde"]);
    }

    private function initPDF(): TCPDF
    {
        $pdf = new TCPDF();
        $pdf->setMargins(20, 15, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $this->renderTitle($pdf, 'BAP APPRO');
        return $pdf;
    }

    private function renderTitle(TCPDF $pdf, string $title)
    {
        $pdf->setFont('helvetica', 'B', 20);
        $pdf->Cell(0, 6, $title, 0, 1, 'C');
        $pdf->Ln(5, true);
    }

    private function renderInfoSection(TCPDF $pdf, string $title1, string $title2, callable $callback)
    {
        $pdf->Ln(3);
        $pdf->setFont('helvetica', 'B', 9);

        if ($title2) {
            $w100 = $this->getUsableWidth($pdf);
            $pdf->Cell($w100 / 2, 5, $title1);
            $pdf->Cell($w100 / 2, 5, $title2, 0, 1);
        } else {
            $pdf->Cell(0, 5, $title1, 0, 1);
        }

        $pdf->Ln(3);

        $pdf->setFont('helvetica', '', 9);
        $callback();
    }

    private function renderInfoBCAndValidation(TCPDF $pdf, int $w100, array $infoBC, array $infoValidationBC)
    {
        $this->renderInfoSection($pdf, 'INFORMATION DU BC', 'INFORMATION VALIDATION BC', function () use ($pdf, $w100, $infoBC, $infoValidationBC) {
            $this->addInfoLine($pdf, 'Fournisseur', $infoBC["nom_fournisseur"], $w100 / 2 - 6, 30, 0, 0);
            $this->addInfoLine($pdf, 'Nom Validateur', $infoValidationBC["validateur"] ?? "-", $w100 / 2, 30, 0);

            $this->addInfoLine($pdf, 'N° FRN', $infoBC["num_fournisseur"], $w100 / 2 - 6, 30, 0, 0);
            $this->addInfoLine($pdf, 'Date Validation', $infoValidationBC["dateValidation"] ? $infoValidationBC["dateValidation"]->format("d/m/Y") : "-", $w100 / 2, 30, 0);
            $pdf->Ln(3);

            $this->addInfoLine($pdf, 'Téléphone', $infoBC["tel_fournisseur"], $w100, 30, 0);

            // Adresse
            $this->renderAdresseFournisseur($pdf, $w100, $infoBC);

            $fields = [
                'N°'                => $infoBC["num_cde"] ?? "-",
                'Date'              => $infoBC["date_cde"] ? date("d/m/Y", strtotime($infoBC["date_cde"])) : "-",
                'Succursale'        => $infoBC["succ_cde"] ?? "-",
                'Service'           => $infoBC["serv_cde"] ?? "-",
                'Opérateur'         => $infoBC["nom_ope"] ?? "-",
                'N° cmd externe'    => $infoBC["num_cde_ext"] ?? "-",
                'Référence'         => $infoBC["libelle_cde"] ?? "-",
                'Montant HT'        => $this->formaterPrix($infoBC["mtn_cde"] ?? 0),
                'Montant TTC'       => $this->formaterPrix($infoBC["ttc_cde"] ?? 0),
                'Nature de l’achat' => $infoBC["type_cde"] ?? "-"
            ];

            foreach ($fields as $label => $value) {
                $this->addInfoLine($pdf, $label, $value, $w100, 30, 0);
                if ($label === 'Date') $pdf->Ln(3);
            }
        });
    }

    private function renderAdresseFournisseur(TCPDF $pdf, $w100, array $infoBC)
    {
        $this->addInfoLine($pdf, 'Adresse FRN', '', $w100, 30, 0, 1);

        $adresse = [
            $infoBC["adr1_fournisseur"],
            $infoBC["adr2_fournisseur"],
            $infoBC["ptt_fournisseur"] . " " . $infoBC["adr4_fournisseur"]
        ];

        foreach ($adresse as $line) {
            $pdf->Cell(15, 5);
            $pdf->Cell($w100 - 15, 5, $line, 0, 1);
        }
    }

    private function renderInfoMateriel(TCPDF $pdf, $w100, array $infoMateriel)
    {
        $this->renderInfoSection($pdf, 'LA COMMANDE CONCERNE LE MATÉRIEL SUIVANT :', '', function () use ($pdf, $w100, $infoMateriel) {
            $this->addInfoLine($pdf, '', $infoMateriel["designation"] ?? "-", $w100, '');
            $this->addInfoLine($pdf, 'N° série', $infoMateriel["numserie"] ?? "-", $w100, 13);
            $this->addInfoLine($pdf, 'Identité', $infoMateriel["identite"] ?? "-", $w100, 13);
        });
    }

    private function renderRecapOR(TCPDF $pdf, array $dataRecapOR)
    {
        $this->renderInfoSection($pdf, 'RECAPITULATION DE L’OR', '', function () use ($pdf, $dataRecapOR) {
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
        });
    }

    private function renderRecapDA(TCPDF $pdf, $w100, DemandeAppro $demandeAppro)
    {
        $this->renderInfoSection($pdf, 'RECAPITULATION DE LA DA', '', function () use ($pdf, $w100, $demandeAppro) {
            $this->addInfoLine($pdf, 'N° DA', $demandeAppro->getNumeroDemandeAppro(), $w100, 25);
            $this->addInfoLine($pdf, 'Date de création', $demandeAppro->getDateCreation()->format('d/m/Y'), $w100, 25);
            $this->addInfoLine($pdf, 'Objet', $demandeAppro->getObjetDal(), $w100, 25);
            $this->addInfoLine($pdf, 'Agence – service émetteur', $demandeAppro->getAgenceServiceEmetteur(), $w100, 39);
            $this->addInfoLine($pdf, 'Agence et service débiteur', $demandeAppro->getAgenceServiceDebiteur(), $w100, 39);
        });
    }

    private function renderInfoFacBl(TCPDF $pdf, $w100, array $infoFacBl)
    {
        $this->renderInfoSection($pdf, 'INFO BL / FAC FOURNISSEUR', '', function () use ($pdf, $w100, $infoFacBl) {
            $this->addInfoLine($pdf, 'Réf', $infoFacBl["refBlFac"] ?? "-", $w100 / 2, 8, 6, 0);
            $this->addInfoLine($pdf, 'N° livraison IPS', $infoFacBl["numLivIPS"] ?? "-", $w100 / 2, 27);
            $this->addInfoLine($pdf, 'Date', $infoFacBl["dateBlFac"] ? $infoFacBl["dateBlFac"]->format('d/m/Y') : "-", $w100 / 2, 8, 6, 0);
            $this->addInfoLine($pdf, 'Date livraison IPS', $infoFacBl["dateLivIPS"] ? date("d/m/Y", strtotime($infoFacBl["dateLivIPS"])) : "-", $w100 / 2, 27);
        });
    }

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

    private function addInfoLine(TCPDF $pdf, string $label, string $value, $wTotal, $labelWidth = 35, $indent = 6, $endLine = 1)
    {
        if ($indent > 0) $pdf->Cell($indent, 5, '', 0, 0);
        $pdf->Cell(6, 5, '-', 0, 0);

        if ($label !== '') {
            $pdf->Cell($labelWidth, 5, $label, 0, 0);
            $pdf->Cell($wTotal - $labelWidth - $indent, 5, ": $value", 0, $endLine);
        } else {
            $pdf->Cell($wTotal - $indent, 5, $value, 0, $endLine);
        }
    }
}
