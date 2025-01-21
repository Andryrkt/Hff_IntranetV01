<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitDevisSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;

class DocSoumisDwApi extends Controller
{
    /**
     * @Route("/constraint-soumission/{numDit}", name="constraint_soumission")
     *
     * @param string $numDit
     * @return void
     */
    public function constraintSoumission($numDit)
    {
        $constraitSoumission = $this->recupConstrainte($numDit);

        header("Content-type:application/json");

        echo json_encode($constraitSoumission);
    }

    private function recupConstrainte(string $numDit): array
    {
        $constraitDevis = self::$em->getRepository(DemandeIntervention::class)->recupConstraitSoumission($numDit);
        $statutDevis = self::$em->getRepository(DitDevisSoumisAValidation::class)->findStatutDevis($numDit);

        if(empty($constraitDevis)){
            $client = "";
            $statutDit = "";
        } else {
            $client = $constraitDevis[0]['client'];
            $statutDit = $constraitDevis[0]['statut'];
        }

        return  [
            "client" => $client,
            "statutDit" => $statutDit,
            "statutDevis" => $statutDevis,
        ];
    }

}