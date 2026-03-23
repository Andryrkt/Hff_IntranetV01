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
     * @Route("/api/serviceDebiteurPlanningMagasin-fetch/{agenceId}", name="api_serviceDebiteurPlanningMagasin_fetch")
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
     * @Route("/api/magasin-commercial/{codeAgence}", name="api_magasin_commercial")
     */
    public function nomCommercial(string $codeAgence)
    {
        $commercial = $this->planningMagasinModel->recupCommercial($codeAgence);

        header("Content-type:application/json");

        echo json_encode($commercial);
    }
}
