<?php

namespace App\Controller\Traits;

use App\Model\magasin\MagasinModel;

trait MagasinTrait
{
    private function orEnString($tab): string
    {
        $numOrValide = $this->transformEnSeulTableau($tab);

         return implode("','", $numOrValide);
    }

    private function firstDateOfWeek()
    {
        $today = new \DateTime();
        $dayOfWeek = $today->format('N');
        $daysToMonday = $dayOfWeek - 1;
        return $today->modify("-$daysToMonday days");
    }

    private function recupNumOrSelonCondition(array $criteria): array
    {
        $magasinModel = new MagasinModel();
        $numOrValideString = $this->orEnString($magasinModel->recupNumOr($criteria));
        $numOrLivrerComplet = $this->orEnString($this->magasinListOrLivrerModel->recupOrLivrerComplet());
        $numOrLivrerIncomplet = $this->orEnString($this->magasinListOrLivrerModel->recupOrLivrerIncomplet());
        $numOrLivrerTout = $this->orEnString($this->magasinListOrLivrerModel->recupOrLivrerTout());

       return  [
            "numOrLivrerComplet" => $numOrLivrerComplet,
            "numOrLivrerIncomplet" => $numOrLivrerIncomplet,
            "numOrLivrerTout" => $numOrLivrerTout,
            "numOrValideString" => $numOrValideString
        ];
    }
}