<?php

namespace App\Api\dit;

use App\Model\dit\DitModel;
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
    public function agence($id) {

        $agence = self::$em->getRepository(Agence::class)->find($id);
    
        $service = $agence->getServices();

        //   $services = $service->getValues();
            $services = [];
        foreach ($service as $key => $value) {
            $services[] = [
                'value' => $value->getId(),
                'text' => $value->getCodeService() . ' ' . $value->getLibelleService()
            ];
        }

        
        //dd($services);
        header("Content-type:application/json");

        echo json_encode($services);

        //echo new JsonResponse($services);
    }


    /**
     * @Route("/fetch-materiel/{idMateriel?0}/{numParc?0}/{numSerie?}", name="fetch_materiel", methods={"GET"})
     * cette fonctin permet d'envoyer les informations materiels en ajax
     */
    public function fetchMateriel($idMateriel,  $numParc, $numSerie)
    {
        $ditModel = new DitModel();
        // Récupérer les données depuis le modèle
        $data = $ditModel->findAll($idMateriel, $numParc, $numSerie);

        // Vérifiez si les données existent
        if (!$data) {
            return new JsonResponse(['error' => 'No material found'], Response::HTTP_NOT_FOUND);
        }
        header("Content-type:application/json");

        $jsonData = json_encode($data);

        $this->testJson($jsonData);
    }
}