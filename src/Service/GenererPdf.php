<?php

namespace App\Service;

use App\Entity\DemandeIntervention;
use PhpParser\Node\Expr\Isset_;
use TCPDF;

//require_once __DIR__ . '/TCPDF-main/tcpdf.php';
//require_once('Model/FPDI-2.6.0/src/autoload.php');

class GenererPdf
{
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
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        //$pdf->Cell(45, 12, 'LOGO', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell(110, 6, 'DEMANDE D\'INTERVENTION', 0, 0, 'C', false, '', 0, false, 'T', 'M');


        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $dit->getNumeroDemandeIntervention() , 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        $pdf->setFont('helvetica', 'B', 12);
        $pdf->setAbsX(55);
        $pdf->cell(110, 6, $dit->getTypeDocument()->getDescription(), 0, 0, 'C', false, '', 0, false, 'T', 'M');
        
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $dit->getDateDemande()->format('d/m/Y'), 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Objet :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(165, 6, $dit->getObjetDemande(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $pdf->cell(25, 6, 'Détails :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(165, 10, $dit->getDetailDemande(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(15, true);

        $pdf->MultiCell(25, 6, "Catégorie :", 0, 'L', false, 0);
        $pdf->cell(30, 6, $dit->getCategorieDemande()->getLibelleCategorieAteApp(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(85);
        $pdf->MultiCell(40, 6, " avis recouvrement :", 0, 'L', false, 0);
        $pdf->cell(20, 6, $dit->getAvisRecouvrement(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(150);
        $pdf->cell(30, 6, 'Devis demandé :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, '', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        /** INTERVENTION */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Intervention', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(50, 63);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 150, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Date prévue :', 0, 0, '', false, '', 0, false, 'T', 'M');
        if($dit->getDatePrevueTravaux() !== null && !empty($dit->getDatePrevueTravaux())){
            $pdf->cell(50, 6, $dit->getDatePrevueTravaux()->format('d/m/Y'), 1, 0, '', false, '', 0, false, 'T', 'M');
        } else {
            $pdf->cell(50, 6, $dit->getDatePrevueTravaux(), 1, 0, '', false, '', 0, false, 'T', 'M');

        }
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Urgence :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getIdNiveauUrgence()->getDescription(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        /**AGENCE-SERVICE */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Agence - Service', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(50, 85);
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

        /**REPARATION */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, 'Réparation', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(35, 107);
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
        $pdf->cell(30, 6, 'Réaliser par :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getReparationRealise(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        /**CLIENT */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(40, 6, ' Client ', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(30, 129);
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 170, 3, 'F');
        $pdf->Ln(10, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->MultiCell(25, 6, "Nom :", 0, 'L', false, 0);
        $pdf->cell(45, 6, $dit->getNomClient(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(85);
        $pdf->MultiCell(27, 6, "Sous contrat :", 0, 'L', false, 0);
        $pdf->cell(20, 6, $dit->getClientSousContrat(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(135);
        $pdf->cell(25, 6, 'N° téléphone :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getNumeroTel(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);


        /** CARACTERISTIQUE MATERIEL */
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->SetTextColor(14, 65, 148);
        $pdf->Cell(50, 6, 'Caractéristiques du matériel', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->SetFillColor(14, 65, 148);
        $pdf->setAbsXY(70, 151);
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
        $pdf->setAbsXY(40, 193);
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

        

        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetXY(110, 2);
        $pdf->Cell(35, 6, "email : ". $dit->getMailDemandeur() , 0, 0, 'L');





        /**DEUXIEME PAGE */
        $pdf->AddPage();

        $header1 = ['Agences', 'Services', 'Date','numor', 'interv', 'commentaire', 'Sommes'];

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
                    $html .= '<th style="width: 270px; font-weight: bold;" >' . $value . '</th>';
                } elseif ($key === 6) {
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
                        $html .= '<td style="width: 270px; text-align: left;"  >' . $cell . '</td>';
                    } elseif ($key === 'somme') {
                        $html .= '<td style="width: 50px; text-align: right;"  >' . $cell . '</td>';
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


    //$pdf->Output('exemple.pdf', 'I');
    $Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/dit/';
        $pdf->Output($Dossier . $dit->getNumeroDemandeIntervention() . '_' . str_replace("-", "", $dit->getAgenceServiceEmetteur()). '.pdf', 'F');
    }

    /**
     * generer pdf changement de Casier
     */

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
    function genererPdfBadm(array $tab, array $orDb = [], array $or2 = [])
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



        //2ème pages

        if ($tab['typeMouvement'] === 'MISE AU REBUT' && $tab['image'] !== '') {
            $pdf->AddPage();
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/templates/badm/mise_rebut/images/' . $tab['image'];
            // var_dump($tab['extension']);
            // var_dump($imagePath);
            if ($tab['extension'] === 'JPG') {
                $pdf->Image($imagePath, 15, 25, 180, 150, 'JPG', '', '', true, 75, '', false, false, 0, false, false, false);
            } elseif ($tab['extension'] === 'JEPG') {
                $pdf->Image($imagePath, 15, 25, 180, 150, 'JEPG', '', '', true, 75, '', false, false, 0, false, false, false);
            } elseif ($tab['extension'] === 'PNG') {
                $pdf->Image($imagePath, 15, 25, 180, 150, 'PNG', '', '', true, 75, '', false, false, 0, false, false, false);
            }
        }



        if ($tab['OR'] === 'OUI') {

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

        $Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/bdm/';
        $pdf->Output($Dossier . $tab['Num_BDM'] . '_' . $tab['Agence_Service_Emetteur_Non_separer'] . '.pdf', 'F');

        //$pdf->Output('exemple.pdf', 'I');
    }


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

        if (substr($NumDom, 0, 3) === 'DOM') {
            $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\ORDERE DE MISSION\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
            // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        } else if (substr($NumDom, 0, 3) === 'BDM') {
            $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\MOUVEMENT MATERIEL\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
            // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        } else if (substr($NumDom, 0, 3) === 'CAS') {
            $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\CASIER\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
            // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        }  else if (substr($NumDom, 0, 3) === 'DIT') {
            $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\DIT\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
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
