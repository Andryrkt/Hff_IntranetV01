<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DaAfficherRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/demande-appro")
 */
class ActionSurNonDispoController extends Controller
{
    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;

    public function __construct()
    {
        parent::__construct();

        $em                             = $this->getEntityManager();
        $this->daAfficherRepository     = $em->getRepository(DaAfficher::class);
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
    }

    /**
     * @Route("/da-list-cde-frn/delete-articles", name="api_list_cde_frn_delete_articles", methods={"POST"})
     */
    public function deleteArticles(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $daAfficherIds = $data['ids'] ?? [];
        $lines = $data['lines'] ?? [];
        $numDa = $data['numDa'] ?? "";

        if (!$daAfficherIds || !$lines || !$numDa) {
            return new JsonResponse([
                'status'  => 'error',
                'title'   => 'Erreur lors de la suppression',
                'message' => 'Impossible de supprimer. Merci de vérifier les informations et de réessayer.',
            ], 400);
        }

        try {
            $connectedUserName = $this->getUserName();

            $this->daAfficherRepository->markAsDeletedByListId($daAfficherIds, $connectedUserName);
            $this->demandeApproLRepository->deleteByNumDaAndLineNumbers($numDa, $lines);
            $this->demandeApproLRRepository->deleteByNumDaAndLineNumbers($numDa, $lines);

            $count = count($daAfficherIds);
            $label = $count > 1 ? 'articles supprimés' : 'article supprimé';

            return new JsonResponse([
                'status'  => 'success',
                'title'   => 'Action effectuée',
                'message' => "$count $label avec succès.",
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'status'  => 'error',
                'title'   => 'Erreur lors de la suppression',
                'message' => 'Impossible de supprimer certains articles. Merci de réessayer plus tard.<br> Message d\'erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @Route("/da-list-cde-frn/create-new-articles", name="api_list_cde_frn_create_new_articles", methods={"POST"})
     */
    public function createNewDa(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $daAfficherIds = $data['ids'] ?? [];

        if (!$daAfficherIds) {
            return new JsonResponse([
                'status'  => 'error',
                'title'   => 'Erreur lors de la création',
                'message' => 'Impossible de créer de nouveaux articles. Merci de vérifier les informations et de réessayer.',
            ], 400);
        }

        try {
            /** @var DaAfficher[] $daAffichers tableau d'objets DaAfficher correpondant aux ID dans daAfficherIds */
            $daAffichers = $this->daAfficherRepository->findBy(['id' => $daAfficherIds]); // objets DaAfficher correpondant aux ID dans daAfficherIds
            $daAffichersTab = [];
            foreach ($daAffichers as $daAfficher) {
                $daAffichersTab[] = [
                    'id' => $daAfficher->getId(),
                    'numda' => $daAfficher->getNumeroDemandeAppro(),
                    'numligne' => $daAfficher->getNumeroLigne(),
                    'statutdal' => $daAfficher->getStatutDal(),
                    'statutcde' => $daAfficher->getStatutCde(),
                    'artdesi' => $daAfficher->getArtDesi(),
                ];
            }

            return new JsonResponse([
                'daAfficher' => $daAffichersTab,
            ]);

            $count = count($daAfficherIds);
            $label = $count > 1 ? 'articles ajoutés' : 'article ajouté';

            return new JsonResponse([
                'status'  => 'success',
                'title'   => 'Action effectuée',
                'message' => "$count $label avec succès.",
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'status'  => 'error',
                'title'   => 'Erreur lors de la création',
                'message' => 'Impossible de créer certains articles. Merci de réessayer plus tard.<br> Message d\'erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
}
