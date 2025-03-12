<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\FormatageTrait;
use TCPDF;

class GenererPdfOrSoumisAValidation extends GeneratePdf
{

    use FormatageTrait;
    
    /**
     * generer pdf changement de Casier
     */

    function GenererPdfOrSoumisAValidation($ditInsertionOr, $montantPdf, $quelqueaffichage, $email, string $suffix)
    {
        $pdf = new TCPDF();


        $pdf->AddPage();


        $pdf->setFont('helvetica', 'B', 17);
        $pdf->Cell(0, 6, 'Validation OR', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

       // Début du bloc
        $pdf->setFont('helvetica', '', 10);
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        $pdf->setFont('helvetica', 'B', 10);
        // Date de soumission
        $pdf->Cell(45, 6, 'Date soumission : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditInsertionOr->getDateSoumission()->format('d/m/Y'), 0, 1, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(20, 6, 'N° Devis :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(0, 6,$quelqueaffichage['numDevis'][0]['seor_numdev'] === '' ? 0 : $quelqueaffichage['numDevis'][0]['seor_numdev'] , 0, 0, '', false, '', 0, false, 'T', 'M');

        // Numéro OR
        $pdf->SetXY($startX, $pdf->GetY()+ 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Numéro OR : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditInsertionOr->getNumeroOR(), 0, 1, '', false, '', 0, false, 'T', 'M');

        // Version à valider
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Version à valider : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditInsertionOr->getNumeroVersion(), 0, 1, '', false, '', 0, false, 'T', 'M');

        // sortie magasin
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Sortie magasin : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $quelqueaffichage['sortieMagasin'], 0, 1, '', false, '', 0, false, 'T', 'M');

        // Achat locaux
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Achat locaux : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $quelqueaffichage['achatLocaux'], 0, 1, '', false, '', 0, false, 'T', 'M');
        
        // Fin du bloc
        $pdf->Ln(10, true);

        // ================================================================================================
        $header1 = ['ITV', 'Libellé ITV', 'Date pla','Nb Lig av','Nb Lig ap', 'Mtt Total av', 'Mtt total ap', 'Statut'];

            $html = '<table border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px; ">';

