<?php

namespace App\Service\genererPdf;

use TCPDF;
use App\Service\GlobalVariablesService;

class GenererPdfCasier extends GeneratePdf
{

/**
     * generer pdf changement de Casier
     */

    function genererPdfCasier(array $tab)
    {
        $pdf = new TCPDF();


        $pdf->AddPage();


        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath = $_ENV['BASE_PATH_LONG'] . '/Views/assets/henrifraise.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        //$pdf->Cell(45, 12, 'LOGO', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell(110, 12, 'CREATION DE CASIER', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);



        $pdf->Cell(35, 6, $tab['Num_CAS'], 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

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
        $pdf->Cell(40, 6, 'Nouveau casier', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(45, 80);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 155, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->MultiCell(30, 6, "Agence de rattachement", 0, 'L', false, 0);
        $pdf->cell(63, 6, $tab['Agence'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);
        $pdf->cell(30, 6, 'Motif de Création', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Motif_Creation'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


        $pdf->MultiCell(30, 6, "Client :", 0, 'L', false, 0);
        $pdf->cell(63, 6, $tab['Client'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(110);
        $pdf->cell(24, 6, 'Chantier :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Chantier'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        // // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetXY(118, 2);
        $pdf->Cell(35, 6, 'Email émetteur : ' . $tab['Email_Emetteur'], 0, 0, 'L');




        $Dossier = $_ENV['BASE_PATH_FICHIER'].'/cas/';
        $pdf->Output($Dossier . $tab['Num_CAS'] . '_' . $tab['Agence_Service_Emetteur_Non_separer'] . '.pdf', 'F');
    }

}