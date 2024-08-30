<?php

namespace App\Controller\Traits;

use App\Model\magasin\MagasinModel;

trait MagasinTrait
{
    private function orEnString($criteria): string
    {
        $magasinModel = new MagasinModel();
        $numOrValide = $this->transformEnSeulTableau($magasinModel->recupNumOr($criteria));

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