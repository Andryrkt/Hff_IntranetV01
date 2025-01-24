<?php

namespace App\Service\genererPdf;

use TCPDF;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;

class GenererPdfDit extends GeneratePdf
{
    use FormatageTrait;

    /**
     * GENERER PDF DEMANDE D'INTERVENTION
     *
     * @return void
     */
    function genererPdfDit(DemandeIntervention $dit, array $historiqueMateriel)
    {
        $pdf = new TCPDF();

        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath =  'C:/wamp64/www/Hffintranet/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        //$pdf->Cell(45, 12, 'LOGO', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell(110, 6, 'DEMANDE D\'INTERVENTION', 0, 0, 'C', false, '', 0, false, 'T', 'M');


        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $dit->getNumeroDemandeIntervention(), 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        $pdf->setFont('helvetica', 'B', 12);
        $pdf->setAbsX(55);
        if ($dit->getTypeDocument() !== null) {
            $descriptionTypeDocument = $dit->getTypeDocument()->getDescription();
        } else {
            $descriptionTypeDocument = ''; // Ou toute autre valeur par défaut appropriée
        }
        $pdf->cell(110, 6, $descriptionTypeDocument, 0, 0, 'C', false, '', 0, false, 'T', 'M');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $dit->getDateDemande()->format('d/m/Y'), 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        //========================================================================================
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Objet :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getObjetDemande(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $pdf->cell(25, 6, 'Détails :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->MultiCell(0, 10, $dit->getDetailDemande() . "\n", 1, 'J', 0, 2, '', '', true);
        //$pdf->cell(165, 10, , 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        $pdf->MultiCell(25, 6, "Catégorie :", 0, 'L', false, 0);
        if ($dit->getCategorieDemande() !== null) {
            $libelleCategorie = $dit->getCategorieDemande()->getLibelleCategorieAteApp();
        } else {
            $libelleCategorie = ''; // Ou toute autre valeur par défaut appropriée
        }
        $pdf->cell(55, 6, $libelleCategorie, 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(95);
        $pdf->MultiCell(40, 6, " avis recouvrement :", 0, 'L', false, 0);
        $pdf->cell(15, 6, $dit->getAvisRecouvrement(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(155);
        $pdf->cell(30, 6, 'Devis demandé :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getDemandeDevis(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);
//=========================================================================================================
        /** INTERVENTION */
        // $pdf->setFont('helvetica', 'B', 11);
        // $pdf->SetTextColor(14, 65, 148);
        // // $pdf->setAbsY(63);
        // $pdf->Cell(30, 6, 'Intervention', 1, 0, '', false, '', 0, false, 'T', 'M');
        // $pdf->SetFillColor(14, 65, 148);
        // $pdf->setAbsXY(40, 65);
        // $pdf->Rect($pdf->GetX(), $pdf->GetY(), 160, 3, 'F');
        // $pdf->Ln(7, true);

        $this->renderTextWithLine($pdf, 'Intervention');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Date prévue :', 0, 0, '', false, '', 0, false, 'T', 'M');
        if ($dit->getDatePrevueTravaux() !== null && !empty($dit->getDatePrevueTravaux())) {
            $pdf->cell(50, 6, $dit->getDatePrevueTravaux()->format('d/m/Y'), 1, 0, '', false, '', 0, false, 'T', 'M');
        } else {
            $pdf->cell(50, 6, $dit->getDatePrevueTravaux(), 1, 0, '', false, '', 0, false, 'T', 'M');
        }
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Urgence :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getIdNiveauUrgence()->getDescription(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);
//===================================================================================================
        /**AGENCE-SERVICE */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Agence - Service', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(50, 82);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 150, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Emetteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 6, $dit->getAgenceServiceEmetteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Débiteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getAgenceServiceDebiteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);
//====================================================================================================
        /**REPARATION */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Réparation', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(35, 104);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 165, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Type :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, $dit->getInternetExterne(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->cell(23, 6, 'Réparation :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(35, 6, $dit->getTypeReparation(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(25, 6, 'Réalisé par :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getReparationRealise(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        /**CLIENT */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, ' Client ', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(30, 140);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 170, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->MultiCell(12, 6, "Nom :", 0, 'L', false, 0);
        $nomClient = strlen($dit->getNomClient()) <= 30 ? $dit->getNomClient() : substr($dit->getNomClient(), 0, 30);
        $pdf->cell(70, 6, $nomClient, 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(93);
        $pdf->MultiCell(17, 6, "N° tel :", 0, 'R', false, 0);
        $pdf->cell(22, 6, $dit->getNumeroTel(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(132);
        $pdf->cell(15, 6, ' Email :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $mailClient = strlen($dit->getMailClient()) <= 26 ? $dit->getMailClient() : substr($dit->getMailClient(), 0, 26);
        $pdf->cell(0, 6, $mailClient, 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


        /** CARACTERISTIQUE MATERIEL */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(50, 6, 'Caractéristiques du matériel', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(70, 161);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 130, 3, 'F');
        $pdf->Ln(10, true);


        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);


        $pdf->cell(25, 6, 'Désignation :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(70, 6, $dit->getDesignation(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(148);
        $pdf->cell(20, 6, 'N° Série :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getNumSerie(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


        $pdf->cell(25, 6, 'N° Parc :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, $dit->getNumParc(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->cell(23, 6, 'Modèle :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(35, 6, $dit->getModele(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(30, 6, 'Constructeur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getConstructeur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $pdf->cell(25, 6, 'Casier :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(40, 6, $dit->getCasier(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(80);
        $pdf->cell(23, 6, 'Id Matériel :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(20, 6, $dit->getIdMateriel(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(33, 6, 'livraison partielle :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getLivraisonPartiel(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


        /** ETAT MACHINE */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Etat machine', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(40, 203);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 160, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->MultiCell(25, 6, "Heures :", 0, 'L', false, 0);
        $pdf->cell(30, 6, $dit->getHeure(), 1, 0, '', false, '', 0, false, 'T', 'M');
        // $pdf->setAbsX(70);
        // $pdf->MultiCell(25, 6, "OR :", 0, 'L', false, 0);
        // $pdf->cell(35, 6, '', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(135);
        $pdf->cell(25, 6, 'Kilométrage :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getKm(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        /** BILANT FINANCIERE */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);

        $pdf->Cell(40, 6, 'Valeur (MGA)', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(41, 225);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 160, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);



        $pdf->MultiCell(37, 6, "Cout d'Acquisition :", 0, 'L', false, 0);
        $pdf->cell(30, 6, $this->formatNumber($dit->getCoutAcquisition()), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->MultiCell(40, 6, "Amort :", 0, 'R', false, 0);
        $pdf->cell(30, 6, $this->formatNumber($dit->getAmortissement()), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(155);
        $pdf->cell(15, 6, 'Vnc :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $this->formatNumber($dit->getValeurNetComptable()), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(15, true);

        $pdf->MultiCell(37, 6, "Charge d'entretien :", 0, 'L', false, 0);
        $pdf->cell(30, 6, $this->formatNumber($dit->getChargeEntretient()), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->MultiCell(40, 6, "Charge Locative :", 0, 'R', false, 0);
        $pdf->cell(30, 6, $this->formatNumber($dit->getChargeLocative()), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(155);
        $pdf->cell(15, 6, 'CA :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $this->formatNumber($dit->getChiffreAffaire()), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(15, true);

        $pdf->MultiCell(45, 6, "Résultat d'exploitation : ", 0, 'L', false, 0);
        $pdf->cell(30, 6, $this->formatNumber($dit->getResultatExploitation()), 1, 0, '', false, '', 0, false, 'T', 'M');



        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetXY(110, 2);
        $pdf->Cell(35, 6, "email : " . $dit->getMailDemandeur(), 0, 0, 'L');


        /**DEUXIEME PAGE */
        $this->affichageHistoriqueMateriel($pdf, $historiqueMateriel);


        //$pdf->Output('exemple.pdf', 'I');
        // Obtention du chemin absolu du répertoire de travail
        //$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/dit/');
        $documentRoot = 'C:/wamp64/www/Upload/dit'; //faut pas déplacer ou utiliser une variable global sinon ça marche pas avec les comands

        $fileName = $dit->getNumeroDemandeIntervention() . '_' . str_replace("-", "", $dit->getAgenceServiceEmetteur());
        $filePath = $documentRoot . '/' . $fileName . '.pdf';

        // Vérifiez si le répertoire existe et a les bonnes permissions
        if (!is_dir($documentRoot) || !is_writable($documentRoot)) {
            echo "Le répertoire $documentRoot n'existe pas ou n'est pas accessible en écriture.";
            exit;
        }

        $pdf->Output($filePath, 'I');

        // Vérification de la création du fichier
        if (file_exists($filePath)) {
            echo "\nLe fichier a été généré avec succès : $filePath";
        } else {
            echo "\nErreur lors de la génération du fichier PDF.";
        }
    }


    function renderTextWithLine($pdf, $text, $lineWidth = 190, $lineOffset = 3, $font = 'helvetica', $fontStyle = 'B', $fontSize = 12, $textColor = [14, 65, 148], $lineColor = [14, 65, 148], $lineHeight = 3) {
        // Set font and text color
        $pdf->setFont($font, $fontStyle, $fontSize);
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    
        // Calculate text width
        $textWidth = $pdf->GetStringWidth($text);
    
        // Add the text
        $pdf->Cell($textWidth, 6, $text, 0, 0, 'L');
    
        // Set fill color for the line
        $pdf->SetFillColor($lineColor[0], $lineColor[1], $lineColor[2]);
    
        // Calculate the position for the line (next to the text)
        $lineStartX = $pdf->GetX() + $lineOffset; // Add a small offset
        $lineStartY = $pdf->GetY() + 2; // Adjust for alignment
    
        // Draw the line
        $pdf->Rect($lineStartX, $lineStartY, $lineWidth - $textWidth - $lineOffset, $lineHeight, 'F');
    
        // Move to the next line
        $pdf->Ln(10);
    }
    
    


    private function affichageHistoriqueMateriel($pdf, $historiqueMateriel)
    {
        $pdf->AddPage();

        $header1 = ['Agences', 'Services', 'Date', 'numor', 'interv', 'commentaire', 'pos', 'Sommes'];

        // Commencer le tableau HTML
        $html = '<h2 style="text-align:center">HISTORIQUE DE REPARATION</h2>';

        $html .= '<table border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px; ">';

        $html .= '<thead>';
        $html .= '<tr>';
        foreach ($header1 as $key => $value) {
            if ($key === 0) {
                $html .= '<th style="width: 40px; font-weight: 900;" >' . $value . '</th>';
            } elseif ($key === 1) {
                $html .= '<th style="width: 40px; font-weight: bold;" >' . $value . '</th>';
            } elseif ($key === 2) {
                $html .= '<th style="width: 50px; font-weight: bold;" >' . $value . '</th>';
            } elseif ($key === 3) {
                $html .= '<th style="width: 50px; font-weight: bold;" >' . $value . '</th>';
            } elseif ($key === 4) {
                $html .= '<th style="width: 30px; font-weight: bold;" >' . $value . '</th>';
            } elseif ($key === 5) {
                $html .= '<th style="width: 250px; font-weight: bold;" >' . $value . '</th>';
            } elseif ($key === 6) {
                $html .= '<th style="width: 30px; font-weight: bold; text-align: center;" >' . $value . '</th>';
            } elseif ($key === 7) {
                $html .= '<th style="width: 50px; font-weight: bold;" >' . $value . '</th>';
            } else {
                $html .= '<th >' . $value . '</th>';
            }
        }
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        // Ajouter les lignes du tableau
        foreach ($historiqueMateriel as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $cell) {

                if ($key === 'codeagence') {
                    $html .= '<td style="width: 40px"  >' . $cell . '</td>';
                } elseif ($key === 'codeservice') {
                    $html .= '<td style="width: 40px"  >' . $cell . '</td>';
                } elseif ($key === 'datedebut') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'numeroor') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'numerointervention') {
                    $html .= '<td style="width: 30px"  >' . $cell . '</td>';
                } elseif ($key === 'commentaire') {
                    $html .= '<td style="width: 250px; text-align: left;"  >' . $cell . '</td>';
                } elseif ($key === 'somme') {
                    $html .= '<td style="width: 50px; text-align: right;"  >' . $cell . '</td>';
                } elseif ($key === 'pos') {
                    $html .= '<td style="width: 30px; text-align: right; text-align: center;"  >' . $cell . '</td>';
                }
                // else {
                //     $html .= '<td  >' . $cell . '</td>';
                // }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';


        $pdf->writeHTML($html, true, false, true, false, '');
    }
}
