<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use App\Entity\admin\historisation\documentOperation\HistoriqueOperationEtape;
use App\Service\historiqueOperation\suivi\SuiviOperationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Suivi étape par étape des opérations (accessible uniquement aux administrateurs HFF-ADMIN)
 */
class SuiviOperationController extends Controller
{
    private const LIMITES_POSSIBLES = [25, 50, 100, 200, 500];

    /**
     * @Route("/admin/suivi-operation", name="suivi_operation_index")
     */
    public function index(Request $request)
    {
        $this->verifierAcces();

        $recherche         = $request->query->get('recherche');
        $typeOperation    = $request->query->get('type');
        $problemeSeulement = $request->query->getBoolean('probleme');
        $nonLuSeulement    = $request->query->getBoolean('non_lu');
        $idHistorique      = $request->query->getInt('historique') ?: null;
        $dateDebut         = $this->creerDate($request->query->get('date_debut'));
        $dateFin           = $this->creerDate($request->query->get('date_fin'));
        // valeurs imposées : la limite est injectée dans le TOP de la requête des modifications en base
        $limite            = in_array($request->query->getInt('limite'), self::LIMITES_POSSIBLES, true) ? $request->query->getInt('limite') : 25;

        $repository = $this->getEntityManager()->getRepository(HistoriqueOperationEtape::class);

        return $this->render('admin/historisation/suivi-operation/index.html.twig', [
            'suivis'            => $repository->rechercherSuivis($recherche, $typeOperation, $problemeSeulement, $dateDebut, $dateFin, $idHistorique, $nonLuSeulement, $limite),
            'modificationsBdd'  => $repository->rechercherModificationsBdd($recherche, $dateDebut, $dateFin, $limite),
            'surveillanceDocuware' => $repository->rechercherSurveillanceDocuware($recherche, $dateDebut, $dateFin, $limite),
            'limite'            => $limite,
            'limitesPossibles'  => self::LIMITES_POSSIBLES,
            'typesOperation'   => SuiviOperationService::LIBELLES_TYPE,
            'recherche'         => $recherche,
            'typeOperation'    => $typeOperation,
            'problemeSeulement' => $problemeSeulement,
            'nonLuSeulement'    => $nonLuSeulement,
            'idHistorique'      => $idHistorique,
            'dateDebut'         => $dateDebut,
            'dateFin'           => $dateFin,
        ]);
    }

    /**
     * Marque une opération (toutes ses étapes) comme lue ou non lue
     *
     * Préfixe api_ : contrôle de session uniquement (pas de permission par route), l'accès admin est vérifié ici
     *
     * @Route("/api/admin/suivi-operation/{idSuivi}/lu", name="api_suivi_operation_marquer_lu", methods={"POST"})
     */
    public function marquerLu(Request $request, string $idSuivi)
    {
        if (!$this->estAdmin()) {
            return new JsonResponse(['message' => 'Accès refusé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $lu   = (bool) ($data['lu'] ?? false);

        $repository = $this->getEntityManager()->getRepository(HistoriqueOperationEtape::class);
        if ($repository->marquerLu($idSuivi, $lu, $this->getUserName()) === 0) {
            return new JsonResponse(['message' => 'Opération introuvable'], 404);
        }

        return new JsonResponse([
            'lu'          => $lu,
            'luPar'       => $lu ? $this->getUserName() : null,
            'dateLecture' => $lu ? (new \DateTime())->format('d/m/Y H:i') : null,
        ]);
    }

    private function creerDate(?string $date): ?\DateTime
    {
        if (!$date) return null;
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime ?: null;
    }

    /**
     * Redirige vers l'accueil si l'utilisateur connecté n'est pas administrateur (profil HFF-ADMIN)
     */
    private function verifierAcces(): void
    {
        if (!$this->estAdmin()) {
            $this->redirectToRoute('profil_acceuil');
        }
    }
}
