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
    
        $numOrValideItvString = $this->orEnString($this->recupNumORItvValide($numeroOrs, $em)['numeroOr_itv']);
        $numOrValideString = $this->orEnString($this->recupNumORItvValide($numeroOrs, $em)['numeroOr']);
    
        $numOrLivrerComplet = $this->orEnString($this->magasinListOrLivrerModel->recupOrLivrerComplet($numOrValideItvString, $numOrValideString, $criteria));
        $numOrLivrerIncomplet = $this->orEnString($this->magasinListOrLivrerModel->recupOrLivrerIncomplet($numOrValideItvString, $numOrValideString, $criteria));
        $numOrLivrerTout = $this->orEnString($this->magasinListOrLivrerModel->recupOrLivrerTout($numOrValideItvString, $numOrValideString, $criteria));

        return  [
            "numOrLivrerComplet" => $numOrLivrerComplet,
            "numOrLivrerIncomplet" => $numOrLivrerIncomplet,
            "numOrLivrerTout" => $numOrLivrerTout,
            "numOrValideString" => $numOrValideItvString
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
        $numOrValideItv = [];
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            
            if(!empty($numItv)){
                foreach ($numItv as  $value) {
                    $numOrValideItv[] = $numeroOr['numero_or'].'-'.$value;
                    $numOrValide[] = $numeroOr['numero_or'];
                }
            }
        }
        return [
            'numeroOr_itv' => $numOrValideItv,
            'numeroOr' => $numOrValide
        ];
    }
}
