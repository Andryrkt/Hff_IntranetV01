<?php
namespace App\Controller\Traits;

use App\Model\planning\PlanningModel;

trait PlanningTraits
{
    private function orEnString($criteria): string
    {
        $numOrValide = $this->transformEnSeulTableau($criteria);

         return implode("','", $numOrValide);
    }
private function recupNumOrValider($criteria){
    $PlanningModel  = new PlanningModel();
    $resNumor = $this->orEnString($PlanningModel->recuperationNumOrValider($criteria));
    return $resNumor;
}
    
}