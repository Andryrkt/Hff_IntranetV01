<?php

namespace App\Controller\Traits\magasin\ors;


use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrATraiterModel;

trait MagasinOrALIvrerTrait
{
    private function recupNumOrSelonCondition(array $criteria): array
    {
        $magasinModel = new MagasinListeOrATraiterModel();
        /** @var array $numeroOrsItv @var array $numeroOr */
        [$numeroOrsItv, $numeroOr] = $magasinModel->recupNumOr($criteria);

        // $numOrLivrerComplet = $this->orEnString($this->magasinListOrLivrerModel->recupOrLivrerComplet($numOrValideItvString, $criteria));
        // $numOrLivrerIncomplet = $this->orEnString($this->magasinListOrLivrerModel->recupOrLivrerIncomplet($numOrValideItvString, $criteria));
        // $numOrLivrerTout = $this->orEnString($this->magasinListOrLivrerModel->recupOrLivrerTout($numOrValideItvString, $criteria));

        // return  [
        //     "numOrLivrerComplet" => $numOrLivrerComplet,
        //     "numOrLivrerIncomplet" => $numOrLivrerIncomplet,
        //     "numOrLivrerTout" => $numOrLivrerTout,
        //     "numOrValideString" => $numOrValideItvString
        // ];

        return [
            $this->orEnString($numeroOrsItv),
            $this->orEnString($numeroOr)
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

    // private function recupNumORItvValide($numeroOrs, $em)
    // {
    //     $numOrValideItv = [];
    //     $numOrValide = [];
    //     foreach ($numeroOrs as $numeroOr) {
    //         $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);

    //         if(!empty($numItv)){
    //             foreach ($numItv as  $value) {
    //                 $numOrValideItv[] = $numeroOr['numero_or'].'-'.$value;
    //                 $numOrValide[] = $numeroOr['numero_or'];
    //             }
    //         }
    //     }
    //     return [
    //         'numeroOr_itv' => $numOrValideItv,
    //         'numeroOr' => $numOrValide
    //     ];
    // }
}
