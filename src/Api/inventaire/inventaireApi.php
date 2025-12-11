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

            $uploadsDir = $_ENV['BASE_PATH_FICHIER'] . '/inventaire';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            $newFilename = "INV_$id." . $file->guessExtension();

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
                $tab[]=[
                    'id' => $keys,
                    'value' => $liste,
                    'label' =>trim($key)
                ];
            }
        }
        



        header("Content-type:application/json");
        echo json_encode($tab);
    }
}
