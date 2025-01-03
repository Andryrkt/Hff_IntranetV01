<?php

namespace App\Api\planning;

use App\Controller\Controller;
use App\Model\planning\PlanningModel;
use App\Entity\dit\DemandeIntervention;
use App\Model\planning\ModalPlanningModel;
use Symfony\Component\Routing\Annotation\Route;

class PlanningApi extends Controller
{
    private PlanningModel $planningModel;

    public function __construct()
    {
        parent::__construct();
        $this->planningModel = new PlanningModel();
    }

    /**
     * @Route("/serviceDebiteurPlanning-fetch/{agenceId}")
     */
    public function serviceDebiteur($agenceId)
    { 
        if($agenceId == 100){
            $serviceDebiteur = [];
        } else {
            $serviceDebiteur = $this->planningModel->recuperationServiceDebite($agenceId);
        }
        
        header("Content-type:application/json");

        echo json_encode($serviceDebiteur);
    }
}