<?php

namespace App\Api\magasin\cdeFrn;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\magasin\cdeFrn\CdeFrnSoumissionService;

class GenerateCommandeFournisseurApi extends Controller
{
    private CdeFrnSoumissionService $cdeFrnSoumissionService;

    public function __construct(CdeFrnSoumissionService $cdeFrnSoumissionService)
    {
        $this->cdeFrnSoumissionService = $cdeFrnSoumissionService;
    }

    /**
     * @Route("/api/cde-frn/{numCde}/generate-pdf", name="api_generate_cde_frn", methods={"GET"})
     */
    public function generatePdfCmdeFournisseur(string $numCde): JsonResponse
    {
        $cdeFrnSoumisAValidationDTO = null;

        // 1. Validation basique de l'input
        if (empty($numCde) || !preg_match('/^\d{7,8}$/', $numCde)) {
            return new JsonResponse([
                'data'    => $cdeFrnSoumisAValidationDTO,
                'message' => 'Numéro de document invalide.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $cdeFrnSoumisAValidationDTO = $this->cdeFrnSoumissionService->generatePdfForSubmission($numCde, $this->getUserMail(), $this->getSecurityService()->getCodeSocieteUser());

            if ($cdeFrnSoumisAValidationDTO === null) {
                return new JsonResponse([
                    'data'    => $cdeFrnSoumisAValidationDTO,
                    'message' => "<span class='text-danger'>Aucune information trouvée pour la commande \"<span class='text-decoration-underline fw-bold'>$numCde</span>\".</span>"
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse([
                'data'    => $cdeFrnSoumisAValidationDTO,
                'message' => "PDF généré avec succès."
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'data'    => $cdeFrnSoumisAValidationDTO,
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
