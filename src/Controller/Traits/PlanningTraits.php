<?php
namespace App\Controller\Traits;

use App\Model\planning\PlanningModel;
use App\Entity\dit\DitOrsSoumisAValidation;

trait PlanningTraits
{
    private function orEnString($criteria): string
    {
        $numOrValide = $this->transformEnSeulTableau($criteria);

         return implode("','", $numOrValide);
    }

    private function recupNumOrValider($criteria, $em){
        $PlanningModel  = new PlanningModel();
        $numeroOrs = $PlanningModel->recuperationNumOrValider($criteria);
        $numOrValide = $this->numeroOrValide($numeroOrs, $PlanningModel, $em);
        $resNumor = $this->orEnString($numOrValide);
        return $resNumor;
    }

    private function numeroOrValide($numeroOrs, $PlanningModel, $em)
    {
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            if(!empty($numItv)){
                $numItvs = $PlanningModel->recupNumeroItv($numeroOr['numero_or'],$this->orEnString($numItv));
                if($numItvs[0]['nbitv'] === "0"){
                    $numOrValide[] = $numeroOr;
                }
            }
        }

        return $numOrValide;
    }
    
}