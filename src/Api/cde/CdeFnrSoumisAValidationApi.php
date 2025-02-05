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
     * @Route("api/cde-fnr-non-receptionner/{numFournisseur}", name="api-cdeèfnr-non-receptionner")
     */
    public function cdeFnrNonReceptionner($numFournisseur)
    {
        $cdeFnrNonReceptionner = $this->cdeFnrModel->recupListeInitialCdeFrn($numFournisseur);

        header("Content-type:application/json");

        echo json_encode($cdeFnrNonReceptionner);
    }
}