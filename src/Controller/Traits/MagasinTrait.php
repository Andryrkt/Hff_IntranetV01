<?php

namespace App\Controller\Traits;

trait MagasinTrait
{
    private function orEnString($criteria): string
    {
        $numOrValide = $this->transformEnSeulTableau($this->magasinModel->recupNumOr($criteria));

         return implode(',', $numOrValide);
    }
}