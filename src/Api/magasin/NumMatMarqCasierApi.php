<?php

namespace App\Api\magasin;

use App\Model\dit\DitModel;
use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Routing\Annotation\Route;

class NumMatMarqCasierApi extends Controller
{
    /**
     * @Route("/api/numMat-marq-casier/{numOr}", name="api_numMat_marq_casier")
     */
    public function NumMatMarqCasier($numOr)
    {
        $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numOr]);
        $response = [];

        if ($ditRepository !== null) {
            $idMateriel = $ditRepository->getIdMateriel();
            $ditModel = new DitModel();
            $marqueCasier = $ditModel->recupMarqueCasierMateriel($idMateriel);

            if (!empty($marqueCasier) && is_array($marqueCasier)) {
                // Prenez uniquement le premier élément car la logique précédente écrasait les autres
                $item = $marqueCasier[0];

                $response = [
                    'numMat' => $idMateriel,
                    'numSerie' => $item['num_serie'] ?? null,
                    'numParc' => $item['num_parc'] ?? null,
                    'marque' => $item['marque'] ?? null,
                    'model' => $item['modele'] ?? null,
                    'designation' => $item['designation'] ?? null,
                    'casier' => $item['casier'] ?? null
                ];
            }
        }

        header("Content-type:application/json");
        echo json_encode($response);
        exit; // Assurez-vous que rien d'autre n'est exécuté après la réponse
    }
}
