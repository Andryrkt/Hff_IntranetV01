<?php

namespace App\Api\dit;

use App\Entity\admin\Agence;
use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class NewApi extends Controller
{



    /**
     * @Route("/api/fetch-materiel", name="api_fetch_materiel", methods={"GET"})
     * cette fonctin permet d'envoyer les informations materiels en ajax
     */
    public function fetchMateriel()
    {
        try {
            // Récupérer les données depuis le modèle
            $data = $this->getDitModel()->findAll();

            // Vérifiez si les données existent
            if (!$data) {
                return new JsonResponse(['error' => 'No material found'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
