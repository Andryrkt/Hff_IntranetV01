<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Entity\dit\DitFactureSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;

class ListApi extends Controller
{
    /** 
     * RECUPERATION numero intervention, numero facture et statut du facture
     * @Route("/facturation-fetch/{numOr}", name="facturation_fetch") 
     * */
    public function facturation($numOr)
    {
        $facture = self::$em->getRepository(DitFactureSoumisAValidation::class)->findNumItvFacStatut($numOr);

        header("Content-type:application/json");
        echo json_encode($facture);
    }
        
}
