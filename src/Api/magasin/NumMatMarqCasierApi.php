<?php

namespace App\Api\magasin;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Repository\dit\DitRepository;
use Symfony\Component\Routing\Annotation\Route;

class NumMatMarqCasierApi extends Controller
{
    /**
     * @Route("/api/numMat-marq-casier/{numOr}", name="api_numMat_marq_casier")
     */
    public function NumMatMarqCasier($numOr)
    {   
        $ditRepository = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numOr]);
        if($ditRepository != null){
            $idMateriel = $ditRepository->getIdMateriel();

            $marqueCasier = $this->ditModel->recupMarqueCasierMateriel($idMateriel);

            $numMatMarqCasier = [
                'numMat' => $idMateriel,
                'marque' => $marqueCasier[0]['marque'],
                'casier' => $marqueCasier[0]['casier']
            ];
            header("Content-type:application/json");

            echo json_encode($numMatMarqCasier);
        }
        
    }
}