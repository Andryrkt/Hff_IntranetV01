<?php

namespace App\Api\magasin;

use App\Controller\Controller;
use App\Model\magasin\MagasinModel;
use Symfony\Component\Routing\Annotation\Route;

class agenceServiceInformixApi extends Controller
{
    /** 
     * RECUPERATION SERVICE INFORMIX
     * @Route("/service-informix-fetch/{agence}", name="service_informix_fetch") 
     * */
    public function agenceInformix($agence)
    {
        $magasinModel = new MagasinModel();
        $service = $magasinModel->service($agence);

        header("Content-type:application/json");

        echo json_encode($service);
    }
}