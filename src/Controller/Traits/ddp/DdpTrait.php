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
            if (!empty($numfactures)) {
                $numCdes2[] = $numCdeDw;
            } else {
                $numCdes1[] = $numCdeDw;
            }
        }
        $numCdes = [];

        if ($typeId == 2) {
            $numCdes = $numCdes2;
        } else {
            $numCdes = $numCdes1;
        }
        return $numCdes;
    }

    private function recupCdeDw($data, $numDdp, $numVersion): array
    {
        $pathAndCdes = [];
        foreach ($data->getNumeroCommande() as  $numcde) {
            $pathAndCdes[] = $this->demandePaiementModel->getPathDwCommande($numcde);
        }

        $nomDufichierCde = [];
        foreach ($pathAndCdes as  $pathAndCde) {
            if ($pathAndCde[0]['path'] != null) {
                $cheminDufichierInitial = $_ENV['BASE_PATH_FICHIER'] . "/" . $pathAndCde[0]['path'];

                if (!file_exists($cheminDufichierInitial)) {
                    // Le fichier n'existe pas, on passe au suivant
                    continue;
                }

                $nomFichierInitial = basename($pathAndCde[0]['path']);

                $cheminDufichierDestinataire = $this->cheminDeBase . '/' . $numDdp . '_New_' . $numVersion . '/' . $nomFichierInitial;
                
                $destinationDir = dirname($cheminDufichierDestinataire);
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0777, true);
                }

                if (copy($cheminDufichierInitial, $cheminDufichierDestinataire)) {
                    $nomDufichierCde[] =  $nomFichierInitial;
                }
            }
        }

        return $nomDufichierCde;
    }
}
