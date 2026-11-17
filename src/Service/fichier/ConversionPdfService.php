<?php

namespace App\Service\fichier;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Prépare les PDF avant la fusion (FusionPdf, FPDI) en les convertissant au format PDF 1.4 avec Ghostscript.
 *
 * La conversion ne sert qu'à rendre le fichier lisible par le parseur gratuit de FPDI, qui ne sait pas lire
 * certains PDF récents (flux d'objets / table de références compressés). Ghostscript coûte environ 1 s par fichier
 * (essentiellement son démarrage) : un fichier déjà lisible par FPDI, comme les pages de garde générées par
 * l'intranet, n'est donc pas converti.
 */
class ConversionPdfService
{
    private const GHOSTSCRIPT_PAR_DEFAUT = 'C:\Program Files\gs\gs10.05.0\bin\gswin64c.exe';

    private string $ghostscript;

    public function __construct(?string $ghostscript = null)
    {
        // chemin modifiable par la variable d'environnement GHOSTSCRIPT_PATH (ex. autre version ou autre OS)
        $this->ghostscript = $ghostscript ?? ($_ENV['GHOSTSCRIPT_PATH'] ?? self::GHOSTSCRIPT_PAR_DEFAUT);
    }

    /**
     * Rend chaque fichier lisible par FPDI (conversion sur place si nécessaire)
     *
     * @param string[] $fichiersAvecChemin
     * @return string[] les mêmes chemins, dans le même ordre (une image JPG/PNG est remplacée par son PDF « image.jpg.pdf »)
     */
    public function convertirLesPdf(array $fichiersAvecChemin): array
    {
        $fichiers = [];
        foreach ($fichiersAvecChemin as $fichier) {
            $fichiers[] = $this->convertir($fichier);
        }

        return $fichiers;
    }

    /**
     * Convertit le fichier sur place avec Ghostscript, sauf s'il est déjà lisible par FPDI
     */
    public function convertir(string $fichier): string
    {
        // Vérifier si le fichier existe et est accessible
        if (!file_exists($fichier)) {
            throw new \Exception("Fichier introuvable : $fichier");
        }

        if (!is_readable($fichier)) {
            throw new \Exception("Le fichier PDF ne peut pas être lu : $fichier");
        }

        // nature du fichier lue dans son contenu, pas dans son extension
        $typeImage = @getimagesize($fichier)[2] ?? null;
        if (in_array($typeImage, [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            return $this->imageEnPdf($fichier);
        }

        if (file_get_contents($fichier, false, null, 0, 5) !== '%PDF-') {
            $nom = basename($fichier);
            $format = pathinfo($fichier, PATHINFO_EXTENSION) ?: 'inconnu';
            throw new \Exception("Le fichier « $nom » (format $format) ne peut pas être fusionné : seuls les PDF et les images JPG/PNG sont acceptés, joignez-le en PDF.");
        }

        if ($this->estLisibleParFpdi($fichier)) {
            return $fichier;
        }

        $fichierTemporaire = $fichier . "_temp.pdf";

        // Commande Ghostscript (2>&1 : ses messages d'erreur sortent sur stderr)
        $commande = "\"{$this->ghostscript}\" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -o \"$fichierTemporaire\" \"$fichier\" 2>&1";

        exec($commande, $sortie, $codeRetour);

        if ($codeRetour !== 0) {
            if (is_file($fichierTemporaire)) unlink($fichierTemporaire);
            $nom = basename($fichier);
            throw new \Exception("Erreur lors de la conversion du PDF « $nom » avec Ghostscript (code $codeRetour) : " . implode(' | ', array_slice($sortie, -5)));
        }

        // Remplacement du fichier
        if (!rename($fichierTemporaire, $fichier)) {
            throw new \Exception("Impossible de remplacer l'ancien fichier PDF.");
        }

        return $fichier;
    }

    /**
     * Fait les mêmes lectures que la fusion (FusionPdf::mergePdfs) : ouverture du fichier et import de chaque page.
     * Quelques millisecondes, contre environ 1 s pour une conversion Ghostscript.
     */
    private function estLisibleParFpdi(string $fichier): bool
    {
        try {
            $pdf = new Fpdi();
            $nombrePages = $pdf->setSourceFile($fichier);
            for ($page = 1; $page <= $nombrePages; $page++) {
                $pdf->importPage($page);
            }

            return $nombrePages > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Met l'image (JPG/PNG) sur une page A4 centrée, à côté de l'original : « photo.jpg » → « photo.jpg.pdf »
     */
    private function imageEnPdf(string $image): string
    {
        $fichierPdf = $image . '.pdf';
        [$largeurImage, $hauteurImage] = getimagesize($image);

        try {
            $pdf = new Fpdi();
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false); // une image pleine hauteur ne doit pas créer de page blanche
            $pdf->AddPage($largeurImage > $hauteurImage ? 'L' : 'P');

            $largeurPage = $pdf->getPageWidth();
            $hauteurPage = $pdf->getPageHeight();
            $echelle = min($largeurPage / $largeurImage, $hauteurPage / $hauteurImage);
            $largeur = $largeurImage * $echelle;
            $hauteur = $hauteurImage * $echelle;

            $pdf->Image($image, ($largeurPage - $largeur) / 2, ($hauteurPage - $hauteur) / 2, $largeur, $hauteur);
            $pdf->Output($fichierPdf, 'F');
        } catch (\Throwable $e) {
            throw new \Exception("Impossible de convertir l'image « " . basename($image) . " » en PDF : " . $e->getMessage(), 0, $e);
        }

        return $fichierPdf;
    }
}
