<?php

namespace App\Service\genererPdf;

use TCPDF;
use App\Service\GlobalVariablesService;
use App\Controller\Traits\FormatageTrait;

class GenererPdfBadm extends GeneratePdf
{
    use FormatageTrait;

    /**
     * Generer pdf badm 
     */
    function genererPdfBadm(array $tab, array $orDb = [], array $or2 = [])
    {

        $pdf = new TCPDF();


        $pdf->AddPage();


        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath = $_ENV['BASE_PATH_LONG'] . '/Views/assets/henrifraise.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        //$pdf->Cell(45, 12, 'LOGO', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell(110, 6, 'BORDEREAU DE MOUVEMENT DE MATERIEL', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);


        $pdf->Cell(35, 6, $tab['Num_BDM'], 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        $pdf->setFont('helvetica', 'B', 12);
        $pdf->setAbsX(55);
        if ($tab['typeMouvement'] === 'CHANGEMENT DE CASIER') {
            $pdf->SetFillColor(155, 155, 155);
            $pdf->cell(110, 6, $tab['typeMouvement'], 0, 0, 'C', true, '', 0, false, 'T', 'M');
        } elseif ($tab['typeMouvement'] === 'MISE AU REBUT') {
            $pdf->SetFillColor(255, 69, 0);
            $pdf->cell(110, 6, $tab['typeMouvement'], 0, 0, 'C', true, '', 0, false, 'T', 'M');
        } elseif ($tab['typeMouvement'] === 'CESSION D\'ACTIF') {
            $pdf->SetFillColor(240, 0, 32);
            $pdf->cell(110, 6, $tab['typeMouvement'], 0, 0, 'C', true, '', 0, false, 'T', 'M');
        } elseif ($tab['typeMouvement'] === 'CHANGEMENT AGENCE/SERVICE') {
            $pdf->SetFillColor(0, 128, 255);
            $pdf->cell(110, 6, $tab['typeMouvement'], 0, 0, 'C', true, '', 0, false, 'T', 'M');
        } elseif ($tab['typeMouvement'] === 'ENTREE EN PARC') {
            $pdf->SetFillColor(0, 86, 27);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->cell(110, 6, $tab['typeMouvement'], 0, 0, 'C', true, '', 0, false, 'T', 'M');
        }
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $tab['Date_Demande'], 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(50, 6, 'Caractéristiques du matériel', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(70, 28);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 130, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);


        $pdf->cell(25, 6, 'Désignation :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(70, 6, $tab['Designation'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(150);
        $pdf->cell(12, 6, 'N° ID :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Num_ID'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $pdf->cell(25, 6, 'N° Série :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(70, 6, $tab['Num_Serie'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Groupe :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Groupe'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


        $pdf->cell(25, 6, 'N° Parc :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, $tab['Num_Parc'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->cell(23, 6, 'Affectation:', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(35, 6, $tab['Affectation'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(30, 6, 'Constructeur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Constructeur'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $pdf->cell(25, 6, 'Date d’achat :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, $tab['Date_Achat'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->MultiCell(23, 6, "Année :", 0, 'L', false, 0);
        $pdf->cell(35, 6, $tab['Annee_Model'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(140);
        $pdf->cell(20, 6, 'Modèle :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Modele'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Etat machine', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(40, 80);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 160, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->MultiCell(25, 6, "Heures :", 0, 'L', false, 0);
        $pdf->cell(30, 6, $tab['Heures_Machine'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->MultiCell(25, 6, "OR :", 0, 'L', false, 0);
        $pdf->cell(35, 6, $tab['OR'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(135);
        $pdf->cell(25, 6, 'Kilométrage :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Kilometrage'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);



        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Service émetteur', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(50, 102);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 150, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(35, 6, 'Agence - Service :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(63, 6, $tab['Agence_Service_Emetteur'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Casier :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Casier_Emetteur'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);



        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Service destinataire', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(54, 124);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 147, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(35, 6, 'Agence - Service :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(63, 6, $tab['Agence_Service_Destinataire'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Casier :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Casier_Destinataire'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


        $pdf->cell(35, 6, 'Motif :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Motif_Arret_Materiel'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);



        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Entrée en parc', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(43, 156);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 158, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(35, 6, 'Etat à l’achat:', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(63, 6, $tab['Etat_Achat'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(110);
        $pdf->cell(50, 6, 'Date de mise en location :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Date_Mise_Location'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);



        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);

        $pdf->Cell(40, 6, 'Valeur (MGA)', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(41, 178);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 160, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);


        $pdf->MultiCell(35, 6, "Coût d’acquisition:", 0, 'L', false, 0);
        $pdf->cell(63, 6, $this->formatNumber($tab['Cout_Acquisition']), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(110);
        $pdf->cell(20, 6, 'Amort :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $this->formatNumber($tab['Amort']), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(0, 0, 0);
        $pdf->setAbsXY(130, 196);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 70, 1, 'F');
        $pdf->Ln(3, true);
        $pdf->setAbsX(110);
        $pdf->cell(20, 6, 'VNC :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $this->formatNumber($tab['VNC']), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Cession d’actif', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(44, 210);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 158, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->MultiCell(35, 6, "Nom client :", 0, 'L', false, 0);
        $pdf->cell(63, 6, $tab['Nom_Client'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(110);
        $pdf->MultiCell(25, 6, "Modalité de\npaiement :", 0, 'L', false, 0);
        $pdf->cell(0, 6, $tab['Modalite_Paiement'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);
        $pdf->cell(35, 6, 'Prix HT :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(63, 6, $this->formatNumber($tab['Prix_HT']), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        /** DEBUT MISE AU REBUT */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Mise au rebut', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(41, 242);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 160, 3, 'F');
        $pdf->Ln(10, true);


        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(35, 6, 'Motif :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Motif_Mise_Rebut'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);
        /** FIN MISE AUREBUT */


        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetXY(118, 2);
        $pdf->Cell(35, 6, 'Email émetteur : ' . $tab['Email_Emetteur'], 0, 0, 'L');

        //2ème pages
        if ($tab['OR'] === 'OUI') {
            $this->affichageListOr($pdf, $orDb);
        }
        //3eme page
        // if ($tab['typeMouvement'] === 'MISE AU REBUT' && $tab['image'] !== '') {
        //     $this->AjoutImage($pdf, $tab);
        // }

        $Dossier = $_ENV['BASE_PATH_FICHIER'].'/bdm/';
        $pdf->Output($Dossier . $tab['Num_BDM'] . '_' . $tab['Agence_Service_Emetteur_Non_separer'] . '.pdf', 'F');

        //$pdf->Output('exemple.pdf', 'I');
    }


    /**
     * Ajout d'image dans le pdf
     *
     * @param [type] $pdf
     * @param [type] $tab
     * @return void
     */
    public function AjoutImage($pdf, $tab)
    {
        $pdf->AddPage();
            $imagePath = $tab['image'];
            if ($tab['extension'] === 'JPG') {
                $pdf->Image($imagePath, 15, 25, 180, 150, 'JPG', '', '', true, 75, '', false, false, 0, false, false, false);
            } elseif ($tab['extension'] === 'JEPG') {
                $pdf->Image($imagePath, 15, 25, 180, 150, 'JEPG', '', '', true, 75, '', false, false, 0, false, false, false);
            } elseif ($tab['extension'] === 'PNG') {
                $pdf->Image($imagePath, 15, 25, 180, 150, 'PNG', '', '', true, 75, '', false, false, 0, false, false, false);
            }
    }

    
    /**
     * Recuperation et affichage des or dans une tableau
     *
     * @param [type] $pdf
     * @param [type] $orDb
     * @return void
     */
    public function affichageListOr($pdf, $orDb)
    {
        //ajouter une nouvelle page et changer l'orientation de la page
        $pdf->AddPage('L');

        $header1 = ['Agence', 'Service', 'numor', 'Date', 'ref', 'interv', 'intitulé travaux', 'Ag/Serv débiteur', 'montant total', 'montant pièces', 'montant piece livrées'];

        // Commencer le tableau HTML
        $html = '<h2 style="text-align:center">Liste OR encours</h2>';

        $html .= '<table border="1" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px; ">';

        $html .= '</colgroup>';
        $html .= '<thead>';
        $html .= '<tr>';
        foreach ($header1 as $key => $value) {
            if ($key === 0) {
                $html .= '<th style="width: 75px" >' . $value . '</th>';
            } elseif ($key === 2) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 3) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 4) {
                $html .= '<th style="width: 90px" >' . $value . '</th>';
            } elseif ($key === 5) {
                $html .= '<th style="width: 30px" >' . $value . '</th>';
            } elseif ($key === 6) {
                $html .= '<th style="width: 230px;" >' . $value . '</th>';
            } elseif ($key === 7) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 8) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 9) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 10) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } else {
                $html .= '<th >' . $value . '</th>';
            }
        }
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        // Ajouter les lignes du tableau
        foreach ($orDb as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $cell) {

                if ($key === 'agence') {
                    $html .= '<td style="width: 75px"  >' . $cell . '</td>';
                } elseif ($key === 'slor_numor') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'date') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'seor_refdem_lib') {
                    $html .= '<td style="width: 90px"  >' . $cell . '</td>';
                } elseif ($key === 'sitv_interv') {
                    $html .= '<td style="width: 30px"  >' . $cell . '</td>';
                } elseif ($key === 'stiv_comment') {
                    $html .= '<td style="width: 230px; text-align: left;"  >' . $cell . '</td>';
                } elseif ($key === 'agence_service') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'montant_total') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'montant_pieces') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'montant_pieces_livrees') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } else {
                    $html .= '<td  >' . $cell . '</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';


        $pdf->writeHTML($html, true, false, true, false, '');
    }
}