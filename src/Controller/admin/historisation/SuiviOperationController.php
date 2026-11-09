<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use App\Entity\admin\historisation\documentOperation\HistoriqueOperationEtape;
use App\Entity\admin\historisation\documentOperation\SuiviOperationTache;
use App\Service\historiqueOperation\suivi\SuiviOperationService;
use App\Service\historiqueOperation\suivi\TacheSuiviOperation;
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
        $idTache           = $request->query->getInt('tache') ?: null;

        $repositoryTache = $this->getEntityManager()->getRepository(SuiviOperationTache::class);
        $parametresTaches = [
            'compteursTaches'   => $repositoryTache->compteurs(),
            'libellesStatutTache'   => TacheSuiviOperation::LIBELLES_STATUT,
            'libellesEffort'    => TacheSuiviOperation::LIBELLES_EFFORT,
            'libellesPriorite'  => TacheSuiviOperation::LIBELLES_PRIORITE,
            'typesOperation'    => SuiviOperationService::LIBELLES_TYPE,
        ];

        // onglet "Tâches" : même route (pas de droit de page supplémentaire à paramétrer)
        if ($request->query->get('vue') === 'taches') {
            $filtres = [
                'statut'    => $request->query->get('statut', 'OUVERTES'),
                'effort'    => $request->query->get('effort'),
                'priorite'  => $request->query->get('priorite'),
                'type'      => $request->query->get('type'),
                'assigne'   => $request->query->get('assigne'),
                'recherche' => $request->query->get('recherche'),
            ];

            return $this->render('admin/historisation/suivi-operation/taches.html.twig', $parametresTaches + [
                'taches'  => $repositoryTache->rechercher($filtres),
                'filtres' => $filtres,
            ]);
        }

        $repository = $this->getEntityManager()->getRepository(HistoriqueOperationEtape::class);
        $suivis     = $repository->rechercherSuivis($recherche, $typeOperation, $problemeSeulement, $dateDebut, $dateFin, $idHistorique, $nonLuSeulement, $limite, $idTache);
        $idsTache   = [];
        foreach ($suivis as $suivi) {
            foreach ($suivi['etapes'] as $etape) {
                $idsTache[] = $etape->getIdTache();
            }
        }

        return $this->render('admin/historisation/suivi-operation/index.html.twig', $parametresTaches + [
            'suivis'            => $suivis,
            'taches'            => $repositoryTache->parIds($idsTache),
            'idTache'           => $idTache,
            'modificationsBdd'  => $repository->rechercherModificationsBdd($recherche, $dateDebut, $dateFin, $limite),
            'surveillanceDocuware' => $repository->rechercherSurveillanceDocuware($recherche, $dateDebut, $dateFin, $limite),
            'limite'            => $limite,
            'limitesPossibles'  => self::LIMITES_POSSIBLES,
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

    /**
     * Modifie une tâche de correction (tri, avancement, analyse). Seuls les champs envoyés sont modifiés.
     * Choisir l'effort d'une tâche "à trier" la fait passer "à faire".
     *
     * @Route("/api/admin/suivi-operation/tache/{id}", name="api_suivi_operation_tache_modifier", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function modifierTache(Request $request, int $id)
    {
        if (!$this->estAdmin()) {
            return new JsonResponse(['message' => 'Accès refusé'], 403);
        }

        /** @var SuiviOperationTache|null $tache */
        $tache = $this->getEntityManager()->getRepository(SuiviOperationTache::class)->find($id);
        if ($tache === null) {
            return new JsonResponse(['message' => 'Tâche introuvable'], 404);
        }

        $data   = json_decode($request->getContent(), true) ?: [];
        $ancienStatut = $tache->getStatut();

        if (array_key_exists('effort', $data)) {
            if ($data['effort'] !== null && $data['effort'] !== '' && !isset(TacheSuiviOperation::LIBELLES_EFFORT[$data['effort']])) {
                return new JsonResponse(['message' => 'Effort inconnu'], 400);
            }
            $tache->setEffort($data['effort'] ?: null);
            if ($tache->getEffort() !== null && $ancienStatut === TacheSuiviOperation::STATUT_A_TRIER && !isset($data['statut'])) {
                $tache->setStatut(TacheSuiviOperation::STATUT_A_FAIRE);
            }
        }
        if (isset($data['priorite'])) {
            if (!isset(TacheSuiviOperation::LIBELLES_PRIORITE[$data['priorite']])) {
                return new JsonResponse(['message' => 'Priorité inconnue'], 400);
            }
            $tache->setPriorite($data['priorite']);
        }
        if (isset($data['statut'])) {
            if (!isset(TacheSuiviOperation::LIBELLES_STATUT[$data['statut']])) {
                return new JsonResponse(['message' => 'Statut inconnu'], 400);
            }
            $tache->setStatut($data['statut']);
        }
        if (array_key_exists('assigneA', $data)) {
            $tache->setAssigneA(trim((string) $data['assigneA']) !== '' ? mb_substr(trim($data['assigneA']), 0, 100) : null);
        }
        if (array_key_exists('echeance', $data)) {
            $tache->setEcheance($this->creerDate($data['echeance'] ?: null));
        }
        if (array_key_exists('cause', $data)) {
            $tache->setCause(trim((string) $data['cause']) !== '' ? trim($data['cause']) : null);
        }
        if (array_key_exists('correction', $data)) {
            $tache->setCorrection(trim((string) $data['correction']) !== '' ? mb_substr(trim($data['correction']), 0, 500) : null);
        }

        // résolution : date et auteur ; la régression est soldée (une nouvelle occurrence la signalera à nouveau)
        if ($tache->getStatut() === TacheSuiviOperation::STATUT_RESOLU && $ancienStatut !== TacheSuiviOperation::STATUT_RESOLU) {
            $tache->setDateResolution(new \DateTime())->setResoluPar($this->getUserName())->setEstRegression(false);
        } elseif ($tache->getStatut() !== TacheSuiviOperation::STATUT_RESOLU) {
            $tache->setDateResolution(null)->setResoluPar(null);
        }

        $tache->setDateModification(new \DateTime())->setModifiePar($this->getUserName());
        $this->getEntityManager()->flush();

        return new JsonResponse([
            'statut'         => $tache->getStatut(),
            'libelleStatut'  => TacheSuiviOperation::LIBELLES_STATUT[$tache->getStatut()],
            'effort'         => $tache->getEffort(),
            'priorite'       => $tache->getPriorite(),
            'estRegression'  => $tache->getEstRegression(),
            'resolu'         => $tache->getDateResolution() ? 'Résolu par ' . $tache->getResoluPar() . ' le ' . $tache->getDateResolution()->format('d/m/Y H:i') : null,
            'modification'   => 'Modifié par ' . $tache->getModifiePar() . ' le ' . $tache->getDateModification()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Crée les tâches des opérations en problème enregistrées avant la mise en place des tâches
     *
     * @Route("/api/admin/suivi-operation/taches/reprise", name="api_suivi_operation_taches_reprise", methods={"POST"})
     */
    public function reprendreHistorique()
    {
        if (!$this->estAdmin()) {
            return new JsonResponse(['message' => 'Accès refusé'], 403);
        }

        set_time_limit(300);
        $nombre = TacheSuiviOperation::reprendreHistorique($this->getEntityManager()->getConnection());

        return new JsonResponse(['nombre' => $nombre]);
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
