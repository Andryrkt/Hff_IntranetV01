<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/demande-appro")
 */
class ActionSurNonDispoController extends Controller
{
    /**
     * @Route("/da-list-cde-frn/delete-articles", name="api_list_cde_frn_delete_articles", methods={"POST"})
     */
    public function deleteArticles(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $articles = $data['articles'] ?? [];

        $articlesString = implode(', ', $articles);

        // Ici tu fais la suppression de tes articles
        // Exemple : $this->getDoctrine()->getRepository(Article::class)->deleteSelected($articles);

        return new JsonResponse([
            'status'  => 'success',
            'message' => count($articles) . ' article(s) supprimé(s) avec succès: Articles supprimées: ' . $articlesString
        ]);
    }
}
