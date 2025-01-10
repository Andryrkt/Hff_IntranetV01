<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\FormatageTrait;
use App\Entity\dit\DitDevisSoumisAValidation;
use TCPDF;

class GenererPdfDevisSoumisAValidation extends GeneratePdf
{
    use FormatageTrait;

    function GenererPdfDevisVente(DitDevisSoumisAValidation $devisSoumis, array $montantPdf, array $quelqueaffichage, string $email)
    {
        $pdf = new TCPDF();

        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 17);
        $pdf->Cell(0, 6, 'Validation DEVIS VENTE', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $detailsBloc = [
            'Date soumission' => $devisSoumis->getDateHeureSoumission()->format('d/m/Y'),
            'Numéro DIT' => $devisSoumis->getNumeroDit(),
            'Numéro DEVIS' => $devisSoumis->getNumeroDevis(),
            'Version à valider' => $devisSoumis->getNumeroVersion(),
            'Sortie magasin' => $quelqueaffichage['sortieMagasin'] ?? 'N/A',
            'Achat locaux' => $quelqueaffichage['achatLocaux'] ?? 'N/A',
        ];

        $this->addDetailsBlock($pdf, $detailsBloc);


        // ================================================================================================
        $headerConfig1 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'libelleItv', 'label' => 'Libellé ITV', 'width' => 200, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'nbLigAv', 'label' => 'Nb Lig av', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'nbLigAp', 'label' => 'Nb Lig ap', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotalAv', 'label' => 'Mtt Total av', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttTotalAp', 'label' => 'Mtt Total ap', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'statut', 'label' => 'Statut', 'width' => 40, 'style' => 'font-weight: bold; text-align: center;'],
        ];

        $generator = new PdfTableGenerator();
        $html1 = $generator->generateTable($headerConfig1, $montantPdf['avantApres'], $montantPdf['totalAvantApres']);
        $pdf->writeHTML($html1, true, false, true, false, '');


        //$pdf->Ln(10, true);
        //===========================================================================================
        //Titre: Controle à faire
        $this->addTitle($pdf, 'Contrôle à faire (par rapport dernière version) :');

        $details = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSupp']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSupp']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSupp']['nbrModif'],
            'Montant total modifié' => $this->formatNumber($montantPdf['nombreStatutNouvEtSupp']['mttModif']),
        ];

        $this->addSummaryDetails($pdf, $details);

        //==========================================================================================================
        //Titre: Récapitulation de l'OR
        $this->addTitle($pdf, 'Récapitulation du devis');

        $pdf->setFont('helvetica', '', 12);
        $headerConfig2 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotal', 'label' => 'Mtt Total', 'width' => 70, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttPieces', 'label' => 'Mtt Pièces', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttMo', 'label' => 'Mtt MO', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttSt', 'label' => 'Mtt ST', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttLub', 'label' => 'Mtt LUB', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttAutres', 'label' => 'Mtt Autres', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
        ];


        $html2 = $generator->generateTable($headerConfig2, $montantPdf['recapOr'], $montantPdf['totalRecapOr']);
        $pdf->writeHTML($html2, true, false, true, false, '');


        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(118, 2);
        $pdf->Cell(35, 6, $email, 0, 0, 'L');


        $Dossier = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/dev/';
        $filePath = $Dossier . 'devis_ctrl_' . $devisSoumis->getNumeroDevis() . '_' . $devisSoumis->getNumeroVersion() . '.pdf';
        $pdf->Output($filePath, 'F');
    }

    function GenererPdfDevisForfait(DitDevisSoumisAValidation $devisSoumis, array $montantPdf, array $quelqueaffichage, string $email)
    {
        $pdf = new TCPDF();

        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 17);
        $pdf->Cell(0, 6, 'Validation DEVIS FORFAIT', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $detailsBloc = [
            'Date soumission' => $devisSoumis->getDateHeureSoumission()->format('d/m/Y'),
            'Numéro du client' => '9999999' . ' - ' . 'nom_du_client',
            'Numéro DIT' => $devisSoumis->getNumeroDit() . ' - ' . 'objet_du_DIT',
            'Numéro DEVIS' => $devisSoumis->getNumeroDevis(),
            'Version à valider' => $devisSoumis->getNumeroVersion(),
            'Sortie magasin' => $quelqueaffichage['sortieMagasin'] ?? 'N/A',
            'Achat locaux' => $quelqueaffichage['achatLocaux'] ?? 'N/A',
        ];

        $this->addDetailsBlock($pdf, $detailsBloc);

        $this->generateSeparateLine($pdf);

        /* // ================================================================================================
        $headerConfig1 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'libelleItv', 'label' => 'Libellé ITV', 'width' => 200, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'nbLigAv', 'label' => 'Nb Lig av', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'nbLigAp', 'label' => 'Nb Lig ap', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotalAv', 'label' => 'Mtt Total av', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttTotalAp', 'label' => 'Mtt Total ap', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'statut', 'label' => 'Statut', 'width' => 40, 'style' => 'font-weight: bold; text-align: center;'],
        ];

        $generator = new PdfTableGenerator();
        $html1 = $generator->generateTable($headerConfig1, $montantPdf['avantApres'], $montantPdf['totalAvantApres']);
        $pdf->writeHTML($html1, true, false, true, false, '');


        //$pdf->Ln(10, true);
        //===========================================================================================
        //Titre: Controle à faire
        $this->addTitle($pdf, 'Contrôle à faire (par rapport dernière version) :');

        $details = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSupp']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSupp']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSupp']['nbrModif'],
            'Montant total modifié' => $this->formatNumber($montantPdf['nombreStatutNouvEtSupp']['mttModif']),
        ];

        $this->addSummaryDetails($pdf, $details);

        //==========================================================================================================
        //Titre: Récapitulation de l'OR
        $this->addTitle($pdf, 'Récapitulation de l\'OR');

        $pdf->setFont('helvetica', '', 12);
        $headerConfig2 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotal', 'label' => 'Mtt Total', 'width' => 70, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttPieces', 'label' => 'Mtt Pièces', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttMo', 'label' => 'Mtt MO', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttSt', 'label' => 'Mtt ST', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttLub', 'label' => 'Mtt LUB', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttAutres', 'label' => 'Mtt Autres', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
        ];


        $html2 = $generator->generateTable($headerConfig2, $montantPdf['recapOr'], $montantPdf['totalRecapOr']);
        $pdf->writeHTML($html2, true, false, true, false, '');


        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(118, 2);
        $pdf->Cell(35, 6, $email, 0, 0, 'L'); */


        $Dossier = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/dev/';
        $filePath = $Dossier . 'devis_ctrl_' . $devisSoumis->getNumeroDevis() . '_' . $devisSoumis->getNumeroVersion() . '.pdf';
        $pdf->Output($filePath, 'I');
    }
}