            $html .= '<thead>';
            $html .= '<tr style="background-color: #D3D3D3;">';
            foreach ($header1 as $key => $value) {
                if ($key === 0) {
                    $html .= '<th style="width: 40px; font-weight: 900;" >' . $value . '</th>';
                } elseif ($key === 1) {
                    $html .= '<th style="width: 150px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 2) {
                    $html .= '<th style="width: 50px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 3) {
                    $html .= '<th style="width: 50px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 4) {
                    $html .= '<th style="width: 50px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 5) {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 6) {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 7) {
                    $html .= '<th style="width: 40px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } else {
                    $html .= '<th >' . $value . '</th>';
                }
            }
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            // Ajouter les lignes du tableau
            foreach ($montantPdf['avantApres'] as $row) {
                $html .= '<tr>';
                foreach ($row as $key => $cell) {
                    if ($key === 'itv') {
                        $html .= '<td style="width: 40px"  >' . $cell . '</td>';
                    } elseif ($key === 'libelleItv') {
                        $html .= '<td style="width: 150px; text-align: left;"  >' . $cell . '</td>';
                    } elseif ($key === 'datePlanning') {
                        $html .= '<td style="width: 50px; text-align: left;"  >' . $this->formatDateTime($cell). '</td>';
                    } elseif ($key === 'nbLigAv') {
                        $html .= '<td style="width: 50px; "  >' . $cell . '</td>';
                    } elseif ($key === 'nbLigAp') {
                        $html .= '<td style="width: 50px;"  >' . $cell . '</td>';
                    } elseif ($key === 'mttTotalAv') {
                        $html .= '<td style="width: 80px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } elseif ($key === 'mttTotalAp') {
                        $html .= '<td style="width: 80px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } elseif ($key === 'statut') {
                        if($cell === 'Supp'){
                                $html .= '<td style="width: 40px; text-align: left; background-color: #FF0000;"  >  ' . $cell . '</td>';
                        } elseif($cell === 'Modif') {
                                $html .= '<td style="width: 40px; text-align: left; background-color: #FFFF00;"  >  ' . $cell . '</td>';
                        } elseif ($cell === 'Nouv') {
                                $html .= '<td style="width: 40px; text-align: left; background-color: #00FF00;"  >  ' . $cell . '</td>';
                        } else {
                            $html .= '<td style="width: 40px; text-align: left; "  >  ' . $cell . '</td>';
                        }
                    } 
                    
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '<tr style="background-color: #D3D3D3;">';
            foreach ($montantPdf['totalAvantApres'] as $key => $value) {
                if ($key === 'premierLigne') {
                    $html .= '<th style="width: 40px; font-weight: 900;" ></th>';
                } elseif ($key === 'deuxiemeLigne') {
                    $html .= '<th style="width: 150px; font-weight: 900;" ></th>';
                } elseif ($key === 'total') {
                    $html .= '<th style="width: 50px; font-weight: bold;" > TOTAL</th>';
                } elseif ($key === 'totalNbLigAv') {
                    $html .= '<th style="width: 50px; font-weight: bold; " >' . $value . '</th>';
                } elseif ($key === 'totalNbLigAp') {
                    $html .= '<th style="width: 50px; font-weight: bold; " >' . $value . '</th>';
                } elseif ($key === 'totalMttTotalAv') {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: right;" >' . $this->formatNumberDecimal($value) . '</th>';
                } elseif ($key === 'totalMttTotalAp') {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: right;" >' . $this->formatNumberDecimal($value) . '</th>';
                } elseif ($key === 'dernierLigne') {
                    $html .= '<th style="width: 40px; font-weight: bold; text-align: center;" ></th>';
                }
            }
            $html .= '</tr>';
            $html .= '</tfoot>';
            $html .= '</table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            //$pdf->Ln(10, true);
//===========================================================================================
            //Titre: Controle à faire
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, 'Contrôle à faire (par rapport dernière version) : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $pdf->setFont('helvetica', '', 10);
        //Nouvelle intervention
        $pdf->Cell(45, 6, ' - Nouvelle intervention : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 5, $montantPdf['nombreStatutNouvEtSupp']['nbrNouv'], 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //intervention supprimer

        $pdf->Cell(45, 6, ' - Intervention supprimée : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 5, $montantPdf['nombreStatutNouvEtSupp']['nbrSupp'], 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //nombre ligne modifiée
        $pdf->Cell(45, 6, ' - Nombre ligne modifiée :', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 5, $montantPdf['nombreStatutNouvEtSupp']['nbrModif'], 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //montant total modifié
        $pdf->Cell(45, 6, ' - Montant total modifié :', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 5, $this->formatNumber($montantPdf['nombreStatutNouvEtSupp']['mttModif']), 0, 0, '', false, '', 0, false, 'T', 'M');

        $pdf->Ln(10, true);

//==========================================================================================================
 //Titre: Récapitulation de l'OR
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, 'Récapitulation de l\'OR ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true); 
        
        $pdf->setFont('helvetica', '', 12);
        $header1 = ['ITV', 'Mtt Total', 'Mtt Pièces','Mtt MO', 'Mtt ST', 'Mtt LUB', 'Mtt Autres'];
        

            $html = '<table border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px; ">';

            $html .= '<thead>';
            $html .= '<tr style="background-color: #D3D3D3;">';
            foreach ($header1 as $key => $value) {
                if ($key === 0) {
                    $html .= '<th style="width: 40px; font-weight: 900;" >' . $value . '</th>';
                } elseif ($key === 1) {
                    $html .= '<th style="width: 70px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 2) {
                    $html .= '<th style="width: 60px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 3) {
                    $html .= '<th style="width: 60px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 4) {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 5) {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 6) {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                }
            }
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            // Ajouter les lignes du tableau
            foreach ($montantPdf['recapOr'] as $row) {
            
                $html .= '<tr>';
                foreach ($row as $key => $cell) {
                    if ($key === 'itv') {
                        $html .= '<td style="width: 40px"  >' . $cell . '</td>';
                    } elseif ($key === 'mttTotal') {
                        $html .= '<td style="width: 70px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } elseif ($key === 'mttPieces') {
                        $html .= '<td style="width: 60px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } elseif ($key === 'mttMo') {
                        $html .= '<td style="width: 60px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } elseif ($key === 'mttSt') {
                        $html .= '<td style="width: 80px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } elseif ($key === 'mttLub') {
                        $html .= '<td style="width: 80px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } elseif ($key === 'mttAutres') {
                        $html .= '<td style="width: 80px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } 
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '<tr style="background-color: #D3D3D3;">';
            foreach ($montantPdf['totalRecapOr']as $key => $value) {
            
                if ($key === 'total') {
                    $html .= '<th style="width: 40px; font-weight: 900;" >TOTAL</th>';
                } elseif ($key === 'montant_itv') {
                    $html .= '<th style="width: 70px; font-weight: bold; text-align: right;" > ' . $this->formatNumberDecimal($value) . '</th>';
                } elseif ($key === 'montant_piece') {
                    $html .= '<th style="width: 60px; font-weight: bold; text-align: right;" >' . $this->formatNumberDecimal($value) . '</th>';
                } elseif ($key === 'montant_mo') {
                    $html .= '<th style="width: 60px; font-weight: bold; text-align: right;" >' . $this->formatNumberDecimal($value) . '</th>';
                } elseif ($key === 'montant_achats_locaux') {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: right;" >' . $this->formatNumberDecimal($value) . '</th>';
                } elseif ($key === 'montant_lubrifiants') {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: right;" >' . $this->formatNumberDecimal($value) . '</th>';
                } elseif ($key === 'montant_frais_divers') {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: right;" >' . $this->formatNumberDecimal($value). '</th>';
                } 
            }
            $html .= '</tr>';
            $html .= '</tfoot>';
            $html .= '</table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetXY(118, 2);
            $pdf->Cell(35, 6, $email, 0, 0, 'L');


        $Dossier = $_ENV['BASE_PATH_FICHIER'].'/vor/';
        $nomFichier = 'oRValidation_' .$ditInsertionOr->getNumeroOR().'-'.$ditInsertionOr->getNumeroVersion().'#'.$suffix. '.pdf';
        $pdf->Output($Dossier.$nomFichier, 'F');
    }

}