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
        $generator = new PdfTableGenerator();

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

        $this->addDetailsBlock($pdf, $detailsBloc, 'helvetica', 45, 50, 6, 2, 5);

        $this->generateSeparateLine($pdf);

        $this->addTitle($pdf, 'FORFAIT client');
        $pdf->setFont('helvetica', '', 12);
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

        $html1 = $generator->generateTable($headerConfig1, $montantPdf['avantApres'], $montantPdf['totalAvantApres']);
        $pdf->writeHTML($html1, true, false, true, false, '');

        $this->addTitle($pdf, 'Contrôle à faire (par rapport à la dernière version du FORFAIT) :');

        $details1 = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSupp']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSupp']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSupp']['nbrModif'],
        ];

        $this->addSummaryDetails($pdf, $details1);

        $this->addTitle($pdf, 'VENTE client');
        $pdf->setFont('helvetica', '', 12);
        // ================================================================================================
        $headerConfig2 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'libelleItv', 'label' => 'Libellé ITV', 'width' => 200, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'nbLigAv', 'label' => 'Nb Lig av', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'nbLigAp', 'label' => 'Nb Lig ap', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotalAv', 'label' => 'Mtt Total av', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttTotalAp', 'label' => 'Mtt Total ap', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'statut', 'label' => 'Statut', 'width' => 40, 'style' => 'font-weight: bold; text-align: center;'],
        ];

        $html2 = $generator->generateTable($headerConfig2, $montantPdf['avantApres'], $montantPdf['totalAvantApres']);
        $pdf->writeHTML($html2, true, false, true, false, '');

        $this->addTitle($pdf, 'Contrôle à faire (par rapport à la dernière version du FORFAIT) :');

        $details2 = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSupp']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSupp']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSupp']['nbrModif'],
            'Montant total modifié' => $this->formatNumber($montantPdf['nombreStatutNouvEtSupp']['mttModif']),
        ];

        $this->addSummaryDetails($pdf, $details2);

        $this->addTitle($pdf, 'VARIATION des prix de vente des références (3 dernières ventes facturées de plus anciennes au plus récentes)');
        $pdf->setFont('helvetica', '', 12);
        $headerConfig3 = [
            ['key' => 'lineType', 'label' => 'Type de ligne', 'width' => 60, 'style' => 'font-weight: bold;'],
            ['key' => 'cst', 'label' => 'CST', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'refPieces', 'label' => 'Réf. Pièce', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'pu1', 'label' => 'PU 1', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'datePu1', 'label' => 'Date PU 1', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'pu2', 'label' => 'PU 2', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'datePu2', 'label' => 'Date PU 2', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'pu3', 'label' => 'PU 3', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'datePu3', 'label' => 'Date PU 3', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
        ];
        $html3 = $generator->generateTable($headerConfig3, $montantPdf['avantApres'], $montantPdf['totalAvantApres']);
        $pdf->writeHTML($html3, true, false, true, false, '');

        $this->addTitle($pdf, 'Récapitulation par type de ligne pour les ventes :');
        $pdf->setFont('helvetica', '', 12);
        $headerConfig4 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotal', 'label' => 'Mtt Total', 'width' => 70, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttPieces', 'label' => 'Mtt Pièces', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttMo', 'label' => 'Mtt MO', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttSt', 'label' => 'Mtt ST', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttLub', 'label' => 'Mtt LUB', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttAutres', 'label' => 'Mtt Autres', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
        ];


        $html4 = $generator->generateTable($headerConfig4, $montantPdf['recapOr'], $montantPdf['totalRecapOr']);
        $pdf->writeHTML($html4, true, false, true, false, '');

        $headerConfig5 = [
            ['key' => 'itv', 'label' => 'MONTANT DEVIS AVANT :', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotal', 'label' => '999 999 999,99', 'width' => 70, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttPieces', 'label' => 'MONTANT DEVIS APRES :', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttMo', 'label' => '999 999 999,99', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttSt', 'label' => 'MARGE', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttLub', 'label' => 'MARGE', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
        ];

        $footer = ['TOTAL FORFAIT + VENTE ', '', 'TOTAL FORFAIT + VENTE ', 'AV : 99 %', 'AP : 99 %'];

        $html5 = $generator->generateTable($headerConfig5, [], $footer);
        $pdf->writeHTML($html5, true, false, true, false, '');

        $this->generateSeparateLine($pdf);

        $this->addTitle($pdf, 'CESSION INTERNE');
        $pdf->setFont('helvetica', '', 12);
        // ================================================================================================
        $headerConfig6 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'libelleItv', 'label' => 'Libellé ITV', 'width' => 200, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'nbLigAv', 'label' => 'Nb Lig av', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'nbLigAp', 'label' => 'Nb Lig ap', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotalAv', 'label' => 'Mtt Total av', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttTotalAp', 'label' => 'Mtt Total ap', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'statut', 'label' => 'Statut', 'width' => 40, 'style' => 'font-weight: bold; text-align: center;'],
        ];

        $html6 = $generator->generateTable($headerConfig6, $montantPdf['avantApres'], $montantPdf['totalAvantApres']);
        $pdf->writeHTML($html6, true, false, true, false, '');

        $this->addTitle($pdf, 'Contrôle à faire (par rapport à la dernière version du FORFAIT) :');

        $details3 = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSupp']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSupp']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSupp']['nbrModif'],
            'Montant total modifié' => $this->formatNumber($montantPdf['nombreStatutNouvEtSupp']['mttModif']),
        ];

        $this->addSummaryDetails($pdf, $details3);

        $this->addTitle($pdf, 'Récapitulation de la CESSION :');
        $pdf->setFont('helvetica', '', 12);
        $headerConfig7 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotal', 'label' => 'Mtt Total', 'width' => 70, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttPieces', 'label' => 'Mtt Pièces', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttMo', 'label' => 'Mtt MO', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttSt', 'label' => 'Mtt ST', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttLub', 'label' => 'Mtt LUB', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttAutres', 'label' => 'Mtt Autres', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
        ];

        $html7 = $generator->generateTable($headerConfig7, $montantPdf['recapOr'], $montantPdf['totalRecapOr']);
        $pdf->writeHTML($html7, true, false, true, false, '');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(118, 2);
        $pdf->Cell(35, 6, $email, 0, 0, 'L');


        $Dossier = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/dev/';
        $filePath = $Dossier . 'devis_ctrl_' . $devisSoumis->getNumeroDevis() . '_' . $devisSoumis->getNumeroVersion() . '.pdf';
        $pdf->Output($filePath, 'I');
    }
}
