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
        $ditListeModel = new DitListModel();
        $facture = self::$em->getRepository(DitFactureSoumisAValidation::class)->findNumItvFacStatut($numOr);
        $itv = $ditListeModel->recupItv($numOr);

        $result = [];
        foreach ($itv as $value) {
            $found = false;
                foreach ($facture as $item) {
                    if ($item['numeroItv'] == $value) {
                        $result[] = $item;
                        $found = true;
                        break;
                    }
                }
            
            
            if (!$found) {
                $result[] = [
                    "numeroItv" => $value,
                    "numeroFact" => "-",
                    "statut" => "-"
                ];
            }
        }

        
        header("Content-type:application/json");
        echo json_encode($result);
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
