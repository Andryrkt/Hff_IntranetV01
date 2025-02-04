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
     * @Route("api/cde-fnr-non-receptionner/{numFournisseur}", name="api-cdeèfnr-non-receptionner")
     */
    public function cdeFnrNonReceptionner($numFournisseur)
    {
        $cdeFnrNonReceptionner = $this->cdeFnrModel->recupCdeFnrNonReceptionner($numFournisseur);

        header("Content-type:application/json");

        echo json_encode($cdeFnrNonReceptionner);
    }
}