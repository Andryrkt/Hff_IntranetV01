<?php

namespace App\Api\historisation;

use App\Controller\Controller;
use App\Entity\admin\historisation\pageConsultation\UserLogger;
use Symfony\Component\Routing\Annotation\Route;

class pageConsultationApi extends Controller
{
    /**
     * @Route("/api/consultation-page-fetch-all", name="consultation_page_fetch_all")
     *
     * @return void
     */
    public function allConsultationPage()
    {
        /** 
         * @var UserLogger[] $historiques tableau d'entité
         */
        $historiques = $this->getEntityManager()->getRepository(UserLogger::class)->findBy([], ['id' => 'DESC']);

        $results = [];
        foreach ($historiques as $historique) {
            $results[] = [
                'user'    => $historique->getUtilisateur(),
                'page'    => $historique->getNom_page(),
                'date'    => $historique->getDateConsultation()->format('d-m-Y H:i:s'),
                'params'  => $historique->getParams(),
                'machine' => $historique->getMachineUser(),
            ];
        }

        header("Content-type:application/json");

        echo json_encode($results);
    }
}
