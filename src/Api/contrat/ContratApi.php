<?php

namespace App\Api\contrat;

use App\Controller\Controller;
use App\Entity\admin\Agence;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * API pour le module Contrat
 */
class ContratApi extends Controller
{
    /**
     * Récupère les services d'une agence par son code texte
     *
     * @Route("/contrat-api/agence-fetch/{code}", name="contrat_agence_fetch", methods={"GET"})
     *
     * @param string $code Code court de l'agence (ex: '01', '02', '30')
     * @return JsonResponse
     */
    public function getServicesByAgenceCode(string $code): JsonResponse
    {
        try {
            // Chercher l'agence par son code court
            $agence = $this->getEntityManager()
                ->getRepository(Agence::class)
                ->findOneBy(['codeAgence' => $code]);

            if (!$agence) {
                return new JsonResponse(['error' => 'Agence not found'], Response::HTTP_NOT_FOUND);
            }

            $services = $agence->getServices();

            $servicesData = [];
            foreach ($services as $service) {
                $servicesData[] = [
                    'value' => $service->getCodeService(),
                    'text' => $service->getCodeService() . ' - ' . $service->getLibelleService()
                ];
            }

            return new JsonResponse($servicesData);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Récupère toutes les références de contrats uniques
     *
     * @Route("/contrat-api/references", name="contrat_api_references", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function getReferences(): JsonResponse
    {
        try {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT DISTINCT c.reference, c.objet
                    FROM contrat c
                    WHERE c.reference IS NOT NULL AND c.reference != ''
                    ORDER BY c.reference ASC";

            $results = $conn->fetchAllAssociative($sql);

            return new JsonResponse($results);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Récupère tous les partenaires uniques
     *
     * @Route("/contrat-api/partenaires", name="contrat_api_partenaires", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function getPartenaires(): JsonResponse
    {
        try {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT DISTINCT c.nom_partenaire as nom
                    FROM contrat c
                    WHERE c.nom_partenaire IS NOT NULL AND c.nom_partenaire != ''
                    ORDER BY c.nom_partenaire ASC";

            $results = $conn->fetchAllAssociative($sql);

            return new JsonResponse($results);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
