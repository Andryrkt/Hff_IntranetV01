<?php
namespace App\Controller\Traits;
trait PlanningTraits
{
    private function orEnString($criteria): string
    {
        $numOrValide = $this->transformEnSeulTableau($criteria);

         return implode("','", $numOrValide);
    }
}