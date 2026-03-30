<?php

namespace App\Controller\magasin\devis\Soumission;

use App\Controller\Controller;

class DevisNegBcController extends Controller
{

    /**
     * @Route("/soumission-bc-neg/{numeroDevis}", name="bc_neg_soumission", defaults={"numeroDevis"=null})
     */
    public function index($numeroDevis)
    {
        $codeSociette = $this->getSecurityService()->getCodeSocieteUser();

        //affichage du formulaire
        return $this->render('magasin/devis/soumission/bc.html.twig', []);
    }
}
