<?php

namespace App\Controller\cde;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class CdefnrSoumisAValidationController extends Controller
{
    /**
     * @Route("/cde-fournisseur", name="cde_fournisseur")
     */
    public function cdeFournisseur ()
    {
        self::$twig->display('cde/cdeFnr.html.twig', []);
    }
}