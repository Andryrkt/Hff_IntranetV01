<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\FormatageTrait;
use App\Entity\dit\DitDevisSoumisAValidation;
use TCPDF;

class GenererPdfDevisSoumisAValidation extends GeneratePdf
{
    use FormatageTrait;

    function GenererPdfDevisVente(DitDevisSoumisAValidation $devisSoumis, array $montantPdf, array $quelqueaffichage, array $variationPrixRefPiece, string $email)
    {
        $pdf = new HeaderPdf($email);
        $generator = new PdfTableGenerator();

        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 17);
        $pdf->Cell(0, 6, 'Validation DEVIS VENTE', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $detailsBloc = [
            'Date soumission' => $devisSoumis->getDateHeureSoumission()->format('d/m/Y'),
            'Numéro du client' => $devisSoumis->getNumeroClient() . ' - ' . $devisSoumis->getNomClient(),
            'Numéro DIT' => $devisSoumis->getNumeroDit() . ' - ' . $devisSoumis->getObjetDit(),
            'Numéro DEVIS' => $devisSoumis->getNumeroDevis(),
            'Version à valider' => $devisSoumis->getNumeroVersion(),
            'Sortie magasin' => $quelqueaffichage['sortieMagasin'] ?? 'NON',
            'Achat locaux' => $quelqueaffichage['achatLocaux'] ?? 'NON',
        ];

        $this->addDetailsBlock($pdf, $detailsBloc, 'helvetica', 45, 50, 6, 2, 5);

        $this->generateSeparateLine($pdf);

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

        
        $html1 = $generator->generateTable($headerConfig1, $montantPdf['avantApresVte'], $montantPdf['totalAvantApresVte']);
        $pdf->writeHTML($html1, true, false, true, false, '');

        //===========================================================================================
        //Titre: Controle à faire
        $this->addTitle($pdf, 'Contrôle à faire (par rapport dernière version) :');

        $details = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSuppVte']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSuppVte']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSuppVte']['nbrModif'],
            'Montant total modifié' => $this->formatNumber($montantPdf['nombreStatutNouvEtSuppVte']['mttModif']),
        ];

        $this->addSummaryDetails($pdf, $details);

        //==========================================================================================================
        /**=====================================================
         * RECAPITULATION DE DEVIS
         *=====================================================*/
        //Titre
        $this->addTitle($pdf, 'Récapitulation du devis');
        //tableau
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

        $html2 = $generator->generateTable($headerConfig2, $montantPdf['recapVte'], $montantPdf['totalRecapVte']);
        $pdf->writeHTML($html2, true, false, true, false, '');
      //=====================================================================================================================
        /**=====================================================
         * tableau de variation de prix des références de pièces
         *=====================================================*/
        //Titre
        $this->addTitle($pdf, 'Variation de prix de pièce par référence');
        // Configuration des entêtes du tableau
        $headerConfig = [
            ['key' => 'cst', 'label' => 'CST', 'width' => 40, 'style' => 'text-align: center; font-weight: bold;'],
            ['key' => 'refPiece', 'label' => 'Ref. Pièce', 'width' => 50, 'style' => 'text-align: left; font-weight: bold;'],
            ['key' => 'pu1', 'label' => 'PU 1', 'width' => 60, 'style' => 'text-align: right; font-weight: bold;'],
            ['key' => 'datePu1', 'label' => 'Date PU 1', 'width' => 60, 'style' => 'text-align: center; font-weight: bold;'],
            ['key' => 'pu2', 'label' => 'PU 2', 'width' => 60, 'style' => 'text-align: right; font-weight: bold;'],
            ['key' => 'datePu2', 'label' => 'Date PU 2', 'width' => 60, 'style' => 'text-align: center; font-weight: bold;'],
            ['key' => 'pu3', 'label' => 'PU 3', 'width' => 60, 'style' => 'text-align: right; font-weight: bold;'],
            ['key' => 'datePu3', 'label' => 'Date PU 3', 'width' => 60, 'style' => 'text-align: center; font-weight: bold;'],
        ];

        // Générer le HTML du tableau
        $htmlVariationPrix = $generator->generateTable($headerConfig, $variationPrixRefPiece, []);
        $pdf->writeHTML($htmlVariationPrix, true, false, true, false, '');
        //====================================================================================================================
        // $pdf->SetTextColor(0, 0, 0);
        // $pdf->SetFont('helvetica', '', 10);
        // $pdf->SetXY(118, 2);
        // $pdf->Cell(35, 6, $email, 0, 0, 'L');
        //=====================================================================================================================

        $Dossier = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/dev/';
        $filePath = $Dossier . 'devis_ctrl_' . $devisSoumis->getNumeroDevis() . '_' . $devisSoumis->getNumeroVersion() . '.pdf';
        $pdf->Output($filePath, 'F');
    }


    /**
     * Methode de création de pdf pour le devis forfait
     *
     * @param DitDevisSoumisAValidation $devisSoumis
     * @param array $montantPdf
     * @param array $quelqueaffichage
     * @param array $variationPrixRefPiece
     * @param string $email
     * @return void
     */
    function GenererPdfDevisForfait(DitDevisSoumisAValidation $devisSoumis, array $montantPdf, array $quelqueaffichage, array $variationPrixRefPiece, string $email)
    {
        // $pdf = new TCPDF();
        $generator = new PdfTableGenerator();
        $pdf = new HeaderPdf($email);

        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 17);
        $pdf->Cell(0, 6, 'Validation DEVIS FORFAIT', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $detailsBloc = [
            'Date soumission' => $devisSoumis->getDateHeureSoumission()->format('d/m/Y'),
            'Numéro du client' => $devisSoumis->getNumeroClient() . ' - ' . $devisSoumis->getNomClient(),
            'Numéro DIT' => $devisSoumis->getNumeroDit() . ' - ' . $devisSoumis->getObjetDit(),
            'Numéro DEVIS' => $devisSoumis->getNumeroDevis(),
            'Version à valider' => $devisSoumis->getNumeroVersion(),
            'Sortie magasin' => $quelqueaffichage['sortieMagasin'] ?? 'NON',
            'Achat locaux' => $quelqueaffichage['achatLocaux'] ?? 'NON',
        ];

        $this->addDetailsBlock($pdf, $detailsBloc, 'helvetica', 45, 50, 6, 2, 5);

        $this->generateSeparateLine($pdf);

        // ================================================================================================
        //FORFAIT CLIENT
        $this->addTitle($pdf, 'FORFAIT client');
        $pdf->setFont('helvetica', '', 12);
        $headerConfig1 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'libelleItv', 'label' => 'Libellé ITV', 'width' => 200, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'nbLigAv', 'label' => 'Nb Lig av', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'nbLigAp', 'label' => 'Nb Lig ap', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotalAv', 'label' => 'Mtt Total av', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttTotalAp', 'label' => 'Mtt Total ap', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'statut', 'label' => 'Statut', 'width' => 40, 'style' => 'font-weight: bold; text-align: center;'],
        ];

        $html1 = $generator->generateTable($headerConfig1, $montantPdf['avantApresForfait'], $montantPdf['totalAvantApresForfait']);
        $pdf->writeHTML($html1, true, false, true, false, '');

        //=====================================================================================================
        $this->addTitle($pdf, 'Contrôle à faire (par rapport à la dernière version du FORFAIT) :');

        $details1 = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSuppForfait']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSuppForfait']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSuppForfait']['nbrModif'],
        ];

        $this->addSummaryDetails($pdf, $details1);

        $this->addTitle($pdf, 'VENTE client');
        $pdf->setFont('helvetica', '', 12);
        // ================================================================================================
        //VENTE
        $headerConfig2 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'libelleItv', 'label' => 'Libellé ITV', 'width' => 200, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'nbLigAv', 'label' => 'Nb Lig av', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'nbLigAp', 'label' => 'Nb Lig ap', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotalAv', 'label' => 'Mtt Total av', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttTotalAp', 'label' => 'Mtt Total ap', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'statut', 'label' => 'Statut', 'width' => 40, 'style' => 'font-weight: bold; text-align: center;'],
        ];

        $html2 = $generator->generateTable($headerConfig2, $montantPdf['avantApresVte'], $montantPdf['totalAvantApresVte']);
        $pdf->writeHTML($html2, true, false, true, false, '');
        //==============================================================================================================
        $this->addTitle($pdf, 'Contrôle à faire (par rapport à la dernière version du FORFAIT) :');

        $details2 = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSuppVte']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSuppVte']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSuppVte']['nbrModif'],
            'Montant total modifié' => $this->formatNumber($montantPdf['nombreStatutNouvEtSuppVte']['mttModif']),
        ];

        $this->addSummaryDetails($pdf, $details2);

        //===============================================================================================================
        $this->addTitle($pdf, 'Variation de prix de vente par référence(3 dernières ventes facturées de plus anciennes au plus récentes)');
        $pdf->setFont('helvetica', '', 12);
        $headerConfig3 = [
            ['key' => 'lineType', 'label' => 'Type de ligne', 'width' => 60, 'style' => 'font-weight: bold;'],
            ['key' => 'cst', 'label' => 'CST', 'width' => 60, 'style' => 'font-weight: bold; text-align: center;'],
            ['key' => 'refPieces', 'label' => 'Réf. Pièce', 'width' => 60, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'pu1', 'label' => 'PU 1', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'datePu1', 'label' => 'Date PU 1', 'width' => 60, 'style' => 'font-weight: bold; text-align: center;'],
            ['key' => 'pu2', 'label' => 'PU 2', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'datePu2', 'label' => 'Date PU 2', 'width' => 60, 'style' => 'font-weight: bold; text-align: center;'],
            ['key' => 'pu3', 'label' => 'PU 3', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'datePu3', 'label' => 'Date PU 3', 'width' => 60, 'style' => 'font-weight: bold; text-align: center;'],
        ];
        $totals = [];
        $html3 = $generator->generateTable($headerConfig3, $variationPrixRefPiece, $totals);
        $pdf->writeHTML($html3, true, false, true, false, '');

        //================================================================================================================
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


        $html4 = $generator->generateTable($headerConfig4, $montantPdf['recapVte'], $montantPdf['totalRecapVte']);
        $pdf->writeHTML($html4, true, false, true, false, '');

        //=================================================================================================================
        $pdf->Ln(10, true);
        $headerConfig5 = [
            ['key' => 'colonne1', 'label' => 'MONTANT DEVIS AVANT', 'width' => 105, 'style' => 'font-weight: bold; text-align: center'],
            ['key' => 'colonne2', 'label' => '', 'width' => 65, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'colonne3', 'label' => 'MONTANT DEVIS APRES', 'width' => 105, 'style' => 'font-weight: bold; text-align: center;'],
            ['key' => 'colonne4', 'label' => '', 'width' => 65, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'colonne5', 'label' => 'MARGE', 'width' => 90, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'colonne6', 'label' => 'MARGE', 'width' => 90, 'style' => 'font-weight: bold; text-align: right;'],
        ];

        $montantTotalForfaitVenteAvant = $montantPdf['totalAvantApresForfait']['mttTotalAv'] + $montantPdf['totalAvantApresVte']['mttTotalAv'];
        $montantTotalForfaitVenteApres = $montantPdf['totalAvantApresForfait']['mttTotalAp'] + $montantPdf['totalAvantApresVte']['mttTotalAp'];
        $montantTotalForfaitAvant = $montantPdf['totalAvantApresForfait']['mttTotalAv'];
        $montantTotalForfaitApres = $montantPdf['totalAvantApresForfait']['mttTotalAp'];
        $montantTotalCesAvant = $montantPdf['totalAvantApresCes']['mttTotalAv'];
        $montantTotalCesApres = $montantPdf['totalAvantApresCes']['mttTotalAp'];
        
        $margeAvant = $montantTotalCesAvant === 0.00 ? 0.00 : (($montantTotalForfaitAvant - $montantTotalCesAvant)/$montantTotalCesAvant)*100;
        $margeApres = $montantTotalCesApres === 0.00 ? 0.00 : (($montantTotalForfaitApres - $montantTotalCesApres)/$montantTotalCesApres)*100;

        $footer = [
            'colonne1' => 'TOTAL FORFAIT + VENTE :', 
            'colonne2' =>  number_format((float) $montantTotalForfaitVenteAvant, 2, ',', '.'), 
            'colonne3' => 'TOTAL FORFAIT + VENTE :', 
            'colonne4' =>  number_format((float) $montantTotalForfaitVenteApres, 2, ',', '.'),
            'colonne5' => 'AV : ' .  number_format((float) $margeAvant, 2, ',', '.'). ' %', 
            'colonne6' => 'AP : ' . number_format((float) $margeApres, 2, ',', '.'). '%'
        ];

        $html5 = $generator->generateTable($headerConfig5, [], $footer, true);
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

        $html6 = $generator->generateTable($headerConfig6, $montantPdf['avantApresCes'], $montantPdf['totalAvantApresCes']);
        $pdf->writeHTML($html6, true, false, true, false, '');

        $this->addTitle($pdf, 'Contrôle à faire (par rapport à la dernière version du FORFAIT) :');

        $details3 = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSuppCes']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSuppCes']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSuppCes']['nbrModif'],
            'Montant total modifié' => $this->formatNumber($montantPdf['nombreStatutNouvEtSuppCes']['mttModif']),
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

        $html7 = $generator->generateTable($headerConfig7, $montantPdf['recapCes'], $montantPdf['totalRecapCes']);
        $pdf->writeHTML($html7, true, false, true, false, '');

        // $pdf->SetTextColor(0, 0, 0);
        // $pdf->SetFont('helvetica', '', 10);
        // $pdf->SetXY(118, 2);
        // $pdf->Cell(35, 6, $email, 0, 0, 'L');


        $Dossier = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/dev/';
        $filePath = $Dossier . 'devis_ctrl_' . $devisSoumis->getNumeroDevis() . '_' . $devisSoumis->getNumeroVersion() . '.pdf';
        $pdf->Output($filePath, 'F');
    }
}
