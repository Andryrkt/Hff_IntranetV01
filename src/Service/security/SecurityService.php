<?php

namespace App\Service\security;

use App\Service\UserData\UserDataService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * SecurityService — Contrôle d'accès aux routes et vérification des permissions.
 *
 * Délègue toute la logique de données à DataService.
 * Ce service est intentionnellement léger : il ne connaît ni la BDD, ni la session,
 * ni le cache — uniquement les règles d'accès.
 */
class SecurityService
{
    private UserDataService $dataService;

    // ─── Routes publiques (pas de contrôle d'accès) ──────────────────────────
    private const ROUTES_SEMI_PRIVEES = ['choix_societe'];
    private const ROUTES_PUBLIQUES = ['security_signin', 'auth_deconnexion'];
    private const PREFIXES_API = ['api_'];

    // ─── Constantes de permissions (évite les fautes de frappe) ─────────────
    public const PERMISSION_VOIR      = 'peutVoir';
    public const PERMISSION_AJOUTER   = 'peutAjouter';
    public const PERMISSION_MODIFIER  = 'peutModifier';
    public const PERMISSION_SUPPRIMER = 'peutSupprimer';
    public const PERMISSION_EXPORTER  = 'peutExporter';

    /**
     * Route courante mémorisée lors de controlerAcces().
     * Permet d'appeler verifierPermission() sans paramètre depuis les contrôleurs.
     */
    private ?string $routeCourrante = null;

    public function __construct(UserDataService $dataService)
    {
        $this->dataService = $dataService;
    }

    public function getRouteCourrante(): ?string
    {
        return $this->routeCourrante;
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

        // Route publique ou Routes APi → laisse passer sans aucun contrôle
        if ($this->estRoutePublique($nomRoute) || $this->estRouteApi($nomRoute)) {
            return null;
        }

        // Non connecté → redirection vers login (avec URL de retour)
        if (!$this->dataService->isUserConnected()) {
            return new RedirectResponse($this->genererUrlConnexion());
        }
        // Connecté et route semi-privee → laisse passer
        elseif ($this->estRouteSemiPrivee($nomRoute)) {
            return null;
        }

        // Connecté mais profil non selectionné → redirection vers login
        if ($this->dataService->getProfilId() === null) {
            return new RedirectResponse($this->genererUrlConnexion());
        }

        // Connecté mais peutVoir = false → 403
        if ($this->verifierPermission(self::PERMISSION_VOIR, $nomRoute)) {
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
        $permissions = $this->dataService->getPermissions($nomRoute ?? $this->routeCourrante);

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
        return $this->dataService->getPermissions($nomRoute ?? $this->routeCourrante)
            ?? $this->permissionsVides();
    }

    /**
     * Retourne toutes les pages visibles du profil connecté, groupées par application.
     * Délègue à DataService — résultat mis en cache applicatif.
     */
    public function getPagesProfil(): array
    {
        return $this->dataService->getPagesProfil();
    }

    /**
     * Retourne les infos de l'utilisateur connecté depuis la session.
     */
    public function getUserInfo(): ?array
    {
        return $this->dataService->getUserInfo();
    }

    /** 
     * Retourne l'id du profil
     */
    public function getProfilId(): ?int
    {
        return $this->dataService->getProfilId();
    }

    // =========================================================================
    //  LOGIQUE INTERNE
    // =========================================================================

    private function estRoutePublique(?string $nomRoute): bool
    {
        return $nomRoute !== null && in_array($nomRoute, self::ROUTES_PUBLIQUES, true);
    }

    private function estRouteSemiPrivee(?string $nomRoute): bool
    {
        return $nomRoute !== null && in_array($nomRoute, self::ROUTES_SEMI_PRIVEES, true);
    }

    private function estRouteApi(?string $nomRoute): bool
    {
        if ($nomRoute === null) return false;

        foreach (self::PREFIXES_API as $prefix) {
            if (str_starts_with($nomRoute, $prefix)) return true;
        }
        return false;
    }

    private function genererUrlConnexion(): string
    {
        global $container;
        $urlGenerator = $container->get('router');

        return $urlGenerator->generate('security_signin');
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
