<?php

namespace App\Service\genererPdf;

use TCPDF;

class GeneratePdf
{
    protected string $basePathFile;
    protected string $basePathDocuWare;
    protected string $basePathAssets;

    public function __construct()
    {
        $this->basePathFile     = rtrim($_ENV['BASE_PATH_FICHIER'], '/\\');
        $this->basePathDocuWare = rtrim($_ENV['BASE_PATH_DOCUWARE'], '/\\');
        $this->basePathAssets   = rtrim($_ENV['BASE_PATH_LONG'], '/\\') . "/Views/assets";
    }

    protected function copyFile(string $sourcePath, string $destinationPath): bool
    {
        // Fonction interne pour tenter la copie
        $attemptCopy = function ($attemptNumber) use ($sourcePath, $destinationPath) {
            try {
                $destinationDir = dirname($destinationPath);

                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0777, true);
                }

                if (!file_exists($sourcePath) || !copy($sourcePath, $destinationPath)) {
                    return false;
                }

                // Vérification rapide
                return file_exists($destinationPath) && filesize($destinationPath) > 0;
            } catch (\Exception $e) {
                return false;
            }
        };

        // Première tentative
        if ($attemptCopy(1)) {
            echo "Fichier copié avec succès : $destinationPath\n";
            return true;
        }

        // Deuxième tentative après un court délai
        usleep(50000); // 50ms
        if ($attemptCopy(2)) {
            echo "Fichier copié avec succès après retry : $destinationPath\n";
            return true;
        }

        error_log("Échec de copyFile après 2 tentatives : $sourcePath");
        return false;
    }


    /**
     * ORDRE DE MISSION et BADM
     */
    public function copyInterneToDOCUWARE(string $numDoc, string $codeAgServ)
    {
        $dir = strtolower(substr($numDoc, 0, 3));
        $cheminFichierDistant = "{$this->basePathDocuWare}/ORDRE_DE_MISSION/{$numDoc}_{$codeAgServ}.pdf";
        $cheminDestinationLocal = "{$this->basePathFile}/{$dir}/{$numDoc}_{$codeAgServ}.pdf";
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }


    // Facture OR
    public function copyToDwFactureSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/ORDRE_DE_MISSION/factureValidation_{$numeroOR}_{$numeroVersion}.pdf";
        $cheminDestinationLocal = "{$this->basePathFile}/vfac/factureValidation_{$numeroOR}_{$numeroVersion}.pdf";
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }


    public function copyToDwFacture($numeroVersion, $numeroDoc)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/ORDRE_DE_MISSION/validation_facture_client_{$numeroDoc}_{$numeroVersion}.pdf";
        $cheminDestinationLocal = "{$this->basePathFile}/vfac/validation_facture_client_{$numeroDoc}_{$numeroVersion}.pdf";
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }


    public function copyToDwFactureFichier($numeroVersion, $numeroDoc, array $pathFichiers)
    {
        for ($i = 0; $i < count($pathFichiers); $i++) {
            $cheminFichierDistant = "{$this->basePathDocuWare}/ORDRE_DE_MISSION/facture_client_{$numeroDoc}_{$numeroVersion}_{$i}.pdf";
            $cheminDestinationLocal = $pathFichiers[$i];
            $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
        }
    }

    //Rapport d'intervention
    public function copyToDwRiSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/RAPPORT_INTERVENTION/RI_{$numeroOR}-{$numeroVersion}.pdf";
        $cheminDestinationLocal = "{$this->basePathFile}/vri/RI_{$numeroOR}-{$numeroVersion}.pdf"; // avec tiret 6
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWCdeSoumis($fileName)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/ORDRE_DE_MISSION/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/cde/{$fileName}";
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    // devis DIT (atelier) - page de garde (fiche de controle)
    public function copyToDWDevisSoumis($fileName)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/ORDRE_DE_MISSION/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/dit/dev/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWFichierDevisSoumis($fileName)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/DEVIS ATELIER/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/dit/dev/fichiers/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWFichierDevisSoumisVp($fileName)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/VERIFICATION_PRIX/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/dit/dev/fichiers/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //bon de commande DIT (atelier)
    public function copyToDWAcSoumis($fileName)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/BC ATELIER/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/dit/ac_bc/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    // commande fournisseur magasin
    public function copyToDWCdeFnrSoumis(string $cheminDuFichier, string $numCmde): bool
    {
        $cheminDW = "{$this->basePathDocuWare}/CDE FRN MAGASIN/$numCmde.pdf";
        return $this->copyFile($cheminDuFichier, $cheminDW);
    }


    /** DEMANDE DE PAIEMENT */
    public function copyToDwDdp(string $fileName, $numDdp)
    {
        $cheminDestinationLocal = "{$this->basePathFile}/ddp/{$numDdp}/{$fileName}";
        $cheminFichierDistant = "{$this->basePathDocuWare}/DEMANDE_DE_PAIEMENT/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    // demande appro DIRECT à valider
    public function copyToDWDaAValiderDirect($numDa, string $suffix = "#_a_valider")
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/DA DIRECTE/$numDa#_a_valider.pdf";
        $cheminDestinationLocal = "{$this->basePathFile}/da/$numDa/$numDa$suffix.pdf";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    // demande appro reappro mensuel à valider
    public function copyToDWDaAValiderReapproMensuel($numDa, string $suffix = "#_a_valider")
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/DA REAPPRO/$numDa#_a_valider.pdf";
        $cheminDestinationLocal = "{$this->basePathFile}/da/$numDa/$numDa$suffix.pdf";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    // demande appro reappro ponctuel à valider
    public function copyToDWDaAValiderReapproPonctuel($numDa, string $suffix = "#_a_valider")
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/DA REAPPRO PONCTUEL/$numDa#_a_valider.pdf";
        $cheminDestinationLocal = "{$this->basePathFile}/da/$numDa/$numDa$suffix.pdf";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //bon de commande de demande appro
    public function copyToDWBcDa($fileName, $numDa)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/BC APPRO/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/da/{$numDa}/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }


    //facture et bl de demande appro
    public function copyToDWFacBlDa($fileName, $numDa)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/Facture_BL frns apppro/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/da/{$numDa}/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //BAP de demande appro
    public function copyToDWBapDa($fileNamePathBap, $fileNameForDw, string $typeDemande)
    {
        $dirTabs = [
            'BAP' => "BON A PAYER",
            'DPR' => "DEMANDE_DE_REGULARISATION"
        ];
        $dir = $dirTabs[$typeDemande] ?? "DEMANDE_DE_PAIEMENT";
        $cheminFichierDistant = "{$this->basePathDocuWare}/{$dir}/{$fileNameForDw}";
        $cheminDestinationLocal = $fileNamePathBap;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //bl reappro de demande appro
    public function copyToDWBLReappro($fileName, $numDa)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/ORDRE_DE_MISSION/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/da/{$numDa}/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    // devis Magasin
    public function copyToDWDevisMagasin($fileName, $numeroDevis)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/DEVIS MAGASIN/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/magasin/devis/{$numeroDevis}/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    // BL - INTERNE FTU
    public function copyToDWBlFutInterne($fileName)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/BON DE SORTIE FTU/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/bl/{$fileName}";
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    //FACTURE -BL (clients) FTU
    public function copyToDWBlFutFactureClient($fileName)
    {
        $cheminFichierDistant = "{$this->basePathDocuWare}/BONLIV EXTERNE MAGFTU/{$fileName}";
        $cheminDestinationLocal = "{$this->basePathFile}/bl/{$fileName}";
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

    /**
     * Convertit une chaîne UTF-8 en Windows-1252 pour les polices core TCPDF (Helvetica, Times, Courier).
     * Sans cette conversion, les caractères spéciaux (°, é, à, etc.) s'affichent en "Â°", "Ã©", etc.
     *
     * @param string|null $text
     * @return string
     */
    protected function txt(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
    }
}
