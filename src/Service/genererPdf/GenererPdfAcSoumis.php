<?php

namespace App\Service\genererPdf;

use IntlDateFormatter;
use App\Entity\dit\AcSoumis;
use App\Controller\Traits\FormatageTrait;

class GenererPdfAcSoumis extends GeneratePdf
{
    use FormatageTrait;

    function genererPdfAc(AcSoumis $acSoumis, string $numeroDunom, string $numeroVersionMax, $nomFichier)
    {
        // Création de l'objet PDF
        $pdf = new HeaderFooterAcPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->Header();

        // Configuration du document
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Henri Fraise');
        $pdf->SetTitle('Accusé de Réception');
        $pdf->SetSubject('Accusé de réception du bon de commande');

        // Définir les marges
        $pdf->SetMargins(25, 20, 25); // Marges : gauche = 25mm, haut = 20mm, droite = 25mm
        $pdf->SetAutoPageBreak(TRUE, 20);

        //  afficher l'en-tête et Supprimerle pied de page automatique
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Ajouter une page
        $pdf->AddPage();

        $logoPath = $_ENV['BASE_PATH_LONG']. '/Public/build/images/logoHFF.jpg';
        $pdf->Image($logoPath, 27, 10, 40, '', 'jpg');

        // Définir la police pour l'email
        $pdf->SetFont('helvetica', '', 10);
        // Définir la couleur du texte
        $pdf->SetTextColor(0, 0, 0);
        // Positionner le texte de l'email
        $pdf->SetXY(11, 2); // Coordonnées pour l'email
        $pdf->Cell(35, 6, 'AR-HFF-' . $numeroVersionMax, 0, 0, 'L');

        $pdf->setY(20);

        // Contenu HTML avec texte justifié
        // Ajouter un tableau avec deux colonnes pour l'en-tête
        $html = '
        <style>
            table {
                width: 100%;
            }
            .left {
                text-align: left;
                font-size: 11px;
            }
            .right {
                text-align: right;
                font-size: 11px;
            }
            h1 {
                text-align: center;
                font-size: 18px;
            }
            p {
                text-align: justify;
                line-height: 1.5;
            }
            .footer {
                text-align: center;
                font-size: 10px;
            }
        </style>
        <table>
            <tr>
                <td class="left">
                    <b>HENRI FRAISE FILS & CIE</b><br>
                    BP 28, 90 Làlana Ravoninahitriniarivo,<br>
                    Antananarivo 101 - Madagascar<br>
                    (+261) 20 22 227 21
                </td>
                <td class="right">
                    <b>' . $acSoumis->getDateCreation()->format('d/m/Y') . '</b>
                </td>
            </tr>
        </table>

        <h1>ACCUSE DE RECEPTION</h1>

        <p>
            <b>A l\'attention de ' . $acSoumis->getNomClient() . ' </b> <br>
            <b>' . $acSoumis->getEmailClient() . '</b><br>
        </p>
        <p>
            <b>Objet : Accusé de réception du bon de commande </b> <br>
            <b>N°BC : </b> ' . $acSoumis->getNumeroBc() . ' <br>
            <b>Date BC : </b> ' . $acSoumis->getDateBc()->format('d/m/Y') . '
        </p>
        <p>
            Madame, Monsieur,<br><br>
            Nous accusons réception de votre bon de commande, portant sur <br>'. $acSoumis->getDescriptionBc() .'.<br><br>
            Cette commande fait suite à : <br>
            Devis : ' . $acSoumis->getNumeroDevis() . ' (' . $acSoumis->getNumeroDit() . ') du ' . $acSoumis->getDateDevis()->format('d/m/Y') . '<br>
            Montant HT : ' . $this->formatNumberGeneral($acSoumis->getMontantDevis(), ' ', '.', 2) . ' ' . $acSoumis->getDevise() . '. <br>
            Nous confirmons que votre commande a été enregistrée.<br><br>
            Pour toute question ou demande d\'information complémentaire concernant votre commande ou les travaux à réaliser, nous restons à votre disposition. Vous pouvez nous contacter par email  ' . $acSoumis->getEmailContactHff() . ' ou par téléphone  ' . $acSoumis->getTelephoneContactHff() . '.<br><br>
            Nous vous remercions pour votre confiance et restons à votre service pour toute autre demande.<br><br>
            Dans l\'attente, nous vous prions d\'agréer, Madame, Monsieur, l\'expression de nos salutations distinguées.<br>
        </p>
        ';

        // Écriture du contenu HTML dans le PDF
        $pdf->writeHTML($html, true, false, true, false, '');



        $logoPath = $_ENV['BASE_PATH_LONG'] . '/Public/build/images/footer.png';
        $pdf->Image($logoPath, 27, 265, 160, '', 'png');
        // Générer le fichier PDF
        $Dossier = $_ENV['BASE_PATH_FICHIER']. '/dit/ac_bc/';
        $filePath = $Dossier . $nomFichier;
        $pdf->Output($filePath, 'F');
    }
}
