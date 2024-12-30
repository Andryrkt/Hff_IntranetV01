<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Routing\Annotation\Route;

class DeviApi extends Controller
{
    /**
     * @Route("/constraint-soumission/{numDit}", name="constraint_soumission")
     *
     * @param string $numDit
     * @return void
     */
    public function constraintSoumission($numDit)
    {
        $constraitSoumission = self::$em->getRepository(DemandeIntervention::class)->recupConstraitSoumission($numDit);
        
        header("Content-type:application/json");

        echo json_encode($constraitSoumission);
    }

}