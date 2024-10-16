<?php

namespace App\Controller\Traits;

use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrATraiterModel;
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
        $magasinModel = new MagasinListeOrATraiterModel();
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

    private function recupNumOrTraiterSelonCondition(array $criteria, $em): array
    {
        $magasinModel = new MagasinListeOrATraiterModel();
        $numeroOrs = $magasinModel->recupNumOr($criteria);
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            if(!empty($numItv)){
                $numItvs = $magasinModel->recupNumeroItv($numeroOr['numero_or'],$this->orEnString($numItv));
                if($numItvs[0]['nbitv'] === "0"){
                    $numOrValide[] = $numeroOr;
                }
            }
        }
        
        $numOrValideString = $this->orEnString($numOrValide);
        $numOrLivrerComplet = $this->orEnString($this->magasinModel->recupOrLivrerComplet());
        $numOrLivrerIncomplet = $this->orEnString($this->magasinModel->recupOrLivrerIncomplet());
        $numOrLivrerTout = $this->orEnString($this->magasinModel->recupOrLivrerTout());

        return  [
            "numOrLivrerComplet" => $numOrLivrerComplet,
            "numOrLivrerIncomplet" => $numOrLivrerIncomplet,
            "numOrLivrerTout" => $numOrLivrerTout,
            "numOrValideString" => $numOrValideString
        ];
    }

    private function recupNumOrSelonCond(array $criteria): array
    {
        $magasinModel = new MagasinListeOrATraiterModel();
        $numOrValideString = $this->orEnString($magasinModel->recupNumOr($criteria));
        $numOrEncours = $this->orEnString($this->magasinListOrEncoursModel->recupOrEncours());

        return  [
            "numOrEncours" => $numOrEncours,
            "numOrValideString" => $numOrValideString
        ];
    }

}