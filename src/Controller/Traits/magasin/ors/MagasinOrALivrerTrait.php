<?php

namespace App\Controller\Traits\magasin\ors;


use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrATraiterModel;

trait MagasinOrALIvrerTrait
{
    private function recupNumOrSelonCondition(array $criteria, $em): array
    {
        $magasinModel = new MagasinListeOrATraiterModel();
        $numeroOrs = $magasinModel->recupNumOr($criteria);

        $numOrValide = $this->recupNumORItvValide($numeroOrs, $em);

        $numOrValideString = $this->orEnString($numOrValide);
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

    // private function numeroOrValide($numeroOrs, $magasinModel, $em)
    // {
    //     $numOrValide = [];
    //     foreach ($numeroOrs as $numeroOr) {
    //         $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
    //         if(!empty($numItv)){
    //             $numItvs = $magasinModel->recupNumeroItv($numeroOr['numero_or'],$this->orEnString($numItv));
    //             if($numItvs[0]['nbitv'] === "0"){
    //                 $numOrValide[] = $numeroOr;
    //             }
    //         }
    //     }

    //     return $numOrValide;
    // }

    private function recupNumORItvValide($numeroOrs, $em)
    {
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            if(!empty($numItv)){
                foreach ($numItv as  $value) {
                    $numOrValide[] = $numeroOr['numero_or'].'-'.$value;
                }
            }
        }
        return $numOrValide;
    }
}