<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class DataDitApi extends Controller
{
    /**
     * @Route("/api/data-dit", name="data_dit")
     */
    public function dataDit()
    {
        $paginationData = self::$em->getRepository(DemandeIntervention::class)->findAll();

        dd($paginationData);

        header("Content-type:application/json");

        echo json_encode($paginationData[0]);
    }
}