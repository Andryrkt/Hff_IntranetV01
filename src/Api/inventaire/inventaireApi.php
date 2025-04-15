<?php

namespace App\Api\inventaire;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class inventaireApi extends Controller
{
    /**
     * @Route("/Upload/fichier/{id}", name = "upload_fichier_inventaire")
     * 
     * @return void
     */
    public function listeInventaire(Request $request, $id)
    {
        $file = $request->files->get('fichier');

    if ($file) {
        $allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return new Response("Type de fichier non autorisé.", 400);
        }

        $uploadsDir = $_ENV['BASE_PATH_FICHIER']. '/inventaire';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $newFilename = 'INV'.$id . $file->guessExtension();

        try {
            $file->move($uploadsDir, $newFilename);
            return new Response("Fichier uploadé avec succès : " . $newFilename);
        } catch (FileException $e) {
            return new Response("Erreur lors du téléchargement du fichier.", 500);
        }
    }

    return new Response("Aucun fichier reçu.", 400);
    }
    /**
     * @Route("/listeInventaireDispo-fetch/{agence}/{dateDeb}/{dateFin}")
     */
    public function listeInventaireDispo($agence,$dateDeb,$dateFin){
        
    }
}