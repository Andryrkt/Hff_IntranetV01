<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Entity\dit\DitFactureSoumisAValidation;
use App\Entity\dit\DitRiSoumisAValidation;
use App\Model\dit\DitListModel;
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
    
    /** 
     * RECUPERATION numero intervention, numero facture et statut du facture
     * @Route("/ri-fetch/{numOr}", name="ri_fetch") 
     * */
    public function ri($numOr)
    {
        $ditListeModel = new DitListModel();
        $ri = $ditListeModel->recupItvComment($numOr);
        $riSoumis = self::$em->getRepository(DitRiSoumisAValidation::class)->findNumItv($numOr);
        
        foreach ($ri as &$value) {
            $estRiSoumis = in_array($value['numeroitv'], $riSoumis);
            $value['riSoumis'] = $estRiSoumis;
        }
        unset($value);// Libère la référence


        header("Content-type:application/json");
        echo json_encode($ri);
    }
}
