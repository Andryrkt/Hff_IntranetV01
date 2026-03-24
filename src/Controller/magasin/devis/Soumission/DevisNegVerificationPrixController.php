<?php

namespace App\Controller\magasin\devis\Soumission;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisNegVerificationPrixController extends Controller
{
    /**
     * @Route("/soumission-devis-neg-verification-de-prix/{numeroDevis}", name="devis_neg_soumission_verification_prix", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $numeroDevis = null, Request $request) {}
}
