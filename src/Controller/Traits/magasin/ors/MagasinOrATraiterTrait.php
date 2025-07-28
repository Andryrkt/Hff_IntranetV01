<?php

namespace App\Controller\Traits\magasin\ors;

use App\Entity\dit\DitOrsSoumisAValidation;

trait MagasinOrATraiterTrait
{
    private function recupNumOrTraiterSelonCondition(array $criteria, $magasinModel, $em): array
    {
        $numeroOrs = $magasinModel->recupNumOr($criteria);

        $numOrValideItv = $this->recupNumORItvValide($numeroOrs, $em)['numeroOr_itv'];

        $numOrValideString = $this->orEnString($numOrValideItv);

        return  [
            "numOrValideString" => $numOrValideString,
        ];
    }
    /*
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
            */

    private function recupNumORItvValide($numeroOrs, $em)
    {
        $numOrValideItv = [];
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            if (! empty($numItv)) {
                foreach ($numItv as $value) {
                    $numOrValideItv[] = $numeroOr['numero_or'].'-'.$value;
                    $numOrValide[] = $numeroOr['numero_or'];
                }
            }
        }

        return [
            'numeroOr_itv' => $numOrValideItv,
            'numeroOr' => $numOrValide,
        ];
    }
}
