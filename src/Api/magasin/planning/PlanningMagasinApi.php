<?php

namespace App\Api\magasin\planning;

use App\Controller\Controller;
use App\Model\magasin\planning\PlanningMagasinModel;
use Symfony\Component\Routing\Annotation\Route;

class PlanningMagasinApi extends Controller
{
    private PlanningMagasinModel $planningMagasinModel;

    public function __construct()
    {
        parent::__construct();
        $this->planningMagasinModel = new PlanningMagasinModel();
    }

    /**
     * @Route("/api/magasin-planning-liste-fournisseur", name="api_magasin_planning_liste_fournisseur")
     */
    public function listeFournisseur()
    {
        $fournisseurs = $this->planningMagasinModel->recupListeFournissseur();

        header("Content-type:application/json");
        echo json_encode($fournisseurs);
    }
}
