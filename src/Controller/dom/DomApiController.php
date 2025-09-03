<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\dom\Indemnite;
use App\Service\dom\DomValidationService;
use App\Service\dom\DomBusinessLogicService;
use App\Service\dom\DomNotificationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/dom")
 */
class DomApiController extends Controller
{
    private DomValidationService $validationService;
    private DomBusinessLogicService $businessLogicService;
    private DomNotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->validationService = new DomValidationService(
            $this->getEntityManager()
        );
        $this->businessLogicService = new DomBusinessLogicService(
            $this->getEntityManager(),
            $this->validationService
        );
        $this->notificationService = new DomNotificationService(
            $this->getSessionService(),
            $this->getTwig()
        );
    }

    /**
     * Crée une réponse JSON
     */
    private function json($data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    /**
     * Valide un matricule
     * @Route("/validate-matricule", methods={"POST"})
     */
    public function validateMatricule(Request $request): JsonResponse
    {
        $matricule = $request->get('matricule');

        if (empty($matricule)) {
            return $this->json([
                'valid' => false,
                'message' => 'Le matricule est requis'
            ], 400);
        }

        $result = $this->validationService->validateMatricule($matricule);

        return $this->json($result);
    }

    /**
     * Récupère les informations d'un employé par matricule
     * @Route("/employee-info/{matricule}", methods={"GET"})
     */
    public function getEmployeeInfo(string $matricule): JsonResponse
    {
        try {
            // Simulation de récupération des données employé
            // À adapter selon votre structure de données
            $employeeInfo = [
                'matricule' => $matricule,
                'nom' => 'Nom de l\'employé',
                'prenom' => 'Prénom de l\'employé',
                'service' => 'Service de l\'employé',
                'agence' => 'Agence de l\'employé'
            ];

            return $this->json([
                'success' => true,
                'data' => $employeeInfo
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations'
            ], 500);
        }
    }

    /**
     * Récupère les services d'une agence
     * @Route("/agence-services/{agenceId}", methods={"GET"})
     */
    public function getAgenceServices(int $agenceId): JsonResponse
    {
        try {
            $agence = $this->getEntityManager()->getRepository(Agence::class)->find($agenceId);

            if (!$agence) {
                return $this->json([
                    'success' => false,
                    'message' => 'Agence non trouvée'
                ], 404);
            }

            $services = $this->getEntityManager()->getRepository(Service::class)
                ->createQueryBuilder('s')
                ->join('s.agenceServices', 'as')
                ->where('as.agence = :agence')
                ->setParameter('agence', $agence)
                ->getQuery()
                ->getResult();

            $servicesData = [];
            foreach ($services as $service) {
                $servicesData[] = [
                    'id' => $service->getId(),
                    'code' => $service->getCodeService(),
                    'libelle' => $service->getLibelleService()
                ];
            }

            return $this->json([
                'success' => true,
                'data' => $servicesData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des services'
            ], 500);
        }
    }

    /**
     * Récupère les catégories selon le type de mission
     * @Route("/categories/{typeMissionId}", methods={"GET"})
     */
    public function getCategories(int $typeMissionId): JsonResponse
    {
        try {
            $categories = $this->getEntityManager()->getRepository(Catg::class)
                ->createQueryBuilder('c')
                ->join('c.indemnites', 'i')
                ->where('i.type = :type')
                ->setParameter('type', $typeMissionId)
                ->distinct()
                ->getQuery()
                ->getResult();

            $categoriesData = [];
            foreach ($categories as $category) {
                $categoriesData[] = [
                    'id' => $category->getId(),
                    'code' => $category->getCodeCatg(),
                    'libelle' => $category->getLibelleCatg()
                ];
            }

            return $this->json([
                'success' => true,
                'data' => $categoriesData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des catégories'
            ], 500);
        }
    }

    /**
     * Récupère les sites selon la catégorie
     * @Route("/sites/{categorieId}", methods={"GET"})
     */
    public function getSites(int $categorieId): JsonResponse
    {
        try {
            $categorie = $this->getEntityManager()->getRepository(Catg::class)->find($categorieId);

            if (!$categorie) {
                return $this->json([
                    'success' => false,
                    'message' => 'Catégorie non trouvée'
                ], 404);
            }

            $sites = $this->getEntityManager()->getRepository(Site::class)
                ->createQueryBuilder('s')
                ->join('s.indemnites', 'i')
                ->where('i.catg = :catg')
                ->setParameter('catg', $categorie)
                ->distinct()
                ->getQuery()
                ->getResult();

            $sitesData = [];
            foreach ($sites as $site) {
                $sitesData[] = [
                    'id' => $site->getId(),
                    'code' => $site->getCodeSite(),
                    'libelle' => $site->getLibelleSite()
                ];
            }

            return $this->json([
                'success' => true,
                'data' => $sitesData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des sites'
            ], 500);
        }
    }

    /**
     * Calcule les indemnités
     * @Route("/calculate-indemnities", methods={"POST"})
     */
    public function calculateIndemnities(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $typeMissionId = $data['typeMissionId'] ?? null;
            $categorieId = $data['categorieId'] ?? null;
            $siteId = $data['siteId'] ?? null;
            $agenceCode = $data['agenceCode'] ?? null;
            $nombreJours = $data['nombreJours'] ?? 1;

            if (!$typeMissionId || !$categorieId || !$siteId || !$agenceCode) {
                return $this->json([
                    'success' => false,
                    'message' => 'Paramètres manquants'
                ], 400);
            }

            $indemnite = $this->getEntityManager()->getRepository(Indemnite::class)
                ->findOneBy([
                    'type' => $typeMissionId,
                    'catg' => $categorieId,
                    'destination' => $siteId,
                    'rmq' => $agenceCode
                ]);

            if (!$indemnite) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucune indemnité trouvée pour ces critères'
                ], 404);
            }

            $montantBase = $indemnite->getMontantIdemnite();
            $totalForfaitaire = $montantBase * $nombreJours;

            return $this->json([
                'success' => true,
                'data' => [
                    'montant_base' => $montantBase,
                    'total_forfaitaire' => $totalForfaitaire,
                    'nombre_jours' => $nombreJours
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des indemnités'
            ], 500);
        }
    }

    /**
     * Vérifie les chevauchements de dates
     * @Route("/check-date-overlap", methods={"POST"})
     */
    public function checkDateOverlap(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $matricule = $data['matricule'] ?? null;
            $dateDebut = $data['dateDebut'] ?? null;
            $dateFin = $data['dateFin'] ?? null;
            $excludeDomId = $data['excludeDomId'] ?? null;

            if (!$matricule || !$dateDebut || !$dateFin) {
                return $this->json([
                    'success' => false,
                    'message' => 'Paramètres manquants'
                ], 400);
            }

            $dateDebutObj = new \DateTime($dateDebut);
            $dateFinObj = new \DateTime($dateFin);
            $excludeId = $excludeDomId ? (int)$excludeDomId : null;

            $hasOverlap = $this->validationService->checkDateOverlap(
                $matricule,
                $dateDebutObj,
                $dateFinObj,
                $excludeId
            );

            return $this->json([
                'success' => true,
                'data' => [
                    'has_overlap' => $hasOverlap,
                    'message' => $hasOverlap ?
                        'Un chevauchement de dates a été détecté' :
                        'Aucun chevauchement détecté'
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification des dates'
            ], 500);
        }
    }

    /**
     * Valide un DOM complet
     * @Route("/validate-dom", methods={"POST"})
     */
    public function validateDom(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Création d'un objet DOM temporaire pour la validation
            $dom = new Dom();
            // Remplir les propriétés du DOM avec les données reçues
            // (À adapter selon votre structure)

            $validationResult = $this->validationService->validateDom($dom);

            return $this->json([
                'success' => true,
                'data' => [
                    'is_valid' => $validationResult->isValid(),
                    'errors' => $validationResult->getErrors(),
                    'warnings' => $validationResult->getWarnings()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la validation'
            ], 500);
        }
    }

    /**
     * Génère un numéro DOM
     * @Route("/generate-number", methods={"GET"})
     */
    public function generateDomNumber(): JsonResponse
    {
        try {
            $numero = $this->businessLogicService->generateDomNumber();

            return $this->json([
                'success' => true,
                'data' => [
                    'numero' => $numero
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du numéro'
            ], 500);
        }
    }

    /**
     * Récupère les statistiques DOM
     * @Route("/statistics", methods={"GET"})
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $dateDebut = $request->query->get('dateDebut');
            $dateFin = $request->query->get('dateFin');

            if (!$dateDebut || !$dateFin) {
                $dateDebut = new \DateTime('-1 month');
                $dateFin = new \DateTime();
            } else {
                $dateDebut = new \DateTime($dateDebut);
                $dateFin = new \DateTime($dateFin);
            }

            $statistics = $this->businessLogicService->getDomStatistics($dateDebut, $dateFin);

            return $this->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }
}
