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

     private function recupCdeDw($data,$numDdp,$numVersion):array
    {
        $pathAndCdes = [];
        foreach ($data->getNumeroCommande() as  $numcde) {
            $pathAndCdes[] = $this->demandePaiementModel->getPathDwCommande($numcde);
        }

        $nomDufichierCde = [];
        foreach ($pathAndCdes as  $pathAndCde) {

            $cheminDufichierInitial = $_ENV['BASE_PATH_FICHIER'] . "/" . $pathAndCde[0]['path'];
            $nomFichierInitial = explode("/", $pathAndCde[0]['path'])[2];

            $cheminDufichierDestinataire = $this->cheminDeBase . '/' . $numDdp . '_New_'.$numVersion.'/' . $nomFichierInitial;
            copy($cheminDufichierInitial, $cheminDufichierDestinataire);
            $nomDufichierCde[] =  $nomFichierInitial;
        }
        return $nomDufichierCde;
    }
}