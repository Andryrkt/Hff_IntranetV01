<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class DataDitApi extends Controller
{
    /**
     * @Route("/api/data-dit", name="data_dit")
     */
    public function dataDit(SerializerInterface $serializer)
    {
        $paginationData = self::$em->getRepository(DemandeIntervention::class)->findAll();

        $jsonContent = $serializer->serialize($paginationData, 'json', [
            'groups' => ['intervention'], // Définissez un groupe pour la sérialisation si nécessaire
        ]);

        return new JsonResponse($jsonContent, 200, [], true);
    }
}