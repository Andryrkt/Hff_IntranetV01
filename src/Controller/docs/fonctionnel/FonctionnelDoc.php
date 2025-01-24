<?php

namespace App\Controller\docs\fonctionnel;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class FonctionnelDoc extends Controller
{
    /**
     * @Route("/doc/fonctionnel", name="index_index")
     */
    public function index()
    {


        // Chemin vers votre fichier Markdown
        $markdownFile = dirname(dirname(dirname(dirname(__DIR__)))). DIRECTORY_SEPARATOR .'docs/fonctionnel/index.md';

 
        // Vérifiez si le fichier existe avant de tenter de le lire
        if (!file_exists($markdownFile)) {
            die("Le fichier $markdownFile n'existe pas.");
        }

        // Lire le contenu du fichier Markdown
        $markdownContent = file_get_contents($markdownFile);

        // Convertir le Markdown en HTML
        $htmlContent = $this->parsedown->text($markdownContent);

        // Rendre le template avec le contenu HTML
        self::$twig->display('doc/fonctionnel/fonctionnel.html.twig', 
        [
            'content' => $htmlContent
        ]);
    }
}