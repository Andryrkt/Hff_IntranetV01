<?php

namespace App\Api\planningMagasin;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Model\planning\ModalPlanningModel;
use App\Model\planningMagasin\PlanningMagasinModel;
use Symfony\Component\Routing\Annotation\Route;

class PlanningApi extends Controller
{
    private PlanningMagasinModel $planningMagasinModel;

    public function __construct()
    {
        parent::__construct();
        $this->planningMagasinModel = new PlanningMagasinModel();
    }

    /**
     * @Route("/serviceDebiteurPlanningMagasin-fetch/{agenceId}")
     */
    public function serviceDebiteur($agenceId)
    {
        if ($agenceId == 100) {
            $serviceDebiteur = [];
        } else {
            $serviceDebiteur = $this->planningMagasinModel->recuperationServiceDebite($agenceId);
        }

        header("Content-type:application/json");

        echo json_encode($serviceDebiteur);
    }

    /**
     * @Route("/api/magasin-commercial", name="api_magasin_commercial")
     */
    public function nomCommercial()
    {
        $commercial = $this->planningMagasinModel->recupCommercial();

        header("Content-type:application/json");

        echo json_encode($commercial);
    }
}
