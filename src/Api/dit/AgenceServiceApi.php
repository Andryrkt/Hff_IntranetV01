<?php

namespace App\Api\dit;

use App\Entity\admin\Agence;
use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class AgenceServiceApi extends Controller
{
    /**
     * @Route("/agence-fetch/{id}", name="fetch_agence", methods={"GET"})
     * cette fonction permet d'envoyer les donner du service debiteur selon l'agence debiteur en ajax
     * @return void
     */
    public function agence($id)
    {
        try {
            $agence = $this->getEntityManager()->getRepository(Agence::class)->find($id);

            if (!$agence) {
                return new JsonResponse(['error' => 'Agence not found'], Response::HTTP_NOT_FOUND);
            }

            $service = $agence->getServices();

            $services = [];
            foreach ($service as $key => $value) {
                $services[] = [
                    'value' => $value->getId(),
                    'text' => $value->getCodeService() . ' ' . $value->getLibelleService()
                ];
            }

            return new JsonResponse($services);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @Route("/fetch-materiel/{idMateriel?0}/{numParc?0}/{numSerie?}", name="fetch_materiel", methods={"GET"})
     * cette fonctin permet d'envoyer les informations materiels en ajax
     */
    public function fetchMateriel($idMateriel,  $numParc, $numSerie)
    {
        try {
            // Récupérer les données depuis le modèle
            $data = $this->getDitModel()->findAll($idMateriel, $numParc, $numSerie);

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
