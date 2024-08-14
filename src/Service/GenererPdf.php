<?php

namespace App\Service;

use App\Controller\Traits\FormatageTrait;
use App\Entity\DemandeIntervention;
use PhpParser\Node\Expr\Isset_;
use TCPDF;

//require_once __DIR__ . '/TCPDF-main/tcpdf.php';
//require_once('Model/FPDI-2.6.0/src/autoload.php');

class GenererPdf
{
    use FormatageTrait;


    /**
     * Genere le PDF DEMANDE D'ORDRE DE MISSION (DOM)
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

        // if (substr($NumDom, 0, 3) === 'DOM') {
        //     $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\DEVELOPPEMENT\\ORDERE DE MISSION\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
        //     // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        // } else if (substr($NumDom, 0, 3) === 'BDM') {
        //     $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\DEVELOPPEMENT\\MOUVEMENT MATERIEL\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
        //     // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        // } else if (substr($NumDom, 0, 3) === 'CAS') {
        //     $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\DEVELOPPEMENT\\CASIER\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
        //     // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        // }  else if (substr($NumDom, 0, 3) === 'DIT') {
        //     $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\DEVELOPPEMENT\\DIT\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
        //     // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        // }

        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        $cheminDestinationLocal = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/' . strtolower(substr($NumDom, 0, 3)) . '/' . $NumDom . '_'  . $codeAg_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }
}
