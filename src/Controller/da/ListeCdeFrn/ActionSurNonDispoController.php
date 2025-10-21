<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/demande-appro")
 */
class ActionSurNonDispoController extends Controller
{
    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;

    public function __construct()
    {
        parent::__construct();

        $em                             = $this->getEntityManager();
        $this->daAfficherRepository     = $em->getRepository(DaAfficher::class);
        $this->demandeApproRepository   = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
    }

    /**
     * @Route("/da-list-cde-frn/delete-articles", name="api_list_cde_frn_delete_articles", methods={"POST"})
     */
    public function deleteArticles(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $articles = $data['articles'] ?? [];

        // Ici tu fais la suppression de tes articles
        // Exemple : $this->getDoctrine()->getRepository(Article::class)->deleteSelected($articles);

        return new JsonResponse([
            'status'  => 'success',
            'message' => count($articles) . ' article(s) supprimé(s) avec succès.'
        ]);
    }

    /**
     * @Route("/da-list-cde-frn/create-new-articles", name="api_list_cde_frn_create_new_articles", methods={"POST"})
     */
    public function createNewDa(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $articles = $data['articles'] ?? [];

        // Ici tu fais la suppression de tes articles
        // Exemple : $this->getDoctrine()->getRepository(Article::class)->deleteSelected($articles);

        return new JsonResponse([
            'status'  => 'success',
            'message' => count($articles) . ' article(s) ajouté(s) avec succès.'
        ]);
    }
}
