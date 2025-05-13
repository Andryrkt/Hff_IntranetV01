<?php

namespace App\Controller\Traits\ddp;

trait DdpTrait
{
    private function recuperationCdeFacEtNonFac(int $typeId): array
    {
        $numCdeDws = $this->demandePaiementModel->getNumCdeDw();
            $numCdes1 = [];
            $numCdes2 = [];
                foreach ($numCdeDws as $numCdeDw) {
                    $numfactures = $this->demandePaiementModel->cdeFacOuNonFac($numCdeDw);
                    if(!empty($numfactures)){
                        $numCdes2[] = $numCdeDw;
                    } else {
                        $numCdes1[] = $numCdeDw;
                    }
                }
            $numCdes = [];

            if($typeId == 2) {
                $numCdes = $numCdes2;
            } else {
                $numCdes = $numCdes1;
            }

            return $numCdes;
    }
}