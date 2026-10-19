<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use App\Entity\admin\historisation\documentOperation\HistoriqueOperationEtape;
use App\Service\historiqueOperation\suivi\SuiviOperationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Suivi étape par étape des opérations (accessible uniquement aux administrateurs HFF-ADMIN)
 */
class SuiviOperationController extends Controller
{
    /**
     * @Route("/admin/suivi-operation", name="suivi_operation_index")
     */
    public function index(Request $request)
    {
        $this->verifierAcces();

        $recherche         = $request->query->get('recherche');
        $typeOperation    = $request->query->get('type');
        $problemeSeulement = $request->query->getBoolean('probleme');
        $idHistorique      = $request->query->getInt('historique') ?: null;
        $dateDebut         = $this->creerDate($request->query->get('date_debut'));
        $dateFin           = $this->creerDate($request->query->get('date_fin'));

        $repository = $this->getEntityManager()->getRepository(HistoriqueOperationEtape::class);

        return $this->render('admin/historisation/suivi-operation/index.html.twig', [
            'suivis'            => $repository->rechercherSuivis($recherche, $typeOperation, $problemeSeulement, $dateDebut, $dateFin, $idHistorique),
            'typesOperation'   => SuiviOperationService::LIBELLES_TYPE,
            'recherche'         => $recherche,
            'typeOperation'    => $typeOperation,
            'problemeSeulement' => $problemeSeulement,
            'idHistorique'      => $idHistorique,
            'dateDebut'         => $dateDebut,
            'dateFin'           => $dateFin,
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
