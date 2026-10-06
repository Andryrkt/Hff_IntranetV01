<?php

namespace App\Api\badm;

use App\Entity\admin\Agence;
use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class Form2Api extends Controller
{
    /**
     * @Route("/api/badm/service-fetch/{id}", name="api_badm_fetch_service", methods={"GET"})
     * cette fonction permet d'envoyer les donner du service destinataire et casier destiantaireselon l'agence debiteur en ajax
     * @return void
     */
    public function agenceFetch(int $id)
    {
        $agence = $this->getEntityManager()->getRepository(Agence::class)->find($id);

        $service = $agence->getServices();


        $services = [];
        foreach ($service as $value) {
            $services[] = [
                'value' => $value->getId(),
                'text' => $value->getCodeService() . ' ' . $value->getLibelleService(),
            ];
        }

        header("Content-type:application/json");

        echo json_encode($services);
    }

    /**
     * @Route("/api/badm/casier-fetch/{id}", name="api_badm_fetch_casier", methods={"GET"})
     * cette fonction permet d'envoyer les donner du service destinataire l'agence debiteur en ajax
     * @return void
     */
    public function casierFetch(int $id)
    {
        $agence = $this->getEntityManager()->getRepository(Agence::class)->find($id);

        $casier = $agence->getCasiers();

        $casiers = [];
        foreach ($casier as $value) {
            $casiers[] = [
                'value' => $value->getId(),
                'text' => $value->getCasier()
            ];
        }
        header("Content-type:application/json");

        echo json_encode($casiers);
    }
}
