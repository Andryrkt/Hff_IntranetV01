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
        $itvNumFac = $ditListeModel->recupItvNumFac($numOr);

        $result = [];
        foreach ($itvNumFac as $value) {
            $found = false;
                foreach ($facture as $item) {
                    if ($item['numeroItv'] == $value['itv']) {
                        $result[] = $item;
                        $found = true;
                        break;
                    }
                }
            
            
            if (!$found) {
                $result[] = [
                    "numeroItv" => $value['itv'],
                    "numeroFact" => $value['numerofac'] ? $value['numerofac'] : "-",
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
