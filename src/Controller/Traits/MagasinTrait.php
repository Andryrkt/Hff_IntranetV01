<?php

namespace App\Controller\Traits;

trait MagasinTrait
{
    private function orEnString($criteria): string
    {
        $numOrValide = $this->transformEnSeulTableau($this->magasinModel->recupNumOr($criteria));

         return implode(',', $numOrValide);
    }

    private function firstDateOfWeek()
    {
        $today = new \DateTime();
        $dayOfWeek = $today->format('N');
        $daysToMonday = $dayOfWeek - 1;
        return $today->modify("-$daysToMonday days");
    }
}