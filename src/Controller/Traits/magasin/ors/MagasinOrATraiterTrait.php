<?php

namespace App\Controller\Traits\magasin\ors;


use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrATraiterModel;

trait MagasinOrATraiterTrait
{
    private function recupNumOrTraiterSelonCondition(array $criteria, $magasinModel, $em): array
    {
        $numeroOrs = $magasinModel->recupNumOr($criteria);
        
        $numOrValide = $this->numeroOrValide($numeroOrs, $magasinModel, $em);
        
        $numOrValideString = $this->orEnString($numOrValide);
        $numOrLivrerComplet = $this->orEnString($magasinModel->recupOrLivrerComplet());
        $numOrLivrerIncomplet = $this->orEnString($magasinModel->recupOrLivrerIncomplet());
        $numOrLivrerTout = $this->orEnString($magasinModel->recupOrLivrerTout());
        return  [
            "numOrLivrerComplet" => $numOrLivrerComplet,
            "numOrLivrerIncomplet" => $numOrLivrerIncomplet,
            "numOrLivrerTout" => $numOrLivrerTout,
            "numOrValideString" => $numOrValideString
        ];
    }

    private function numeroOrValide($numeroOrs, $magasinModel, $em)
    {
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

        return $numOrValide;
    }
}