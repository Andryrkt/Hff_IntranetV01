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
        $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numOr]);
        if ($ditRepository != null) {
            $idMateriel = $ditRepository->getIdMateriel();

            $marqueCasier = $this->ditModel->recupMarqueCasierMateriel($idMateriel);

            //changer les elements du tableau
            $numMatMarqCasier = array_map(function ($item) use ($idMateriel) {
                return [
                    'numMat' => $idMateriel,
                    'numSerie' => $item['num_serie'],
                    'numParc' => $item['num_parc'],
                    'marque' => $item['marque'],
                    'model' => $item['modele'],
                    'designation' => $item['designation'],
                    'casier' => $item['casier']
                ];
            }, $marqueCasier);

            // Fusionner les résultats dans un seul tableau
            $numMatMarqCasier = array_merge(...$numMatMarqCasier);

            header("Content-type:application/json");

            echo json_encode($numMatMarqCasier);
        }
    }
}
