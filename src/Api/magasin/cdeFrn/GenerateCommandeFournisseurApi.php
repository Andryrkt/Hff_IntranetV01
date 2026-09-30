<?php

namespace App\Api\magasin\cdeFrn;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Model\magasin\cdeFrn\soumission\CdeSoumissionModel;
use App\Service\genererPdf\magasin\cdeFrn\GeneratePdfCdeMagasin;

class GenerateCommandeFournisseurApi extends Controller
{
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
            // 2. Récupération des données du document
            $commandeSoumissionDto = (new CdeSoumissionModel())->findInfoCommande($numCde, $this->getUserMail(), $this->getSecurityService()->getCodeSocieteUser());

            if ($commandeSoumissionDto === null) {
                return new JsonResponse([
                    'data'    => $cdeFrnSoumisAValidationDTO,
                    'message' => "<span class='text-danger'>Aucune information trouvée pour la commande \"<span class='text-decoration-underline fw-bold'>$numCde</span>\".</span>"
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            $basePath = rtrim($_ENV['BASE_PATH_FICHIER'], '/\\');
            $filePath = "magasin/commandes fournisseurs/$numCde/$numCde.pdf";
            $dirPath  = dirname("$basePath/$filePath");

            if (!is_dir($dirPath)) mkdir($dirPath, 0777, true);

            // 3. Suppression du fichier PDF s'il existe déjà: Forcer la regéneration
            if (file_exists($filePath)) unlink($filePath);

            // 4. Génération du PDF
            (new GeneratePdfCdeMagasin())->generate($commandeSoumissionDto, "$basePath/$filePath");

            return new JsonResponse([
                'data'    => rtrim($_ENV['BASE_PATH_FICHIER_COURT'], '/\\') . "/$filePath",
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
