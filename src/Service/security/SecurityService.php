<?php

namespace App\Service\security;

use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\utilisateur\ApplicationProfilPage;
use App\Entity\admin\utilisateur\Profil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SecurityService
{
    // ─── Routes publiques (pas de contrôle d'accès) ──────────────────────────
    private const ROUTES_PUBLIQUES = ['security_signin', 'auth_deconnexion', 'profil_acceuil'];

    // ─── Constantes de permissions (évite les fautes de frappe) ─────────────
    public const PERMISSION_VOIR      = 'peutVoir';
    public const PERMISSION_AJOUTER   = 'peutAjouter';
    public const PERMISSION_MODIFIER  = 'peutModifier';
    public const PERMISSION_SUPPRIMER = 'peutSupprimer';
    public const PERMISSION_EXPORTER  = 'peutExporter';

    // ─── Durée de vie du cache (en secondes) ─────────────────────────────────
    // 3600 = 1 heure. À ajuster selon la fréquence de modification des droits.
    private const CACHE_TTL = 3600;

    // ─── Préfixe des clés de cache ────────────────────────────────────────────
    private const CACHE_PREFIX = 'permissions';

    private EntityManagerInterface $entityManager;
    private SessionInterface $session;
    private CacheInterface $cache;

    /**
     * Cache intra-requête : évite plusieurs appels au cache persistant
     * dans la même requête HTTP (ex: controlerAcces + getPermissions + verifierPermission).
     *
     * @var array<string, array|null>
     */
    private array $cacheRequete = [];

    /**
     * Route courante mémorisée lors de controlerAcces().
     * Permet d'appeler verifierPermission() sans paramètre depuis les contrôleurs.
     */
    private ?string $routeCourrante = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        CacheInterface $cache
    ) {
        $this->entityManager = $entityManager;
        $this->session       = $session;
        $this->cache         = $cache;
    }

    // =========================================================================
    //  POINT D'ENTRÉE — appelé dans index.php avant le contrôleur
    // =========================================================================

    /**
     * Contrôle complet : connexion + accès à la route (peutVoir).
     *
     * @return RedirectResponse|null  null = OK, RedirectResponse = non connecté
     * @throws AccessDeniedException  si connecté mais peutVoir = false
     */
    public function controlerAcces(Request $request): ?RedirectResponse
    {
        $nomRoute = $request->attributes->get('_route');

        // Mémoriser la route pour les appels depuis les contrôleurs
        $this->routeCourrante = $nomRoute;

        // Route publique → laisse passer sans aucun contrôle
        if ($this->estRoutePublique($nomRoute)) {
            return null;
        }

        // Non connecté → redirection vers login (avec URL de retour)
        if (!$this->estConnecte()) {
            return new RedirectResponse($this->genererUrlConnexion($request));
        }

        // Connecté mais peutVoir = false → 403
        if (!$this->verifierPermission(self::PERMISSION_VOIR, $nomRoute)) {
            throw new AccessDeniedException();
        }

        return null;
    }

    // =========================================================================
    //  API PUBLIQUE — utilisable dans les contrôleurs
    // =========================================================================

    /**
     * Vérifie une permission sans lancer d'exception.
     * Idéal pour afficher/masquer des boutons dans Twig ou un contrôleur.
     *
     * @param string      $permission  Une constante PERMISSION_*
     * @param string|null $nomRoute    null = utilise la route courante
     */
    public function verifierPermission(string $permission, ?string $nomRoute = null): bool
    {
        $permissions = $this->chargerPermissions($nomRoute);

        if ($permissions === null) {
            return false;
        }

        return (bool) ($permissions[$permission] ?? false);
    }

    /**
     * Exige une permission — lance AccessDeniedException si refusée.
     * Idéal pour protéger une action critique (suppression, export...).
     *
     * Exemple :
     *   $securityService->exigerPermission(SecurityService::PERMISSION_SUPPRIMER);
     *   $this->supprimerEnregistrement($id); // on arrive ici seulement si autorisé
     *
     * @throws AccessDeniedException
     */
    public function exigerPermission(string $permission, ?string $nomRoute = null): void
    {
        if (!$this->verifierPermission($permission, $nomRoute)) {
            throw new AccessDeniedException(
                sprintf('Permission "%s" refusée pour cette page.', $permission)
            );
        }
    }

    /**
     * Retourne toutes les permissions de la page courante (ou d'une route donnée).
     * Pratique pour passer au template Twig en une seule fois.
     *
     * Exemple contrôleur :
     *   return $this->twig->render('ma_page.html.twig', [
     *       'permissions' => $securityService->getPermissions(),
     *   ]);
     *
     * Exemple Twig :
     *   {% if permissions.peutSupprimer %}
     *       <button>Supprimer</button>
     *   {% endif %}
     */
    public function getPermissions(?string $nomRoute = null): array
    {
        return $this->chargerPermissions($nomRoute) ?? $this->permissionsVides();
    }

    /**
     * Retourne toutes les pages (peutVoir = true) du profil connecté.
     * Utile pour construire le menu de navigation.
     *
     * Retourne un tableau de PageHff groupées par Application :
     * [
     *   'App1' => [PageHff, PageHff, ...],
     *   'App2' => [PageHff, ...],
     * ]
     */
    public function getPagesProfil(): array
    {
        $profil = $this->getProfil();
        if ($profil === null) {
            return [];
        }

        $pages = [];

        // Profil → ApplicationProfil[] → ApplicationProfilPage[] → PageHff
        /** @var ApplicationProfil $applicationProfil */
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            $application    = $applicationProfil->getApplication();
            $nomApplication = $application->getNom();

            /** @var ApplicationProfilPage $applicationProfilPage */
            foreach ($applicationProfil->getLiaisonsPage() as $applicationProfilPage) {
                if (!$applicationProfilPage->isPeutVoir()) continue;

                $pages[$nomApplication][] = $applicationProfilPage->getPage();
            }
        }

        return $pages;
    }

    /**
     * Retourne les infos de l'utilisateur connecté depuis la session.
     */
    public function getUserInfo(): ?array
    {
        if (!$this->estConnecte()) {
            return null;
        }
        return $this->session->get('user_info');
    }

    /**
     * Invalide TOUTES les entrées de cache pour un profil donné.
     *
     * À appeler depuis le back-office après toute modification des droits d'un profil :
     *
     *   // Dans ton contrôleur de gestion des profils :
     *   $securityService->invaliderCacheProfil($profil->getId());
     *
     * Après l'appel, la prochaine requête de n'importe quel utilisateur
     * ayant ce profil recalculera les permissions depuis la BDD.
     */
    public function invaliderCacheProfil(int $profilId): void
    {
        // Invalidation par tag si l'adaptateur le supporte (FilesystemTagAwareAdapter, RedisTagAwareAdapter...)
        if ($this->cache instanceof \Symfony\Contracts\Cache\TagAwareCacheInterface) {
            $this->cache->invalidateTags([$this->tagProfil($profilId)]);
        } else {
            // Fallback : suppression clé par clé en parcourant les pages du profil
            $this->invaliderCacheProfilSansTags($profilId);
        }

        // Vider aussi le cache intra-requête
        $this->cacheRequete = [];
    }

    // =========================================================================
    //  LOGIQUE INTERNE
    // =========================================================================

    /**
     * Charge les permissions avec deux niveaux de cache :
     *
     *   Niveau 1 — cache intra-requête ($cacheRequete)
     *              Tableau PHP en mémoire. Évite plusieurs appels au cache persistant
     *              dans la même requête (ex: controlerAcces + verifierPermission + getPermissions).
     *
     *   Niveau 2 — cache persistant ($cache)
     *              Fichier / Redis / APCu selon la config de ton conteneur.
     *              Clé : "permissions_<profilId>_<nomRoute>"
     *              Partagée entre tous les utilisateurs ayant le même profil.
     *
     * @return array|null  ['peutVoir' => bool, 'peutAjouter' => bool, ...]
     */
    private function chargerPermissions(?string $nomRoute = null): ?array
    {
        $nomRoute = $nomRoute ?? $this->routeCourrante;

        if ($nomRoute === null) {
            return null;
        }

        // ── Niveau 1 : cache intra-requête ────────────────────────────────────
        if (array_key_exists($nomRoute, $this->cacheRequete)) {
            return $this->cacheRequete[$nomRoute];
        }

        $profilId = $this->getProfilId();
        if ($profilId === null) {
            return $this->cacheRequete[$nomRoute] = null;
        }

        // ── Niveau 2 : cache persistant ───────────────────────────────────────
        $cleCache = $this->construireCleCache($profilId, $nomRoute);

        $permissions = $this->cache->get(
            $cleCache,
            function (ItemInterface $item) use ($profilId, $nomRoute): ?array {
                // Ce callback n'est exécuté qu'en cas de MISS (clé absente ou expirée)
                $item->expiresAfter(self::CACHE_TTL);

                // Associer un tag au profil pour invalider toutes ses routes en une fois
                if (method_exists($item, 'tag')) {
                    $item->tag([$this->tagProfil($profilId)]);
                }

                return $this->calculerPermissionsDepuisBdd($nomRoute);
            }
        );

        // Stocker dans le cache intra-requête pour les appels suivants de la même requête
        return $this->cacheRequete[$nomRoute] = $permissions;
    }

    /**
     * Requête BDD réelle : navigue Profil → ApplicationProfil → ApplicationProfilPage.
     * N'est appelée que lors d'un MISS de cache persistant.
     */
    private function calculerPermissionsDepuisBdd(string $nomRoute): ?array
    {
        $profil = $this->getProfil();
        if ($profil === null) {
            return null;
        }

        /** @var ApplicationProfil $applicationProfil */
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            /** @var ApplicationProfilPage $applicationProfilPage */
            foreach ($applicationProfil->getLiaisonsPage() as $applicationProfilPage) {
                if ($applicationProfilPage->getPage()->getNomRoute() === $nomRoute) {
                    return [
                        self::PERMISSION_VOIR      => $applicationProfilPage->isPeutVoir(),
                        self::PERMISSION_AJOUTER   => $applicationProfilPage->isPeutAjouter(),
                        self::PERMISSION_MODIFIER  => $applicationProfilPage->isPeutModifier(),
                        self::PERMISSION_SUPPRIMER => $applicationProfilPage->isPeutSupprimer(),
                        self::PERMISSION_EXPORTER  => $applicationProfilPage->isPeutExporter(),
                    ];
                }
            }
        }

        // Route non configurée → accès refusé
        return null;
    }

    /**
     * Fallback d'invalidation sans tags.
     * Supprime les clés route par route en parcourant les pages du profil depuis la BDD.
     */
    private function invaliderCacheProfilSansTags(int $profilId): void
    {
        $profil = $this->entityManager->getRepository(Profil::class)->find($profilId);
        if ($profil === null) {
            return;
        }

        /** @var ApplicationProfil $applicationProfil */
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            /** @var ApplicationProfilPage $applicationProfilPage */
            foreach ($applicationProfil->getLiaisonsPage() as $applicationProfilPage) {
                $nomRoute = $applicationProfilPage->getPage()->getNomRoute();
                $cleCache = $this->construireCleCache($profilId, $nomRoute);
                $this->cache->delete($cleCache);
            }
        }
    }

    // ─── Helpers de nommage ──────────────────────────────────────────────────

    /**
     * Construit la clé de cache : "permissions_<profilId>_<nomRoute>".
     * Les caractères spéciaux sont remplacés car les clés PSR-6 n'acceptent pas {}()/\@:
     */
    private function construireCleCache(int $profilId, string $nomRoute): string
    {
        $routeSanitisee = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nomRoute);
        return sprintf('%s_%d_%s', self::CACHE_PREFIX, $profilId, $routeSanitisee);
    }

    /**
     * Tag de cache pour un profil : "profil_<profilId>".
     * Permet d'invalider d'un coup toutes les routes de ce profil.
     */
    private function tagProfil(int $profilId): string
    {
        return sprintf('profil_%d', $profilId);
    }

    // ─── Helpers session ─────────────────────────────────────────────────────

    private function estRoutePublique(?string $nomRoute): bool
    {
        return $nomRoute !== null && in_array($nomRoute, self::ROUTES_PUBLIQUES, true);
    }

    private function estConnecte(): bool
    {
        return $this->session->has('user_info');
    }

    /**
     * Retourne uniquement le profilId depuis la session (sans charger l'entité).
     * Utilisé pour construire la clé de cache sans requête BDD.
     */
    private function getProfilId(): ?int
    {
        $userInfo = $this->session->get('user_info');
        return isset($userInfo['profilId']) ? (int) $userInfo['profilId'] : null;
    }

    /**
     * Charge l'entité Profil depuis la session.
     * Retourne null si non connecté ou profil introuvable.
     */
    private function getProfil(): ?Profil
    {
        $profilId = $this->getProfilId();
        return $profilId ? $this->entityManager->getRepository(Profil::class)->find($profilId) : null;
    }

    private function genererUrlConnexion(Request $request): string
    {
        $urlActuelle  = $request->getRequestUri();
        $urlConnexion = '/login';

        if ($urlActuelle !== '/' && $urlActuelle !== $urlConnexion) {
            $urlConnexion .= '?redirect=' . urlencode($urlActuelle);
        }

        return $urlConnexion;
    }

    private function permissionsVides(): array
    {
        return [
            self::PERMISSION_VOIR      => false,
            self::PERMISSION_AJOUTER   => false,
            self::PERMISSION_MODIFIER  => false,
            self::PERMISSION_SUPPRIMER => false,
            self::PERMISSION_EXPORTER  => false,
        ];
    }
}
