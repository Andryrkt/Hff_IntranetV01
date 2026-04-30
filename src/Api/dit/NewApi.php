<?php

namespace App\Api\dit;

use App\Model\dit\DitModel;
use App\Entity\admin\Agence;
use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class NewApi extends Controller
{



    /**
     * @Route("/api/fetch-all-materiel", name="api_fetch_all_materiel", methods={"GET"})
     * cette fonctin permet d'envoyer les informations materiels en ajax
     */
    public function fetchMateriel()
    {
        $ditModel = new DitModel();
        // Récupérer les données depuis le modèle
        $data = $ditModel->findAll();

        // Vérifiez si les données existent
        if (!$data) {
            return new JsonResponse(['error' => 'No material found'], Response::HTTP_NOT_FOUND);
        }
        header("Content-type:application/json");

        $jsonData = json_encode($data);

        $this->testJson($jsonData);
    }
}
