<?php

namespace App\Service\genererPdf;

use TCPDF;
use App\Service\GlobalVariablesService;

class GeneratePdfDom extends GeneratePdf
{
        /**
         * Genere le PDF DEMANDE D'ORDRE DE MISSION (DOM)
         */
        public function genererPDF(array $tab)
        {
                $pdf = new TCPDF();

                $w50 = $this->getHalfWidth($pdf);
                $couleurTitre = [64, 64, 64]; // gris foncé

                $pdf->AddPage();

                // tête de page 
                $pdf->setY(0);
                $pdf->SetFont('pdfatimesbi', '', 8);
                $pdf->Cell(0, 8, $tab['MailUser'], 0, 1, 'R');

                // Logo HFF
                $logoPath = $_ENV['BASE_PATH_LONG'] . '/Views/assets/logoHff.jpg';
                $pdf->Image($logoPath, 10, 10, 60, 0, 'jpg');

                // Grand titre du pdf
                $pdf->SetFont('pdfatimesbi', 'B', 16);
                $pdf->setX($pdf->GetX() + 35);
                $pdf->Cell(0, 10, 'ORDRE DE MISSION ', 0, 0, 'C');
                $pdf->SetFont('pdfatimesbi', '', 12);
                $pdf->Cell(0, 10, 'Le: ' . $tab['dateS'], 0, 1, 'R');

                $pdf->SetTextColor(...$couleurTitre);
                $pdf->setX($pdf->GetX() + 35);
                $pdf->Cell(0, 10, 'Agence/Service débiteur : ' . $tab['codeServiceDebitteur'] . '-' . $tab['serviceDebitteur'], 0, 0, 'C');
                $pdf->SetTextColor(0, 0, 0);
                $pdf->setX($w50 + 10);
                $pdf->Cell(0, 10, $tab['NumDom'], 0, 1, 'R');


                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(12, 10, 'Type : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 12, 10, $tab['typMiss'], 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->SetFont('pdfatimesbi', '', 10);

                /** SITE */
                $pdf->setTextColor(...$couleurTitre); // Bleu
                $pdf->Cell(11, 10, 'Site : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 11, 10, $tab['Site'], 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->Ln(); // Nouvelle ligne
                $pdf->SetFont('pdfatimesbi', '', 12);

                /** AGENCE */
                $pdf->setTextColor(...$couleurTitre); // Bleu
                $pdf->Cell(17, 10, 'Agence : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 17, 10, $tab['Code_serv'], 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->SetFont('pdfatimesbi', '', 10);

                /** SERVICE */
                $pdf->setTextColor(...$couleurTitre); // Bleu
                $pdf->Cell(17, 10, 'Service : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 17, 10, $tab['serv'], 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->Ln(); // Nouvelle ligne

                $pdf->SetFont('pdfatimesbi', '', 12);
                $pdf->Rect($pdf->GetX(), $pdf->GetY(), $w50 * 2, 10);  // Bordure englobant tout
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(12, 10, 'Nom : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell((2 * $w50) - 12, 10, $tab['Nom'], 0, 1);

                $pdf->Rect($pdf->GetX(), $pdf->GetY(), $w50 * 2, 10);  // Bordure englobant tout
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(19, 10, 'Prénoms : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell((2 * $w50) - 19, 10, $tab['Prenoms'], 0, 1);

                $pdf->Rect($pdf->GetX(), $pdf->GetY(), $w50 * 2, 10);  // Bordure englobant tout
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(21, 10, 'Matricule : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell((2 * $w50) - 21, 10, $tab['matr'], 0, 1);

                $pdf->Rect($pdf->GetX(), $pdf->GetY(), $w50 * 2, 10);  // Bordure englobant tout
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(13, 10, 'Motif : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell((2 * $w50) - 13, 10, $tab['motif'], 0, 1);

                $pdf->Rect($pdf->GetX(), $pdf->GetY(), $w50 * 2, 10);  // Bordure englobant tout
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(20, 10, 'Catégorie : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell((2 * $w50) - 20, 10, $tab['CategoriePers'], 0, 1);

                $pdf->Rect($pdf->GetX(), $pdf->GetY(), $w50 * 2, 10);  // Bordure englobant tout
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(17, 10, 'Période : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell((2 * $w50) - 17, 10, $tab['NbJ'] . ' jour(s)       Du    ' . $tab['dateD'] . '    à    ' . $tab['heureD'] . '    Heures    au    ' . $tab['dateF'] . '    à    ' . $tab['heureF'] . '    Heures ', 0, 1);

                $pdf->Rect($pdf->GetX(), $pdf->GetY(), $w50 * 2, 10);  // Bordure englobant tout
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(37, 10, 'Lieu d intervention : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell((2 * $w50) - 37, 10, $tab['lieu'], 0, 1);

                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(14, 10, 'Client : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 14, 10, $tab['Client'], 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(18, 10, 'N° fiche : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 18, 10, $tab['fiche'], 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->Ln(); // Nouvelle ligne

                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(32, 10, 'Véhicule société : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 32, 10, $tab['vehicule'], 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(29, 10, 'N° de véhicule : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 29, 10, $tab['numvehicul'], 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->Ln(); // Nouvelle ligne

                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(48, 10, 'Indemnité Forfaitaire (+) : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 48, 10, $tab['idemn'] . ' ' . $tab['Devis'] . ' / jour', 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(30, 10, 'Supplément (+) : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 30, 10, $tab['Bonus'] . ' ' . $tab['Devis'] . ' / jour', 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->Ln(); // Nouvelle ligne

                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(48, 10, 'Indemnité de chantier (-) : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 48, 10, $tab['Idemn_depl'] . ' ' . $tab['Devis'] . ' / jour', 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(37, 10, 'Total indemnité (=) : ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 37, 10, $tab['totalIdemn'] . ' ' . $tab['Devis'], 0, 0);
                $pdf->Rect($pdf->GetX() - $w50, $pdf->GetY(), $w50, 10);  // Bordure englobant tout

                $pdf->Ln(); // Nouvelle ligne

                $pdf->setY(165);

                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(20, 10, 'Autres: ', 0, 1);

                $pdf->setXY(30, 165);
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(80, 10,  'MOTIF', 1, 0, 'C');
                $pdf->Cell(80, 10, '' . 'MONTANT', 1, 1, 'C');
                $pdf->setX(30);

                $titreMontantTotal = "MONTANT TOTAL A " . ($tab['typMiss'] === 'TROP PERCU' ? 'RETIRER' : 'PAYER');

                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell(80, 10,  $tab['motifdep01'], 1, 0, 'L');
                $pdf->Cell(80, 10, '' . $tab['montdep01'] . ' ' . $tab['Devis'], 1, 1, 'C');
                $pdf->setX(30);
                $pdf->Cell(80, 10,  $tab['motifdep02'], 1, 0, 'L');
                $pdf->Cell(80, 10, '' . $tab['montdep02'] . ' ' . $tab['Devis'], 1, 1, 'C');
                $pdf->setX(30);
                $pdf->Cell(80, 10,  $tab['motifdep03'], 1, 0, 'L');
                $pdf->Cell(80, 10, '' . $tab['montdep03'] . ' ' . $tab['Devis'], 1, 1, 'C');
                $pdf->setX(30);
                $pdf->Cell(80, 10,  'Total autre ', 1, 0, 'C');
                $pdf->Cell(80, 10,   $tab['totaldep'] . ' ' . $tab['Devis'], 1, 1, 'C');
                $pdf->setX(30);
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(80, 10, $titreMontantTotal, 1, 0, 'C');
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell(80, 10, $tab['AllMontant'] . ' ' . $tab['Devis'], 1, 1, 'C');

                /** Ligne de NB sur les montants */
                $NB_1 = $tab['Idemn_depl'] === '0' ? '' : 'Montant total à payer = ' . $tab['totalIdemn'] . ' - (' . $tab['Idemn_depl'] . '*' . $tab['NbJ'] . ') = ' . $tab['AllMontant'];
                $NB_2 = $tab['Idemn_depl'] === '0' ? '' : $tab['Idemn_depl'] . " étant l'indemnité journalière reçue mensuellement du fait que l'agent se trouve sur un site";
                $pdf->setY(227);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 0, $NB_1, 0, 1);
                $pdf->Cell(0, 0, $NB_2, 0, 1);

                /** Mode de paiement */
                $pdf->SetFont('pdfatimesbi', '', 12);
                $pdf->setTextColor(...$couleurTitre);
                $pdf->Cell(35, 10, 'Mode de paiement: ', 0, 0);
                $pdf->setTextColor(0, 0, 0); // Noir
                $pdf->Cell($w50 - 35, 10, $tab['libmodepaie'], 0, 0);
                $pdf->setX($w50 + 20);
                $pdf->Cell($w50, 10, $tab['mode'], 0, 1);

                /** Génération de fichier */
                $Dossier = $_ENV['BASE_PATH_FICHIER'] . '/dom/';
                $pdf->Output($Dossier . $tab['NumDom'] . '_' . $tab['codeAg_serv'] . '.pdf', 'F');
        }

        private function getHalfWidth(TCPDF $pdf)
        {
                $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
                $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)

                $usable_width = $w_total - $margins['left'] - $margins['right'];
                return $usable_width / 2;
        }
}
