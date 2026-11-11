<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use App\Entity\admin\historisation\documentOperation\HistoriqueOperationEtape;
use App\Entity\admin\historisation\documentOperation\SuiviOperationTache;
use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\utilisateur\User;
use App\Service\historiqueOperation\suivi\AlerteTacheSuiviOperation;
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
                'taches'       => $repositoryTache->rechercher($filtres),
                'filtres'      => $filtres,
                'intervenants' => $this->intervenants(),
                'listeIntervenants' => $this->listeIntervenants(),
                'utilisateurs' => array_column(
                    $this->getEntityManager()->getConnection()->fetchAllAssociative('SELECT nom_utilisateur FROM users ORDER BY nom_utilisateur'),
                    'nom_utilisateur'
                ),
            ]);
        }

        // onglet "Tableau de bord" : statistiques des opérations suivies (30 derniers jours par défaut)
        if ($request->query->get('vue') === 'tableau-de-bord') {
            $debutPeriode = $dateDebut ?? new \DateTime('-29 days');
            $finPeriode   = $dateFin ?? new \DateTime();
            // coché par défaut : absent de l'URL = blocages métier exclus
            $sansControles = $request->query->get('sans_controles', '1') === '1';

            return $this->render('admin/historisation/suivi-operation/tableau-de-bord.html.twig', $parametresTaches + [
                'stats'     => $this->getEntityManager()->getRepository(HistoriqueOperationEtape::class)->tableauDeBord($debutPeriode, $finPeriode, $sansControles),
                'sansControles' => $sansControles,
                'dateDebut' => $debutPeriode,
                'dateFin'   => $finPeriode,
            ]);
        }

        // onglet "Kanban" : une colonne par statut, changement de statut par glisser-déposer
        if ($request->query->get('vue') === 'kanban') {
            $assigne = $request->query->get('assigne');
            $colonnes = array_fill_keys(array_keys(TacheSuiviOperation::LIBELLES_STATUT), []);
            $taches = array_merge(
                $repositoryTache->rechercher(['statut' => 'OUVERTES', 'assigne' => $assigne]),
                $repositoryTache->termineesRecentes($assigne)
            );
            foreach ($taches as $tache) {
                $colonnes[$tache->getStatut()][] = $tache;
            }

            return $this->render('admin/historisation/suivi-operation/kanban.html.twig', $parametresTaches + [
                'colonnes'     => $colonnes,
                'assigne'      => $assigne,
                'intervenants' => $this->intervenants(),
            ]);
        }

        // onglet "Planning" : Gantt des tâches ouvertes planifiées (échéance saisie)
        if ($request->query->get('vue') === 'planning') {
            $assigne = $request->query->get('assigne');
            $barres  = [];
            foreach ($repositoryTache->planifiees($assigne) as $tache) {
                $barres[] = [
                    'id'       => 'tache-' . $tache->getId(),
                    'name'     => ($tache->getAssigneA() ?: 'Non assignée') . ' – n° ' . $tache->getId() . ' ' . $tache->getEtape(),
                    // sans date de début : barre d'un jour sur l'échéance
                    'start'    => ($tache->getDateDebut() ?: $tache->getEcheance())->format('Y-m-d'),
                    'end'      => $tache->getEcheance()->format('Y-m-d'),
                    'progress' => $tache->getStatut() === TacheSuiviOperation::STATUT_EN_COURS ? 50 : 0,
                    'custom_class' => 'priorite-' . strtolower($tache->getPriorite()) . ($tache->getEcheance()->format('Y-m-d') < date('Y-m-d') ? ' en-retard' : ''),
                    'statut'   => TacheSuiviOperation::LIBELLES_STATUT[$tache->getStatut()] ?? $tache->getStatut(),
                ];
            }

            return $this->render('admin/historisation/suivi-operation/planning.html.twig', $parametresTaches + [
                'barres'       => $barres,
                'assigne'      => $assigne,
                'intervenants' => $this->intervenants(),
            ]);
        }

        $repository = $this->getEntityManager()->getRepository(HistoriqueOperationEtape::class);
        $suivis     = $repository->rechercherSuivis($recherche, $typeOperation, $problemeSeulement, $dateDebut, $dateFin, $idHistorique, $nonLuSeulement, $limite, $idTache);
        $idsTache   = [];
        $typesArretes = [];
        foreach ($suivis as $suivi) {
            if ($suivi['statut'] !== SuiviOperationService::STATUT_SUCCES) {
                $typesArretes[] = $suivi['etapes'][0]->getTypeOperation();
            }
            foreach ($suivi['etapes'] as $etape) {
                $idsTache[] = $etape->getIdTache();
            }
        }

        return $this->render('admin/historisation/suivi-operation/index.html.twig', $parametresTaches + [
            'suivis'            => $suivis,
            'taches'            => $repositoryTache->parIds($idsTache),
            'etapesReference'   => $repository->etapesReference($typesArretes),
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
        $ancienAssigne = $tache->getAssigneA();

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
            $assigneA = trim((string) $data['assigneA']);
            // la personne déjà assignée reste acceptée même si elle n'a plus le rôle intervenant
            if ($assigneA !== '' && $assigneA !== $tache->getAssigneA() && !in_array($assigneA, $this->intervenants(), true)) {
                return new JsonResponse(['message' => "« $assigneA » n'a pas le rôle intervenant"], 400);
            }
            $tache->setAssigneA($assigneA !== '' ? $assigneA : null);
        }
        if (array_key_exists('dateDebut', $data)) {
            $tache->setDateDebut($this->creerDate($data['dateDebut'] ?: null));
        }
        if (array_key_exists('echeance', $data)) {
            $tache->setEcheance($this->creerDate($data['echeance'] ?: null));
        }
        if ($tache->getDateDebut() && $tache->getEcheance() && $tache->getDateDebut() > $tache->getEcheance()) {
            return new JsonResponse(['message' => "La date de début doit précéder l'échéance"], 400);
        }
        if (array_key_exists('cause', $data)) {
            $tache->setCause(trim((string) $data['cause']) !== '' ? trim($data['cause']) : null);
        }
        if (array_key_exists('correction', $data)) {
            $tache->setCorrection(trim((string) $data['correction']) !== '' ? trim($data['correction']) : null);
        }

        // résolution : date et auteur ; la régression est soldée (une nouvelle occurrence la signalera à nouveau)
        // (résolu -> déployé garde la date de résolution d'origine)
        $estCorrigee = in_array($tache->getStatut(), TacheSuiviOperation::STATUTS_CORRIGES, true);
        if ($estCorrigee && !in_array($ancienStatut, TacheSuiviOperation::STATUTS_CORRIGES, true)) {
            $tache->setDateResolution(new \DateTime())->setResoluPar($this->getUserName())->setEstRegression(false);
        } elseif (!$estCorrigee) {
            $tache->setDateResolution(null)->setResoluPar(null);
        }

        $tache->setDateModification(new \DateTime())->setModifiePar($this->getUserName());
        $this->getEntityManager()->flush();

        // mail à la nouvelle personne assignée (pas quand on s'assigne soi-même)
        if ($tache->getAssigneA() && $tache->getAssigneA() !== $ancienAssigne && $tache->getAssigneA() !== $this->getUserName()) {
            AlerteTacheSuiviOperation::assignation(
                $this->getEntityManager()->getConnection(), $tache->getId(), $tache->getAssigneA(), (string) $this->getUserName(),
                $tache->getTypeOperation(), $tache->getEtape(), $tache->getDernierMessage()
            );
        }

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
     * Alertes interrogées toutes les 90 s par la barre de navigation (notification du navigateur) :
     * nouveaux bugs urgents et régressions pour les intervenants, tâches ouvertes assignées à l'utilisateur.
     * "cle" identifie une alerte : le navigateur ne notifie qu'une fois chaque clé.
     *
     * @Route("/api/suivi-operation/alertes", name="api_suivi_operation_alertes", methods={"GET"})
     */
    public function alertes()
    {
        $nom = $this->getUserName();
        if ($nom === '') {
            return new JsonResponse([]);
        }

        $connexion = $this->getEntityManager()->getConnection();
        $estIntervenant = (bool) $connexion->fetchOne(
            'SELECT 1 FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.nom_utilisateur = ? AND ur.role_id = ?',
            [$nom, Role::ROLE_INTERVENANT]
        );

        $lignes = $connexion->fetchAllAssociative(
            "SELECT TOP 20 id, type_operation, etape, assigne_a, est_regression, priorite, statut,
                    CONVERT(VARCHAR(19), date_resolution, 120) AS date_resolution
             FROM suivi_operation_tache
             WHERE statut IN ('A_TRIER', 'A_FAIRE', 'EN_COURS')
               AND (assigne_a = ? OR (? = 1 AND (est_regression = 1 OR (statut = 'A_TRIER' AND priorite = 'HAUTE'))))
             ORDER BY derniere_occurrence DESC",
            [$nom, $estIntervenant ? 1 : 0]
        );

        $lien = $this->getUrlGenerator()->generate('suivi_operation_index', ['vue' => 'taches']);
        $alertes = [];
        foreach ($lignes as $ligne) {
            if ($estIntervenant && $ligne['est_regression']) {
                [$cle, $titre] = ['r' . $ligne['id'] . '-' . $ligne['date_resolution'], 'Régression : tâche n° ' . $ligne['id']];
            } elseif ($estIntervenant && $ligne['statut'] === 'A_TRIER' && $ligne['priorite'] === 'HAUTE') {
                [$cle, $titre] = ['n' . $ligne['id'], 'Nouveau bug urgent : tâche n° ' . $ligne['id']];
            } else {
                [$cle, $titre] = ['a' . $ligne['id'] . '-' . $nom, 'Tâche n° ' . $ligne['id'] . ' assignée'];
            }
            $alertes[] = [
                'cle'   => $cle,
                'titre' => $titre,
                'texte' => $ligne['type_operation'] . ' : ' . $ligne['etape'],
                'lien'  => $lien . '#tache-' . $ligne['id'],
            ];
        }

        return new JsonResponse($alertes);
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

    /**
     * Noms des utilisateurs ayant le rôle INTERVENANT (table user_roles), proposés pour l'assignation des tâches
     *
     * @return string[]
     */
    private function intervenants(): array
    {
        return array_column($this->listeIntervenants(), 'nom_utilisateur');
    }

    /**
     * Utilisateurs ayant le rôle INTERVENANT, avec leur mail (exigé par les mails du ticketing)
     *
     * @return array<int, array{id: int, nom_utilisateur: string, mail: ?string}>
     */
    private function listeIntervenants(): array
    {
        return $this->getEntityManager()->getRepository(User::class)->createQueryBuilder('u')
            ->select('u.id, u.nom_utilisateur, u.mail')
            ->innerJoin('u.roles', 'r')
            ->where('r.id = :role')
            ->setParameter('role', Role::ROLE_INTERVENANT)
            ->orderBy('u.nom_utilisateur', 'ASC')
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Donne ou retire le rôle INTERVENANT à un utilisateur (table user_roles).
     * Ce rôle sert aussi au ticketing : l'utilisateur y devient intervenant possible.
     *
     * @Route("/api/admin/suivi-operation/intervenant", name="api_suivi_operation_intervenant", methods={"POST"})
     */
    public function gererIntervenant(Request $request)
    {
        if (!$this->estAdmin()) {
            return new JsonResponse(['message' => 'Accès refusé'], 403);
        }

        $data   = json_decode($request->getContent(), true) ?: [];
        $nom    = trim((string) ($data['nomUtilisateur'] ?? ''));
        $ajout  = ($data['action'] ?? '') === 'ajouter';
        $connexion = $this->getEntityManager()->getConnection();

        $idUtilisateur = $nom !== '' ? $connexion->fetchOne('SELECT id FROM users WHERE nom_utilisateur = ?', [$nom]) : false;
        if ($idUtilisateur === false) {
            return new JsonResponse(['message' => "Utilisateur « $nom » introuvable"], 404);
        }

        $dejaIntervenant = (bool) $connexion->fetchOne(
            'SELECT 1 FROM user_roles WHERE user_id = ? AND role_id = ?',
            [$idUtilisateur, Role::ROLE_INTERVENANT]
        );

        if ($ajout && !$dejaIntervenant) {
            $connexion->insert('user_roles', ['user_id' => $idUtilisateur, 'role_id' => Role::ROLE_INTERVENANT]);
        } elseif (!$ajout && $dejaIntervenant) {
            $connexion->delete('user_roles', ['user_id' => $idUtilisateur, 'role_id' => Role::ROLE_INTERVENANT]);
        }

        return new JsonResponse(['intervenants' => $this->listeIntervenants()]);
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
