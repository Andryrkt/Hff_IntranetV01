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
     * @return string[] les mêmes chemins, dans le même ordre
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

        if ($this->estLisibleParFpdi($fichier)) {
            return $fichier;
        }

        $fichierTemporaire = $fichier . "_temp.pdf";

        // Commande Ghostscript
        $commande = "\"{$this->ghostscript}\" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -o \"$fichierTemporaire\" \"$fichier\"";

        exec($commande, $sortie, $codeRetour);

        if ($codeRetour !== 0) {
            echo "Sortie Ghostscript : " . implode("\n", $sortie);
            throw new \Exception("Erreur lors de la conversion du PDF avec Ghostscript");
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
        if (strtolower(pathinfo($fichier, PATHINFO_EXTENSION)) !== 'pdf') {
            return false; // ce n'est pas un PDF : on laisse Ghostscript décider, comme avant
        }

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
}
