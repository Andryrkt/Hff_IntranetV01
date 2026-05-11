<?php


namespace App\Api\ddp;

use App\Controller\Controller;
use App\Model\ddp\DemandePaiementModel;
use Symfony\Component\Routing\Annotation\Route;

class ModalStatutClotureApi extends Controller
{
    /**
     * @Route("/api/statut-compta/{numeroDa}/{numeroCde}", name="api_statut_compta")
     *
     * @return void
     */
    public function statutCompta(string $numeroDa, string $numeroCde)
    {
        $ddpModel = new DemandePaiementModel();
        $infoStatutCloture = $ddpModel->getInfoDdpDa($numeroDa, $numeroCde);

        header("Content-type:application/json");
        echo json_encode($infoStatutCloture);
    }
}
