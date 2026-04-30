<?php

namespace App\Api\cde;

use App\Controller\Controller;
use App\Model\cde\CdefnrSoumisAValidationModel;
use App\Service\TableauEnStringService;
use Symfony\Component\Routing\Annotation\Route;

class CdeFnrSoumisAValidationApi extends Controller
{
    private $cdeFnrModel;

    public function __construct()
    {
        parent::__construct();
        $this->cdeFnrModel = new CdefnrSoumisAValidationModel();
    }

    /**
     * @Route("/api/cde/liste-fournisseur", name="api_cde_liste_fournisseur")
     */
    public function listeFournisseur()
    {
        $results = [];

        $listeFournisseur = $this->cdeFnrModel->recupListeFournissseur();

        $results = array_map(function ($fournisseur) {
            return [
                'num_fournisseur' => $fournisseur['num_fournisseur'],
                'nom_fournisseur' => $fournisseur['nom_fournisseur'],
            ];
        }, $listeFournisseur);

        header("Content-type:application/json");

        echo json_encode($results);
    }

    /**
     * @Route("/api/cde/frn-non-receptionner", name="api_cde_cde_frn_non_receptionner")
     */
    public function cdeFnrNonReceptionner()
    {
        $cdeFnrNonReceptionner = $this->cdeFnrModel->recupListeCdeFrn($this->numCdeO4());

        header("Content-type:application/json");

        echo json_encode($cdeFnrNonReceptionner);
    }



    /**
     * @Route("/api/cde/commande-fournisseur", name="api_cde_commande_fournisseur")
     */
    public function numCdeFnr()
    {
        $numCdeFnr = $this->cdeFnrModel->recupNumCdeFrn($this->numCdeO4());

        header("Content-type:application/json");

        echo json_encode($numCdeFnr);
    }

    private function numCdeO4(): string
    {
        $cde04 = $this->cdeFnrModel->findsCde04();
        return TableauEnStringService::TableauEnString(',', $cde04);
    }
}
