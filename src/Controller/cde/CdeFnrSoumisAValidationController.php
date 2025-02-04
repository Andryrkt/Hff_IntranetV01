<?php

namespace App\Controller\cde;

use App\Controller\Controller;
use App\Model\cde\CdefnrSoumisAValidationModel;
use Symfony\Component\Routing\Annotation\Route;

class CdefnrSoumisAValidationController extends Controller
{
    private CdefnrSoumisAValidationModel $cdeFnrModel;

    public function __construct()
    {
        parent::__construct();
        $this->cdeFnrModel = new CdefnrSoumisAValidationModel();
    }


    /**
     * @Route("/cde-fournisseur", name="cde_fournisseur")
     */
    public function cdeFournisseur ()
    {
        $fournisseurs = $this->cdeFnrModel->recupListeFournissseur();
        self::$twig->display('cde/cdeFnr.html.twig', [
            'fournisseurs' => $fournisseurs
        ]);
    }
}