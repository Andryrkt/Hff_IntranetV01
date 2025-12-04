<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use App\Entity\da\DaArticleReappro;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/demande-appro")
 */
class DaNewDirectApiController extends Controller
{
    private const ERROR_MESSAGES = [
        'codeAgenceServiceManquant' => 'codeAgence ou codeService manquant',
        'codeAgenceIncorrect'       => 'codeAgence doit contenir exactement 2 caractères',
        'codeServiceIncorrect'      => 'codeService doit contenir exactement 3 caractères',
    ];

    /**
     * @Route("/da-new-direct/{codeAgence}/{codeService}", name="api_da_new_direct", methods={"GET"})
     */
    public function listeArticle(string $codeAgence, string $codeService)
    {
        if (!$codeAgence || !$codeService) return $this->errorMessage(self::ERROR_MESSAGES['codeAgenceServiceManquant']);
        if (strlen($codeAgence) !== 2) return $this->errorMessage(self::ERROR_MESSAGES['codeAgenceIncorrect']);
        if (strlen($codeService) !== 3) return $this->errorMessage(self::ERROR_MESSAGES['codeServiceIncorrect']);

        try {
            $daArticleReapproRepository = $this->getEntityManager()->getRepository(DaArticleReappro::class);
            $articlesReappro = $daArticleReapproRepository->getArticlesList($codeAgence, $codeService);

            return new JsonResponse([
                'status'  => 'success',
                'title'   => 'Succès de l\'opération',
                'message' => "La récupération des articles est un succès.",
                'data'    => array_combine($articlesReappro, $articlesReappro),
            ]);
        } catch (\Exception $e) {
            return $this->errorMessage($e->getMessage());
        }
    }

    private function errorMessage(string $errorMessage): JsonResponse
    {
        return new JsonResponse([
            'status'  => 'error',
            'title'   => 'Erreur lors de la récupération des articles',
            'message' => "Impossible de récupérer les articles: $errorMessage. Merci de vérifier les informations et de réessayer.",
        ], 400);
    }
}
