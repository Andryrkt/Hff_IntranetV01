<?php


namespace App\Api\ddp;

use App\Controller\Controller;
use App\Model\ddp\DemandePaiementModel;
use Symfony\Component\Routing\Annotation\Route;

class ModalStatutClotureApi extends Controller
{
    /**
     * @Route("/ddp/api/statut-cloture/{numeroDa}/{numeroCde}", name="ddp_api_statut_cloture")
     *
     * @return void
     */
    public function statutCloture($numeroDa, $numeroCde)
    {
        $ddpModel = new DemandePaiementModel();
        $infoStatutCloture = $ddpModel->getInfoDdpDa($numeroDa, $numeroCde);

        header("Content-type:application/json");
        echo json_encode($infoStatutCloture);
    }
}
