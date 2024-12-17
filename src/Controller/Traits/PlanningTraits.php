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
        // dump($numeroOrs);
        $numOrItvValide = $this->recupNumORItvValide($numeroOrs,$em);
        //$numOrItvValide = $this->recupNumOrValidersansVmax($em);
        $resNumor = $this->orEnString($numOrItvValide);
        return $resNumor;
    }

/*
    private function recupNumOrValidersansVmax($em)
    {
        return $em->getRepository(DitOrsSoumisAValidation::class)->findNumOrItvValide();
    }
*/
    
    private function recupNumORItvValide($numeroOrs, $em)
    {
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            // dump($numeroOr);
            // dump($numItv);
            if(!empty($numItv)){
                foreach ($numItv as  $value) {
                    $numOrValide[] = $numeroOr['numero_or'].'-'.$value;
                }
            }
        }
        return $numOrValide;
    }
/*
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
    */
}