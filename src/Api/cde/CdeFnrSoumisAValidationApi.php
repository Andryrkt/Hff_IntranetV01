<?php

namespace App\Api\cde;

use App\Controller\Controller;
use App\Model\cde\CdefnrSoumisAValidationModel;
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
     * @Route("api/liste-fournisseur", name="api-liste-fournisseur")
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
     * @Route("api/cde-fnr-non-receptionner", name="api-cdefnr-non-receptionner")
     */
    public function cdeFnrNonReceptionner()
    {
        $cdeFnrNonReceptionner = $this->cdeFnrModel->recupListeCdeFrn();

        header("Content-type:application/json");

        echo json_encode($cdeFnrNonReceptionner);
    }

    /**
     * @Route("api/num-cde-fnr", name="api_num_cde_frn")
     */
    public function numCdeFnr()
    {
        $numCdeFnr = $this->cdeFnrModel->recupNumCdeFrn();

        header("Content-type:application/json");

        echo json_encode($numCdeFnr);
    }

    /**
     * @Route("api/num-cde-04", name="api_num_cde_04")
     */
    public function numCde()
    {
        $cde04 = $this->cdeFnrModel->findsCde04();

        header("Content-type:application/json");

        echo json_encode($cde04);
    }

    
}