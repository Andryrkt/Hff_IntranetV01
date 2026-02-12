<?php

namespace App\Service\security;

use App\Entity\admin\utilisateur\Profil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SecurityService
{
    // ─── Routes publiques (pas de contrôle d'accès) ──────────────────────────
    private const ROUTES_PUBLIQUES = ['security_signin', 'auth_deconnexion', 'accueil'];

    // ─── Constantes de permissions (évite les fautes de frappe) ─────────────
    public const PERMISSION_VOIR      = 'peutVoir';
    public const PERMISSION_AJOUTER   = 'peutAjouter';
    public const PERMISSION_MODIFIER  = 'peutModifier';
    public const PERMISSION_SUPPRIMER = 'peutSupprimer';
    public const PERMISSION_EXPORTER  = 'peutExporter';

    private EntityManagerInterface $entityManager;
    private SessionInterface $session;

    /**
     * Cache des permissions pour la requête courante.
     * Évite plusieurs requêtes BDD pour la même route.
     * @var array<string, array|null>
     */
    private array $cachePermissions = [];

    /**
     * Route courante mémorisée lors de controlerAcces().
     * Permet d'appeler verifierPermission() sans paramètre depuis les contrôleurs.
     */
    private ?string $routeCourrante = null;

    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
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
            throw new AccessDeniedException(
                sprintf('Accès refusé à la route "%s".', $nomRoute)
            );
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
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            $application    = $applicationProfil->getApplication();
            $nomApplication = $application->getNom();

            foreach ($applicationProfil->getApplicationProfilPages() as $applicationProfilPage) {
                if (!$applicationProfilPage->isPeutVoir()) {
                    continue;
                }
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

    // =========================================================================
    //  LOGIQUE INTERNE
    // =========================================================================

    /**
     * Charge les permissions depuis la BDD avec cache par route.
     * Retourne null si aucune entrée trouvée (= accès non configuré → refusé).
     *
     * Navigation : Profil → ApplicationProfil[] → ApplicationProfilPage[] → PageHff
     *
     * @return array|null  ['peutVoir' => bool, 'peutAjouter' => bool, ...]
     */
    private function chargerPermissions(?string $nomRoute = null): ?array
    {
        $nomRoute = $nomRoute ?? $this->routeCourrante;

        if ($nomRoute === null) {
            return null;
        }

        // Retour depuis le cache si déjà chargé
        if (array_key_exists($nomRoute, $this->cachePermissions)) {
            return $this->cachePermissions[$nomRoute];
        }

        $profil = $this->getProfil();
        if ($profil === null) {
            return $this->cachePermissions[$nomRoute] = null;
        }

        // Navigation via les relations : Profil → ApplicationProfil[] → ApplicationProfilPage[]
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            foreach ($applicationProfil->getApplicationProfilPages() as $applicationProfilPage) {
                if ($applicationProfilPage->getPage()->getNomRoute() === $nomRoute) {
                    return $this->cachePermissions[$nomRoute] = [
                        self::PERMISSION_VOIR      => $applicationProfilPage->isPeutVoir(),
                        self::PERMISSION_AJOUTER   => $applicationProfilPage->isPeutAjouter(),
                        self::PERMISSION_MODIFIER  => $applicationProfilPage->isPeutModifier(),
                        self::PERMISSION_SUPPRIMER => $applicationProfilPage->isPeutSupprimer(),
                        self::PERMISSION_EXPORTER  => $applicationProfilPage->isPeutExporter(),
                    ];
                }
            }
        }

        // Aucune entrée trouvée pour cette route → accès non configuré = refusé
        return $this->cachePermissions[$nomRoute] = null;
    }

    private function estRoutePublique(?string $nomRoute): bool
    {
        return $nomRoute !== null && in_array($nomRoute, self::ROUTES_PUBLIQUES, true);
    }

    private function estConnecte(): bool
    {
        return $this->session->has('user_info');
    }

    /**
     * Charge l'entité Profil depuis la session.
     * Retourne null si non connecté ou profil introuvable.
     */
    private function getProfil(): ?Profil
    {
        $userInfo = $this->session->get('user_info');
        $profilId = isset($userInfo['profilId']) ? (int) $userInfo['profilId'] : null;

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
