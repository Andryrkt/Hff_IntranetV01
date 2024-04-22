<?php

namespace App\Service;

use TCPDF;

//require_once __DIR__ . '/TCPDF-main/tcpdf.php';
//require_once('Model/FPDI-2.6.0/src/autoload.php');

class GenererPdf
{

    function genererPdfCasier(array $tab)
    {
        $pdf = new TCPDF();


        $pdf->AddPage();


        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/assets/henrifraise.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        //$pdf->Cell(45, 12, 'LOGO', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell(110, 12, 'CREATION DE CASIER', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);



        $pdf->Cell(35, 6, $tab['Num_CAS'], 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        //$pdf->setFont('helvetica', 'B', 12);
        // $pdf->setAbsX(55);
        // $pdf->SetTextColor(0, 0, 0);
        // $pdf->cell(110, 6, $tab['typeMouvement'], 0, 0, 'C', true, '', 0, false, 'T', 'M');

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




        $Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/cas/';
        $pdf->Output($Dossier . $tab['Num_CAS'] . '_' . $tab['Agence_Service_Emetteur_Non_separer'] . '.pdf', 'F');
    }

    /**
     * Generer pdf badm 
     */
    function genererPdfBadm(array $tab)
    {

        $pdf = new TCPDF();


        $pdf->AddPage();


        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/assets/henrifraise.jpg';
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

        $pdf->MultiCell(35, 6, "Heures :", 0, 'L', false, 0);
        $pdf->cell(63, 6, $tab['Heures_Machine'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(110);
        $pdf->cell(24, 6, 'Kilométrage :', 0, 0, '', false, '', 0, false, 'T', 'M');
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
        $pdf->cell(63, 6, $tab['Cout_Acquisition'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(110);
        $pdf->cell(20, 6, 'Amort :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['Amort'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(0, 0, 0);
        $pdf->setAbsXY(130, 196);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 70, 1, 'F');
        $pdf->Ln(3, true);
        $pdf->setAbsX(110);
        $pdf->cell(20, 6, 'VNC :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $tab['VNC'], 1, 0, '', false, '', 0, false, 'T', 'M');
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
        $pdf->cell(63, 6, $tab['Prix_HT'], 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


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






        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetXY(118, 2);
        $pdf->Cell(35, 6, 'Email émetteur : ' . $tab['Email_Emetteur'], 0, 0, 'L');


        if ($tab['typeMouvement'] === 'MISE AU REBUT' && $tab['image'] !== '') {
            $pdf->AddPage();
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/images/' . $tab['image'];
            $pdf->Image($imagePath, 15, 25, 180, 150, 'JPG', '', '', true, 75, '', false, false, 0, false, false, false);
        }


        $Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/bdm/';
        $pdf->Output($Dossier . $tab['Num_BDM'] . '_' . $tab['Agence_Service_Emetteur_Non_separer'] . '.pdf', 'F');

        //$pdf->Output('exemple.pdf', 'I');
    }


    /**
     * Genere le PDF DOM
     */
    //pdf
    public function genererPDF(array $tab)
    {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, 10, 10, 30, '', 'jpg');

        $pdf->SetFont('pdfatimesbi', 'B', 16);
        $pdf->Cell(0, 10, 'ORDRE DE MISSION ', 0, 1, 'C');
        $pdf->SetFont('pdfatimesbi', '', 12);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(0, 10, 'Agence/Service débiteur : ' . $tab['codeServiceDebitteur'] . '-' . $tab['serviceDebitteur'], 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, $tab['NumDom'], 0, 1, 'R');
        $pdf->Ln(10);
        $pdf->SetFont('pdfatimesbi', '', 12);

        $pdf->setY(30);
        $pdf->Cell(80, 10, 'Type  : ' . $tab['typMiss'], 0, 0);
        // $pdf->Cell(80, 10,  $autrTyp, 0, 0, 'L');
        $pdf->Cell(110, 10, 'Le: ' . $tab['dateS'], 0, 1, 'R');
        $pdf->Cell(80, 10, 'Agence: ' . $tab['Code_serv'], 0, 0);
        $pdf->Cell(110, 10, 'Catégorie : ' . $tab['CategoriePers'], 0, 1, 'R');
        $pdf->Cell(80, 10, 'Service: ' . $tab['serv'], 0, 0);
        $pdf->Cell(110, 10, 'Site : ' . $tab['Site'], 0, 1, 'R');

        $pdf->Cell(80, 10, 'Matricule : ' . $tab['matr'], 0, 0);
        $pdf->Cell(110, 10, 'Ideminté de déplacement: ' . $tab['Idemn_depl'], 0, 1, 'R');

        $pdf->Cell(0, 10, 'Nom : ' . $tab['Nom'], 0, 1);
        $pdf->Cell(0, 10, 'Prénoms: ' . $tab['Prenoms'], 0, 1);
        $pdf->Cell(40, 10, 'Période: ' . $tab['NbJ'] . ' Jour(s)', 0, 0);
        $pdf->Cell(50, 10, 'Soit du ' . $tab['dateD'], 0, 0, 'C');
        $pdf->Cell(30, 10, 'à  ' . $tab['heureD'] . ' Heures ', 0, 0);
        $pdf->Cell(30, 10, ' au  ' . $tab['dateF'], 0, 0);
        $pdf->Cell(30, 10, '  à ' . $tab['heureF'] . ' Heures ', 0, 1);
        $pdf->Cell(0, 10, 'Motif : ' . $tab['motif'], 0, 1);
        $pdf->Cell(80, 10, 'Client : ' . $tab['Client'], 0, 0);
        $pdf->Cell(30, 10, 'N° fiche : ' . $tab['fiche'], 0, 1);
        $pdf->Cell(0, 10, 'Lieu d intervention : ' . $tab['lieu'], 0, 1);
        $pdf->Cell(80, 10, 'Véhicule société : ' . $tab['vehicule'], 0, 0);
        $pdf->Cell(60, 10, 'N° de véhicule: ' . $tab['numvehicul'], 0, 1);

        $pdf->Cell(70, 10, 'Indemnité Forfaitaire: ' . $tab['idemn'] . ' ' . $tab['Devis'] . '/j', 0, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(35, 10, 'Supplément /jour: ', 0, 0, 'L');
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(35, 10,  $tab['Bonus'] . ' ' . $tab['Devis'] . '/j', 0, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(55, 10, 'Total indemnité: ' . $tab['totalIdemn'] . ' ' . $tab['Devis'], 0, 1, 'R');

        $pdf->setY(150);
        $pdf->Cell(20, 10, 'Autres: ', 0, 1, 'R');
        $pdf->setY(160);
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'MOTIF', 1, 0, 'C');
        $pdf->Cell(80, 10, '' . 'MONTANT', 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  '   ' . $tab['motifdep01'], 1, 0, 'L');
        $pdf->Cell(80, 10, '' . $tab['montdep01'] . ' ' . $tab['Devis'], 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  '  ' . $tab['motifdep02'], 1, 0, 'L');
        $pdf->Cell(80, 10, '' . $tab['montdep02'] . ' ' . $tab['Devis'], 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  '   ' . $tab['motifdep03'], 1, 0, 'L');
        $pdf->Cell(80, 10, '' . $tab['montdep03'] . ' ' . $tab['Devis'], 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'Total autre ', 1, 0, 'C');
        $pdf->Cell(80, 10,   $tab['totaldep'] . ' ' . $tab['Devis'], 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'MONTANT TOTAL A PAYER ', 1, 0, 'C');
        $pdf->Cell(80, 10,   $tab['AllMontant'] . ' ' . $tab['Devis'], 1, 1, 'C');

        $pdf->setY(230);
        $pdf->Cell(60, 10, 'Mode de paiement : ', 0, 0);
        $pdf->Cell(60, 10, $tab['libmodepaie'], 0, 0);
        $pdf->Cell(60, 10, $tab['mode'], 0, 1);


        $pdf->SetFont('pdfatimesbi', '', 10);
        $pdf->setY(240);
        $pdf->setX(10);
        //  $pdf->Cell(40, 10, 'LE DEMANDEUR', 1, 0, 'C');
        $pdf->Cell(60, 8, 'CHEF DE SERVICE', 1, 0, 'C');
        $pdf->Cell(60, 8, 'VISA RESP. PERSONNEL ', 1, 0, 'C');
        $pdf->Cell(60, 8, 'VISA COMPTABILITE', 1, 1, 'C');


        $pdf->Cell(60, 20, ' ', 1, 0, 'C');
        $pdf->Cell(60, 20, '  ', 1, 0, 'C');
        $pdf->Cell(60, 20, ' ', 1, 1, 'C');

        //pieds de page 
        $pdf->setY(0);
        $pdf->SetFont('pdfatimesbi', '', 8);




        // Positionnement de la deuxième cellule à droite
        $pdf->Cell(0, 8, $tab['MailUser'], 0, 1, 'R');


        //
        $Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/dom/';
        $pdf->Output($Dossier . $tab['NumDom'] . '_' . $tab['codeAg_serv'] . '.pdf', 'F');
    }

    // copy interne vers DOCUWARE
    /**
     * Copie le PDF generer dans l'upload 
     */
    public function copyInterneToDOXCUWARE($NumDom, $codeAg_serv)
    {

        if (substr($NumDom, 0, 3) === 'DOM') {
            $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\ORDERE DE MISSION\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
            // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        } else if (substr($NumDom, 0, 3) === 'BDM') {
            $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\MOUVEMENT MATERIEL\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
            // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        } else if (substr($NumDom, 0, 3) === 'CAS') {
            $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\CASIER\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
            // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        }


        $cheminDestinationLocal = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/' . strtolower(substr($NumDom, 0, 3)) . '/' . $NumDom . '_'  . $codeAg_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }
}
