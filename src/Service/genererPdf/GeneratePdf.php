<?php

namespace App\Service\genererPdf;

use TCPDF;

class GeneratePdf
{
    protected $baseCheminDuFichier;
    protected $baseCheminDocuware;

    public function __construct(
        ?string $baseCheminDuFichier = null,
        ?string $baseCheminDocuware = null
    ) {
        // Injection de dépendances avec fallback sur les variables d'environnement
        $this->baseCheminDuFichier = $baseCheminDuFichier ?? rtrim($_ENV['BASE_PATH_FICHIER'] ?? '', '/\\') . '/';
        $this->baseCheminDocuware = $baseCheminDocuware ?? rtrim($_ENV['BASE_PATH_DOCUWARE'] ?? '', '/\\') . '/';
    }

    protected function copyFile(string $sourcePath, string $destinationPath): void
    {
        if (!file_exists($sourcePath)) {
            throw new \Exception("Le fichier source n'existe pas : $sourcePath");
        }

        // Créer le répertoire de destination s'il n'existe pas
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            if (!mkdir($destinationDir, 0755, true)) {
                throw new \Exception("Impossible de créer le répertoire : $destinationDir");
            }
        }

        if (!copy($sourcePath, $destinationPath)) {
            throw new \Exception("Impossible de copier le fichier : $sourcePath vers $destinationPath");
        }

        echo "Fichier copié avec succès : $destinationPath\n";
    }


    /**
     * ORDRE DE MISSION et BADM
     */
    public function copyInterneToDOCUWARE($NumDom, $codeAg_serv)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        $cheminDestinationLocal = $this->baseCheminDuFichier . strtolower(substr($NumDom, 0, 3)) . '/' . $NumDom . '_'  . $codeAg_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    // ORDRE DE REPARATION (OR)
    public function copyToDw($numeroVersion, $numeroOR, $suffix)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/oRValidation_' . $numeroOR . '-' . $numeroVersion . '#' . $suffix . '.pdf';
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'vor/oRValidation_' . $numeroOR . '-' . $numeroVersion . '#' . $suffix . '.pdf';
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }


    // Facture
    public function copyToDwFactureSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/factureValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'vfac/factureValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }


    public function copyToDwFacture($numeroVersion, $numeroDoc)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/validation_facture_client_' . $numeroDoc . '_' . $numeroVersion . '.pdf';
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'vfac/validation_facture_client_' . $numeroDoc . '_' . $numeroVersion . '.pdf';
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }


    public function copyToDwFactureFichier($numeroVersion, $numeroDoc, array $pathFichiers)
    {
        for ($i = 0; $i < count($pathFichiers); $i++) {
            $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/facture_client_' . $numeroDoc . '_' . $numeroVersion . '_' . $i . '.pdf';
            $cheminDestinationLocal = $pathFichiers[$i];
            $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
        }
    }

    //Rapport d'intervention
    public function copyToDwRiSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'RAPPORT_INTERVENTION/RI_' . $numeroOR . '-' . $numeroVersion . '.pdf';
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'vri/RI_' . $numeroOR . '-' . $numeroVersion . '.pdf'; // avec tiret 6
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWCdeSoumis($fileName)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'cde/' . $fileName;
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    // devis DIT
    public function copyToDWDevisSoumis($fileName)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'dit/dev/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWFichierDevisSoumis($fileName)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'dit/dev/fichiers/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWFichierDevisSoumisVp($fileName)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'VERIFICATION_PRIX/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'dit/dev/fichiers/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //bon de commande
    public function copyToDWAcSoumis($fileName)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'dit/ac_bc/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //commande fournisseur
    public function copyToDWCdeFnrSoumis($fileName)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'cde_fournisseur/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }


    /** DEMANDE DE PAIEMENT */
    public function copyToDwDdp(string $fileName, $numDdp, $numeroversion)
    {
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'ddp/' . $numDdp . '_New_' . $numeroversion . '/' . $fileName;
        $cheminFichierDistant = $this->baseCheminDocuware . 'DEMANDE_DE_PAIEMENT/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    // demande appro à valider
    public function copyToDWDaAValider($numDa, string $suffix = "#_a_valider")
    {
        $cheminFichierDistant = $this->baseCheminDocuware . "ORDRE_DE_MISSION/$numDa#_a_valider.pdf";
        $cheminDestinationLocal = $this->baseCheminDuFichier . "da/$numDa/$numDa$suffix.pdf";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //bon de commande de demande appro
    public function copyToDWBcDa($fileName, $numDa)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'da/' . $numDa . '/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //bon de commande de demande appro
    public function copyToDWFacBlDa($fileName, $numDa)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'da/' . $numDa . '/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }


    // devis Magasin
    public function copyToDWDevisMagasin($fileName, $numeroDevis)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'magasin/devis/' . $numeroDevis . '/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    // BL - INTERNE FTU
    public function copyToDWBlFutInterne($fileName)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'BON DE SORTIE FTU/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'bl/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //FACTURE -BL (clients) FTU
    public function copyToDWBlFutFactureClient($fileName)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'BONLIV EXTERNE MAGFTU/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'bl/' . $fileName;
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
        $pdf->Ln($lineBreak, true);
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

    protected function renderTextWithLine($pdf, $text, $totalWidth = 190, $lineOffset = 3, $font = 'helvetica', $fontStyle = 'B', $fontSize = 11, $textColor = [14, 65, 148], $lineColor = [14, 65, 148], $lineHeight = 1)
    {
        // Set font and text color
        $pdf->setFont($font, $fontStyle, $fontSize);
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

        // Calculate text width
        $textWidth = $pdf->GetStringWidth($text);

        // Add the text
        $pdf->Cell($textWidth, 6, $text, 0, 0, 'L');

        // Set fill color for the line
        $pdf->SetFillColor($lineColor[0], $lineColor[1], $lineColor[2]);

        // Calculate the remaining width for the line
        $remainingWidth = $totalWidth - $textWidth - $lineOffset;

        // Calculate the position for the line (next to the text)
        $lineStartX = $pdf->GetX() + $lineOffset; // Add a small offset
        $lineStartY = $pdf->GetY() + 3; // Adjust for alignment

        // Draw the line
        if ($remainingWidth > 0) { // Only draw if there is space left for the line
            $pdf->Rect($lineStartX, $lineStartY, $remainingWidth, $lineHeight, 'F');
        }

        // Move to the next line
        $pdf->Ln(6, true);
    }
}
