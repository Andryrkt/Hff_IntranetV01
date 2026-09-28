<?php

namespace App\Api\magasin\cdeFrn;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\genererPdf\magasin\GeneratePdfCdeMagasin;
use App\Model\magasin\CommANDe\Soumission\CdeSoumissionModel;

class GenerateCommandeFournisseurApi extends Controller
{
    /**
     * @Route("/api/cmde-fournisseur/{numCde}/generate-pdf", name="api_generate_cmde_fournisseur", methods={"GET"})
     */
    public function generatePdfCmdeFournisseur(string $numCde): JsonResponse
    {
        // 1. Validation basique de l'input
        if (empty($numCde) || !preg_match('/^\d{7,8}$/', $numCde)) {
            return new JsonResponse([
                'success' => false,
                'error'   => 'Numéro de document invalide.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            // 2. Récupération des données du document
            $commandeSoumissionDto = (new CdeSoumissionModel())->findInfoCommande($numCde, $this->getUserMail(), $_ENV["SUC_NEG"], $this->getSecurityService()->getCodeSocieteUser());

            if ($commandeSoumissionDto === null) {
                return new JsonResponse([
                    'url'     => null,
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
                'url'     => rtrim($_ENV['BASE_PATH_FICHIER_COURT'], '/\\') . "/$filePath",
                'message' => "PDF généré avec succès."
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'url'     => null,
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
