<?php

namespace App\Service\genererPdf;

class GeneratePdf
{
    /**
     * Copie le PDF generer dans l'upload 
     */
    public function copyInterneToDOXCUWARE($NumDom, $codeAg_serv)
    {
        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/' . strtolower(substr($NumDom, 0, 3)) . '/' . $NumDom . '_'  . $codeAg_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    public function copyToDw($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/oRValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/vor/oRValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDwFactureSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/factureValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/vfac/factureValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDwRiSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = 'C:/DOCUWARE/RAPPORT_INTERVENTION/RI_' . $numeroOR . '-' . $numeroVersion . '.pdf';
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/vri/RI_' . $numeroOR . '-' . $numeroVersion . '.pdf'; // avec tiret 6
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWCdeSoumis($fileName)
    {
        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/cde/' . $fileName;
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    public function copyToDWDevisSoumis($fileName)
    {

        $cheminFichierDistant = self::BASE_CHEMIN_DOCUWARE . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = self::BASE_CHEMIN_DU_FICHIER . 'dit/dev/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWAcSoumis($fileName)
    {
        $cheminFichierDistant = self::BASE_CHEMIN_DOCUWARE . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = self::BASE_CHEMIN_DU_FICHIER . 'dit/ac_bc/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWCdeFnrSoumis($fileName)
    {
        $cheminFichierDistant = self::BASE_CHEMIN_DOCUWARE . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = self::BASE_CHEMIN_DU_FICHIER . 'cde_fournisseur/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }
    /**
     * Méthode pour ajouter un titre au PDF
     * 
     * @param TCPDF $pdf le pdf à générer
     * @param string $title le titre du pdf
     * @param string $font le style de la police pour le titre
     * @param string $style le font-weight du titre
     * @param int $size le font-size du titre
     * @param string $align l'alignement
     * @param int $lineBreak le retour à la ligne
     */
    protected function addTitle(TCPDF $pdf, string $title, string $font = 'helvetica', string $style = 'B', int $size = 10, string $align = 'L', int $lineBreak = 5)
    {
        $pdf->setFont($font, $style, $size);

        // Calculer la largeur de la cellule en fonction de la page
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];

        // Utiliser MultiCell pour gérer les titres longs
        $pdf->MultiCell($pageWidth, 6, $title, 0, $align, false, 1, '', '', true);

        // Ajouter un espace après le titre
        $pdf->Ln($lineBreak);
    }

    /** 
     * Méthode pour ajouter des détails (sommaire) au PDF
     * 
     * @param TCPDF $pdf le pdf à générer
     * @param array $details tableau des détails à insérer dans le PDF
     * @param string $font le style de la police pour les détails
     * @param int $fontSize le font-size du détail 
     * @param int $labelWidth la largeur du label du tableau de détails
     * @param int $valueWidth la largeur du value du tableau de détails
     * @param int $lineHeight le retour à la ligne après chaque détail
     * @param int $spacingAfter le retour à la ligne après les détails
     */
    protected function addSummaryDetails(TCPDF $pdf, array $details, string $font = 'helvetica', int $fontSize = 10, int $labelWidth = 45, int $valueWidth = 50, int $lineHeight = 5, int $spacingAfter = 5)
    {
        $pdf->setFont($font, '', $fontSize);

        foreach ($details as $label => $value) {
            $pdf->Cell($labelWidth, 6, ' - ' . $label, 0, 0, 'L', false, '', 0, false, 'T', 'M');
            $pdf->Cell($valueWidth, 5, ': ' . $value, 0, 0, '', false, '', 0, false, 'T', 'M');
            $pdf->Ln($lineHeight, true);
        }

        $pdf->Ln($spacingAfter, true);
    }

    /** 
     * Méthode pour ajouter des détails (en gras) au PDF
     * 
     * @param TCPDF $pdf le pdf à générer
     * @param array $details tableau des détails à insérer dans le PDF
     * @param string $font le style de la police pour les détails
     * @param int $labelWidth la largeur du label du tableau de détails
     * @param int $valueWidth la largeur du value du tableau de détails
     * @param int $lineHeight le retour à la ligne après chaque détail
     * @param int $spacing espace
     * @param int $spacingAfter le retour à la ligne après le bloc de détails
     */
    protected function addDetailsBlock(TCPDF $pdf, array $details, string $font = 'helvetica', int $labelWidth = 45, int $valueWidth = 50, int $lineHeight = 6, int $spacing = 2, int $spacingAfter = 10)
    {
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        foreach ($details as $label => $value) {
            // Positionnement du label
            $pdf->SetXY($startX, $pdf->GetY() + $spacing);
            $pdf->setFont($font, 'B', 10);
            $pdf->Cell($labelWidth, $lineHeight, $label, 0, 0, 'L', false, '', 0, false, 'T', 'M');

            // Positionnement de la valeur
            $pdf->setFont($font, '', 10);
            $pdf->Cell($valueWidth, $lineHeight, ': ' . $value, 0, 1, '', false, '', 0, false, 'T', 'M');
        }

        // Ajout d'un espace après le bloc
        $pdf->Ln($spacingAfter, true);
    }

    /** 
     * Méthode pour générer une ligne de caractères (ligne de séparation)
     * 
     * @param TCPDF $pdf le pdf à générer
     * @param string $char le caractère pour faire la séparation
     * @param string $font le style de la police pour le caractère
     */
    protected function generateSeparateLine(TCPDF $pdf, string $char = '*', string $font = 'helvetica')
    {
        // Définir la largeur disponible
        $pageWidth = $pdf->GetPageWidth(); // Largeur totale de la page
        $leftMargin = $pdf->getOriginalMargins()['left']; // Marge gauche
        $rightMargin = $pdf->getOriginalMargins()['right']; // Marge droite
        $usableWidth = $pageWidth - $leftMargin - $rightMargin; // Largeur utilisable

        // Définir la police
        $pdf->SetFont($font, '', 12);

        $charWidth = $pdf->GetStringWidth($char); // Largeur d'un seul caractère
        $numChars = floor($usableWidth / $charWidth); // Nombre total de caractères pour remplir la largeur
        $line = str_repeat($char, $numChars); // Répéter le caractère

        // Afficher la ligne de séparation
        $pdf->Cell(0, 10, $line, 0, 1, 'C'); // Une cellule contenant la ligne
        //$pdf->Ln(5); // Ajouter un espacement en dessous de la ligne
    }
}
