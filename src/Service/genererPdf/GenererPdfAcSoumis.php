<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\FormatageTrait;
use IntlDateFormatter;
use App\Entity\dit\AcSoumis;

class GenererPdfAcSoumis extends GeneratePdf
{
    use FormatageTrait;

    function genererPdfAc(AcSoumis $acSoumis, string $numeroDunom)
    {
        $locale = 'fr_FR';
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        // Création de l'objet PDF
        $pdf = new HeaderFooterAcPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuration du document
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Henri Fraise');
        $pdf->SetTitle('Accusé de Réception');
        $pdf->SetSubject('Accusé de réception du bon de commande');

        // Définir les marges
        $pdf->SetMargins(25, 20, 25); // Marges : gauche = 25mm, haut = 20mm, droite = 25mm
        $pdf->SetAutoPageBreak(TRUE, 20);

        // Supprimer l'en-tête et le pied de page automatique
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Ajouter une page
        $pdf->AddPage();

        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Public/build/images/logoHFF.jpg';
        $pdf->Image($logoPath, 27, 10, 40, '', 'jpg');
        // Contenu HTML avec texte justifié
        $html = '
        <style>
            h1 {
                text-align: center;
                font-size: 18px;
            }
            p {
                text-align: justify;
                line-height: 1.5;
            }
            .header {
                text-align: left;
                font-size: 11px;
            }
            .footer {
                text-align: center;
                font-size: 10px;
            }
        </style>
        <div class="header">
            <b>HENRI FRAISE FILS & CIE</b><br>
            BP 28, 90 Làlana Ravoninahitriniarivo,<br>
            Antananarivo 101 - Madagascar<br>
            (+261) 20 22 123 45
        </div>
        <br>
        <h1>ACCUSE DE RECEPTION</h1>
        <br>
        <p>
            <b>'.$formatter->format($acSoumis->getDateCreation()).'</b><br>
            <b>A l\'attention de '. $acSoumis->getNomClient().' </b> <br>
            <b>'.$acSoumis->getEmailClient().'</b><br>
        </p>
        <p>
            <b>Objet : Accusé de réception du bon de commande n°'.$acSoumis->getNumeroBc().'</b>
        </p>
        <p>
            Madame, Monsieur,<br><br>
            Nous accusons réception de votre bon de commande n°'.$acSoumis->getNumeroBc().', daté du '.$formatter->format($acSoumis->getDateBc()).', portant sur '.$acSoumis->getDescriptionBc().'.<br><br>
            Cette commande fait suite à notre devis n° '.$acSoumis->getNumeroDevis().' ('.$acSoumis->getNumeroDit().') en date du '.$formatter->format($acSoumis->getDateDevis()).' dont la date d\'expiration est '.$formatter->format($acSoumis->getDateExpirationDevis()).', d\'un montant total de '.$this->formatNumber($acSoumis->getMontantDevis()).$acSoumis->getDevise().'. Nous confirmons que votre commande a été enregistrée.<br><br>
            Pour toute question ou demande d\'information complémentaire concernant votre commande ou les travaux à réaliser, nous restons à votre disposition. Vous pouvez nous contacter par email à '.$acSoumis->getEmailContactHff().' ou par téléphone au '.$acSoumis->getTelephoneContactHff().'.<br><br>
            Nous vous remercions pour votre confiance et restons à votre service pour toute autre demande.<br><br>
            Dans l\'attente, nous vous prions d\'agréer, Madame, Monsieur, l\'expression de nos salutations distinguées.<br>
        </p>
        ';

        // Écriture du contenu HTML dans le PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Public/build/images/footer.png';
        $pdf->Image($logoPath, 27, 265, 160, '', 'png');
        // Générer le fichier PDF
        $Dossier = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/ac_bc/';
        $filePath = $Dossier . 'bc_' . $numeroDunom . '_'.$acSoumis->getNumeroVersion().'.pdf';
        $pdf->Output($filePath, 'F');
    }
}