<?php

namespace App\Api\pol\ddd;

use App\Model\dit\DitModel;
use App\Entity\admin\Agence;
use App\Controller\Controller;
use App\Model\ddd\DemandeDiagnosticPneuModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class NewApi extends Controller
{



    /**
     * @Route("/api/fetch-all-available-materiel", name="api_fetch_all_available_materiel", methods={"GET"})
     * cette fonctin permet d'envoyer les informations materiels en ajax
     */
    public function fetchAvailableMateriel()
    {
        $demandeDiagnosticPneuModel = new DemandeDiagnosticPneuModel();
        // Récupérer les données depuis le modèle
        $data = $demandeDiagnosticPneuModel->findAllAValaibleMateriel();

        // Vérifiez si les données existent
        if (!$data) {
            return new JsonResponse(['error' => 'No material found'], Response::HTTP_NOT_FOUND);
        }
        header("Content-type:application/json");

        $jsonData = json_encode($data);

        $this->testJson($jsonData);
    }
}
