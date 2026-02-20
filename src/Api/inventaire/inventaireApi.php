<?php

namespace App\Api\inventaire;

use App\Controller\Controller;
use App\Model\inventaire\InventaireModel;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class inventaireApi extends Controller
{
    private InventaireModel $inventaireModel;
    public function __construct()
    {
        parent::__construct();
        $this->inventaireModel = new InventaireModel;
    }
    /**
     * @Route("/api/delete/fichier/{numInv}", name = "api_delete_fichier_inventaire")
     * 
     * @return void
     */
    public function supprimerFichierInventaire($numInv)
    {
        $uploadsDir = rtrim($_ENV['BASE_PATH_FICHIER'], '/') . '/inventaire';
        $filePath = $uploadsDir . "/INV_$numInv.xlsx";

        try {
            // Vérification de l'existence du fichier
            if (!file_exists($filePath)) {
                return new Response("Fichier introuvable : INV_$numInv.xlsx", 404);
            }

            // Tentative de suppression
            if (!unlink($filePath)) {
                return new Response("Impossible de supprimer le fichier INV_$numInv.xlsx", 500);
            }

            return new Response("Fichier INV_$numInv.xlsx supprimé avec succès", 200);
        } catch (\Exception $e) {
            return new Response("Erreur lors de la suppression : " . $e->getMessage(), 500);
        }
    }


    /**
     * @Route("/api/upload/fichier/{numInv}", name = "api_upload_fichier_inventaire")
     * 
     * @return void
     */
    public function uploadFichierInventaire(Request $request, $numInv)
    {
        $file = $request->files->get('fichier');

        if ($file) {
            $allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                return new Response("Type de fichier non autorisé.", 400);
            }

            $uploadsDir = $_ENV['BASE_PATH_FICHIER'] . '/inventaire';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            $newFilename = "INV_$numInv." . $file->guessExtension();

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
     * @Route("/api/listeInventaireDispo-fetch/{agence}/{dateDeb}/{dateFin}", name="api_listeInventaireDispo_fetch")
     */
    public function listeInventaireDispo($agence, $dateDeb, $dateFin)
    {
        $criteria = [
            'agence'    => $agence,
            'dateDebut' => $dateDeb instanceof \DateTime ? $dateDeb : new \DateTime($dateDeb),
            'dateFin'   => $dateFin instanceof \DateTime ? $dateFin : new \DateTime($dateFin),
        ];


        $listeInventaireDispo = $this->inventaireModel->recuperationListeInventaireDispo($criteria);
        $tab = [];
        foreach ($listeInventaireDispo as $keys => $listes) {
            foreach ($listes as $key => $liste) {
                $tab[] = [
                    'id' => $keys,
                    'value' => $liste,
                    'label' => trim($key)
                ];
            }
        }




        header("Content-type:application/json");
        echo json_encode($tab);
    }
}
