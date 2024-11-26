<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DitCdeSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitCdeSoumisAValidationController extends Controller
{
    /**
     * @Route("/insertion-cde/{numDit}", name="dit_insertion_cde")
     */
    public function cdeSoumisAValidation(Request $request, $numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $ditCdeSoumisAValidation = new DitCdeSoumisAValidation();
    }
}