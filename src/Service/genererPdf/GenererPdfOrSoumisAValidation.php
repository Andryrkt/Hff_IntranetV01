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

    function GenererPdfOrSoumisAValidation($ditInsertionOr)
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

        // Date de soumission
        $pdf->Cell(45, 6, 'Date soumission : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 6, $ditInsertionOr->getDateSoumission()->format('d/m/Y'), 1, 1, '', false, '', 0, false, 'T', 'M');

        // Numéro OR
        $pdf->SetXY($startX, $pdf->GetY()+ 2);
        $pdf->Cell(45, 6, 'Numéro OR : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 6, $ditInsertionOr->getNumeroOR(), 1, 1, '', false, '', 0, false, 'T', 'M');

        // Version à valider
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->Cell(45, 6, 'Version à valider : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 6, $ditInsertionOr->getNumeroVersion(), 1, 1, '', false, '', 0, false, 'T', 'M');

        // Fin du bloc
        $pdf->Ln(10, true);

        // ================================================================================================
        $header1 = ['ITV', 'Libellé ITV', 'Nb Lig av','Nb Lig ap', 'Mtt Total av', 'Mtt total ap', 'Statut'];
        $historiqueMateriel = [ [
                'codeagence' => 'Agences', 
                'codeservice' =>'Services', 
                'datedebut' => 'Date',
                'numeroor' => 'numor', 
                'numerointervention' =>'interv', 
                'commentaire' => 'commentaire', 
                'somme' => 'Supp'],
                [
                        'codeagence' => 'Agences', 
                        'codeservice' =>'Services', 
                        'datedebut' => 'Date',
                        'numeroor' => 'numor', 
                        'numerointervention' =>'interv', 
                        'commentaire' => 'commentaire', 
                        'somme' => 'Nouv'],
                        [
                                'codeagence' => 'Agences', 
                                'codeservice' =>'Services', 
                                'datedebut' => 'Date',
                                'numeroor' => 'numor', 
                                'numerointervention' =>'interv', 
                                'commentaire' => 'commentaire', 
                                'somme' => 'Modif']];

            $html = '<table border="1" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px; ">';

            $html .= '<thead>';
            $html .= '<tr style="background-color: #D3D3D3;">';
            foreach ($header1 as $key => $value) {
                if ($key === 0) {
                    $html .= '<th style="width: 40px; font-weight: 900;" >' . $value . '</th>';
                } elseif ($key === 1) {
                    $html .= '<th style="width: 70px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 2) {
                    $html .= '<th style="width: 60px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 3) {
                    $html .= '<th style="width: 60px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 4) {
                    $html .= '<th style="width: 80px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 5) {
                    $html .= '<th style="width: 80px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 6) {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: center;" >' . $value . '</th>';
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
                        $html .= '<td style="width: 70px"  >' . $cell . '</td>';
                    } elseif ($key === 'datedebut') {
                        $html .= '<td style="width: 60px"  >' . $cell . '</td>';
                    } elseif ($key === 'numeroor') {
                        $html .= '<td style="width: 60px"  >' . $cell . '</td>';
                    } elseif ($key === 'numerointervention') {
                        $html .= '<td style="width: 80px"  >' . $cell . '</td>';
                    } elseif ($key === 'commentaire') {
                        $html .= '<td style="width: 80px;"  >' . $cell . '</td>';
                    } elseif ($key === 'somme') {
                        if($cell === 'Supp'){
                                $html .= '<td style="width: 80px; text-align: left; background-color: #FF0000;"  >  ' . $cell . '</td>';
                        } elseif($cell === 'Modif') {
                                $html .= '<td style="width: 80px; text-align: left; background-color: #FFFF00;"  >  ' . $cell . '</td>';
                        } elseif ($cell === 'Nouv') {
                                $html .= '<td style="width: 80px; text-align: left; background-color: #00FF00;"  >  ' . $cell . '</td>';
                        }
                    } 
                    // else {
                    //     $html .= '<td  >' . $cell . '</td>';
                    // }
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '<tr>';
            foreach ($header1 as $key => $value) {
                if ($key === 0) {
                    $html .= '<th style="width: 40px; font-weight: 900;" ></th>';
                } elseif ($key === 1) {
                    $html .= '<th style="width: 70px; font-weight: bold;" > TOTAL</th>';
                } elseif ($key === 2) {
                    $html .= '<th style="width: 60px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 3) {
                    $html .= '<th style="width: 60px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 4) {
                    $html .= '<th style="width: 80px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 5) {
                    $html .= '<th style="width: 80px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 6) {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: center;" ></th>';
                } else {
                    $html .= '<th >' . $value . '</th>';
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
        $pdf->cell(50, 4, '', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //intervention supprimer

        $pdf->Cell(45, 6, ' - Intervention supprimée : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 4, '', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //nombre ligne modifiée
        $pdf->Cell(50, 6, ' - Nombre ligne modifiée ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //montant total modifié
        $pdf->Cell(0, 6, ' - Montant total modifié ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //montant total modifié
        $pdf->Cell(0, 6, ' - Heure saisie inférieur dernière compteur relevé ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

//==========================================================================================================
 //Titre: Récapitulation de l'OR
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, 'Récapitulation de l\'OR ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);       
        $header1 = ['ITV', 'Mtt Total', 'Mtt Pièces','Mtt MO', 'Mtt ST', 'Mtt LUB', 'Mtt Autres'];
        $historiqueMateriel = [ [
                'codeagence' => 'Agences', 
                'codeservice' =>'Services', 
                'datedebut' => 'Date',
                'numeroor' => 'numor', 
                'numerointervention' =>'interv', 
                'commentaire' => 'commentaire', 
                'pos' =>'pos', 
                'somme' => 'Sommes'],
                [
                        'codeagence' => 'Agences', 
                        'codeservice' =>'Services', 
                        'datedebut' => 'Date',
                        'numeroor' => 'numor', 
                        'numerointervention' =>'interv', 
                        'commentaire' => 'commentaire', 
                        'pos' =>'pos', 
                        'somme' => 'Sommes'],
                        [
                                'codeagence' => 'Agences', 
                                'codeservice' =>'Services', 
                                'datedebut' => 'Date',
                                'numeroor' => 'numor', 
                                'numerointervention' =>'interv', 
                                'commentaire' => 'commentaire', 
                                'pos' =>'pos', 
                                'somme' => 'Sommes']];

            $html = '<table border="1" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px; ">';

            $html .= '<thead>';
            $html .= '<tr style="background-color: #D3D3D3;">';
            foreach ($header1 as $key => $value) {
                if ($key === 0) {
                    $html .= '<th style="width: 40px; font-weight: 900;" >' . $value . '</th>';
                } elseif ($key === 1) {
                    $html .= '<th style="width: 70px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 2) {
                    $html .= '<th style="width: 60px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 3) {
                    $html .= '<th style="width: 60px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 4) {
                    $html .= '<th style="width: 80px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 5) {
                    $html .= '<th style="width: 80px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 6) {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: center;" >' . $value . '</th>';
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
                        $html .= '<td style="width: 70px"  >' . $cell . '</td>';
                    } elseif ($key === 'datedebut') {
                        $html .= '<td style="width: 60px"  >' . $cell . '</td>';
                    } elseif ($key === 'numeroor') {
                        $html .= '<td style="width: 60px"  >' . $cell . '</td>';
                    } elseif ($key === 'numerointervention') {
                        $html .= '<td style="width: 80px"  >' . $cell . '</td>';
                    } elseif ($key === 'commentaire') {
                        $html .= '<td style="width: 80px;"  >' . $cell . '</td>';
                    } elseif ($key === 'somme') {
                        $html .= '<td style="width: 80px;"  >' . $cell . '</td>';
                     } 
                    // else {
                    //     $html .= '<td  >' . $cell . '</td>';
                    // }
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '<tr>';
            foreach ($header1 as $key => $value) {
                if ($key === 0) {
                    $html .= '<th style="width: 40px; font-weight: 900;" >TOTAL</th>';
                } elseif ($key === 1) {
                    $html .= '<th style="width: 70px; font-weight: bold;" > ' . $value . '</th>';
                } elseif ($key === 2) {
                    $html .= '<th style="width: 60px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 3) {
                    $html .= '<th style="width: 60px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 4) {
                    $html .= '<th style="width: 80px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 5) {
                    $html .= '<th style="width: 80px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 6) {
                    $html .= '<th style="width: 80px; font-weight: bold; text-align: center;" >' . $value . '</th>';
                } else {
                    $html .= '<th >' . $value . '</th>';
                }
            }
            $html .= '</tr>';
            $html .= '</tfoot>';
            $html .= '</table>';

            $pdf->writeHTML($html, true, false, true, false, '');




        $Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/';
        $pdf->Output($Dossier.'oRValidation_' .$ditInsertionOr->getNumeroVersion(). '.pdf', 'F');
    }

}