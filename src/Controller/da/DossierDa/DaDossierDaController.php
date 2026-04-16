<?php

namespace App\Controller\da\DossierDa;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaDossierDaController extends Controller
{
    /**
     * @Route("/dossier-da/{numDa}", name="da_dossier_da")
     */
    public function dossierDa($numDa)
    {
        return $this->render("da/dossier-da.html.twig", [
            'numDa' => $numDa,
        ]);
    }
}
