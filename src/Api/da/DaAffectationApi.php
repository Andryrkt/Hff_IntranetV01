<?php

namespace App\Api\da;

use App\Controller\Controller;
use App\Model\da\DaModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/api/demande-appro/autocomplete") */
class DaAffectationApi extends Controller
{
    /**
     * @Route("/all-reference", name="api_da_affectation_all_reference", methods={"GET"})
     */
    public function allReference(): JsonResponse
    {
        try {
            $daModel = new DaModel;
            $data = $daModel->getAllReferenceAutorisees();

            if (empty($data)) {
                return new JsonResponse([
                    'message' => 'Aucune donnée trouvée',
                    'data'    => []
                ], JsonResponse::HTTP_OK);
            }

            return new JsonResponse([
                'message' => 'Données chargées avec succès',
                'data'    => $data
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors du chargement des données: ' . $e->getMessage(),
                'data'    => []
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
