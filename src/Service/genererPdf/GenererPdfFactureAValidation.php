<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\FormatageTrait;
use TCPDF;

class GenererPdfFactureAValidation extends GeneratePdf
{

    use FormatageTrait;
    
    /**
     * generer pdf facture à validation
     */
    function GenererPdfFactureSoumisAValidation($ditfacture, $numDevis, $montantPdf, $etatOr, $email, $interneExterne)
    {
        $pdf = new TCPDF();


        $pdf->AddPage();


        $pdf->setFont('helvetica', 'B', 17);
        $pdf->Cell(0, 6, 'Validation Facture', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

       // Début du bloc
        $pdf->setFont('helvetica', '', 10);
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        $pdf->setFont('helvetica', 'B', 10);
        // Date de soumission
        $pdf->Cell(45, 6, 'Date soumission : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditfacture->getDateSoumission()->format('d/m/Y'), 0, 1, '', false, '', 0, false, 'T', 'M');
        

        // Numéro Facture
        $pdf->SetXY($startX, $pdf->GetY()+ 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Numéro Facture : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditfacture->getNumeroFact(), 0, 1, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(30, 6, 'Numéro Devis :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(0, 6,$numDevis[0]['seor_numdev'] === '' ? 0 : $numDevis[0]['seor_numdev'] , 0, 0, '', false, '', 0, false, 'T', 'M');

        // Numéro OR
        $pdf->SetXY($startX, $pdf->GetY()+ 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Numéro OR : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditfacture->getNumeroOR(), 0, 1, '', false, '', 0, false, 'T', 'M');
        
        //partiellement facturé / Complètement facturé
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Etat OR :', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $etatOr, 0, 1, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        // Numéro soumission
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Numéro soumission : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditfacture->getNumeroSoumission(), 0, 1, '', false, '', 0, false, 'T', 'M');

        // Fin du bloc
        $pdf->Ln(10, true);

        // ================================================================================================
        $header1 = ['ITV', 'Libellé ITV', 'statut ITV','Mtt ITV', 'Mtt FAC', 'AG Serv Deb DIT', 'AG Serv Deb FAC', 'Contrôle à faire'];

            $html = '<table border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px; ">';

            $html .= '<thead>';
            $html .= '<tr style="background-color: #D3D3D3;">';
            foreach ($header1 as $key => $value) {
                if ($key === 0) {
                    $html .= '<th style="width: 20px; font-weight: 900;" >' . $value . '</th>';
                } elseif ($key === 1) {
                    $html .= '<th style="width: 190px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 2) {
                    $html .= '<th style="width: 40px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 3) {
                    $html .= '<th style="width: 55px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 4) {
                    $html .= '<th style="width: 55px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 5) {
                    $html .= '<th style="width: 40px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 6) {
                    $html .= '<th style="width: 40px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } elseif ($key === 7) {
                    $html .= '<th style="width: 110px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } else {
                    $html .= '<th >' . $value . '</th>';
                }
            }
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            // Ajouter les lignes du tableau
            foreach ($montantPdf['infoItvFac'] as $row) {
                $html .= '<tr>';
                foreach ($row as $key => $cell) {
                    
                    if ($key === 'itv') {
                        $html .= '<td style="width: 20px"  >' . $cell . '</td>';
                    } elseif ($key === 'libelleItv') {
                        $html .= '<td style="width: 190px; text-align: left;"  >' . $cell . '</td>';
                    } elseif ($key === 'statutItv') {
                        $html .= '<td style="width: 40px; "  >' . $cell . '</td>';
                    } elseif ($key === 'mttItv') {
                        $html .= '<td style="width: 55px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } elseif ($key === 'mttFac') {
                        $html .= '<td style="width: 55px; text-align: right;"  >' . $this->formatNumberDecimal($cell) . '</td>';
                    } elseif ($key === 'AgServDebDit') {
                        $html .= '<td style="width: 40px; "  >' . $cell . '</td>';
                    } elseif ($key === 'AgServDebFac') {
                        $html .= '<td style="width: 40px;"  >' . $cell . '</td>';
                    } elseif ($key === 'statut') {
                        if($cell === 'OK'){
                            $html .= '<td style="width: 110px; text-align: left; background-color: #008000;"  >' . $cell . '</td>';
                        } elseif($cell === 'DIT migrée') {
                            $html .= '<td style="width: 110px; text-align: left;"  >' . $cell . '</td>';
                        }  else {
                            $html .= '<td style="width: 110px; text-align: left; background-color: #FF0000;"  >' . $cell . '</td>';
                        }
                    }
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '<tr style="background-color: #D3D3D3;">';
                foreach ($montantPdf['totalItvFac'] as $key => $value) {
                    if ($key === 'premierLigne') {
                        $html .= '<th style="width: 20px; font-weight: 900;" ></th>';
                    } elseif ($key === 'total') {
                        $html .= '<th style="width: 190px; font-weight: bold;" > TOTAL</th>';
                    } elseif ($key === 'statur') {
                        $html .= '<th style="width: 40px; font-weight: bold; " ></th>';
                    } elseif ($key === 'totalMttItv') {
                        $html .= '<th style="width: 55px; font-weight: bold; " >' . $this->formatNumberDecimal($value) . '</th>';
                    } elseif ($key === 'totalMttFac') {
                        $html .= '<th style="width: 55px; font-weight: bold; text-align: right;" >' . $this->formatNumberDecimal($value) . '</th>';
                    } elseif ($key === 'AgServDebDit') {
                        $html .= '<th style="width: 40px; font-weight: bold; text-align: right;" ></th>';
                    } elseif ($key === 'AgServDebFac') {
                        $html .= '<th style="width: 40px; font-weight: bold;" ></th>';
                    } elseif ($key === 'controleAFaire')  {
                        $html .= '<th style="width: 110px; font-weight: bold;" ></th>';
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
        $pdf->Cell(72, 6, ' - Nombre intervention non validée - facturée : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $montantPdf['controleAFaire']['nbrNonValideFacture'], 0, 0, '', false,'' , 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //intervention supprimer

        $pdf->Cell(140, 6, ' - Nombre intervention dont service débiteur sur la DIT <> service débiteur sur la facture : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $montantPdf['controleAFaire']['nbrServDebDitDiffServDebFac'], 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //nombre ligne modifiée
        $pdf->Cell(100, 6, ' - Nombre intervention avec montant validé <> montant facturé :', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $montantPdf['controleAFaire']['nbrMttValideDiffMttFac'], 0, 0, '', false, '', 0, false, 'T', 'M');
        
        $pdf->Ln(10, true);

//==========================================================================================================
 //Titre: Récapitulation de l'OR
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, 'Récapitulatif de la facture', 0, 0, 'L', false, '', 0, false, 'T', 'M');
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


        $Dossier = $_ENV['BASE_PATH_FICHIER'].'/vfac/';
        if($interneExterne == 'INTERNE') {
            $filePath = $Dossier . 'factureValidation_' . $ditfacture->getNumeroFact() . '_' . $ditfacture->getNumeroSoumission() . '.pdf';
        } else {
            $filePath = $Dossier . 'validation_facture_client_' . $ditfacture->getNumeroFact() . '_' . $ditfacture->getNumeroSoumission() . '.pdf';
        }

        $pdf->Output($filePath, 'F');
        
        return $filePath;
    }

}