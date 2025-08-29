<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class DocumentationController extends Controller
{
    /**
     * @Route("/doc/technique", name="app_doc_tech_index")
     */
    public function index()
    {
        $docDir = 'C:/wamp64/www/Hffintranet/docs/technique/'; // Chemin absolu
        $files = glob($docDir . '*.md');

        error_log("DEBUG: Files found by glob: " . print_r($files, true)); // Ligne de débogage

        $documents = [];
        if ($files) {
            foreach ($files as $file) {
                $filename = basename($file);
                $documents[] = [
                    'filename' => $filename,
                    'title' => $this->getTitleFromFilename(pathinfo($filename, PATHINFO_FILENAME)),
                ];
            }
        }

        error_log("DEBUG: Documents array before Twig: " . print_r($documents, true)); // NEW DEBUG LINE

        self::$twig->display('documentation/index.html.twig', [
            'documents' => $documents,
        ]);
    }

    private function getTitleFromFilename(string $filename): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $filename));
    }

    /**
     * @Route("/doc/technique/{filename}", name="app_doc_tech_show")
     */
    public function show(string $filename)
    {
        // Sécurité : Nettoyer le nom de fichier pour éviter les attaques de type "directory traversal"
        $safeFilename = basename($filename);
        $filePath = 'docs/technique/' . $safeFilename;

        if (!file_exists($filePath)) {
            // Idéalement, lancer une exception 404 de Symfony
            throw new \Exception("Fichier de documentation non trouvé."); 
        }

        $markdownContent = file_get_contents($filePath);

        // Utiliser Parsedown qui est déjà dans les dépendances du projet
        $parser = new \Parsedown();
        $htmlContent = $parser->text($markdownContent);

        $title = $this->getTitleFromFilename(pathinfo($safeFilename, PATHINFO_FILENAME));

        self::$twig->display('documentation/show.html.twig', [
            'title' => $title,
            'content' => $htmlContent,
        ]);
    }
}
