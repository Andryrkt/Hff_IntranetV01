<?php

namespace App\Api\mutation;

use App\Controller\Controller;
use App\Entity\admin\Personnel;
use App\Entity\mutation\Mutation;
use Symfony\Component\Routing\Annotation\Route;

class MutationApi extends Controller
{
    /**
     * @Route("/personnel-fetch-id/{personnelId}", name="fetch_personnel_id", methods={"GET"})
     *
     * @param [type] $personnelId
     * @return void
     */
    public function personnelFetchId($personnelId)
    {
        $personne = self::$em->getRepository(Personnel::class)->find($personnelId);
        $matricule = $personne->getMatricule();
        $numTel = self::$em->getRepository(Mutation::class)->findLastNumtel((string)$matricule);
        $tab = [
            'compteBancaire' => $personne->getNumeroCompteBancaire(),
            'telephone' => $numTel
        ];

        header("Content-type:application/json");

        echo json_encode($tab);
    }
}
