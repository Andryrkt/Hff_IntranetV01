<?php

namespace App\Service\security;

use App\Entity\admin\utilisateur\Profil;
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
    private const ROUTES_SEMI_PRIVEES = ['choix_societe', 'sso_annuaire'];
    private const ROUTE_ACCUEIL = 'profil_acceuil';
    private const ROUTES_PUBLIQUES = ['security_signin', 'auth_deconnexion'];
    private const PREFIXES_API = ['api_'];
    private const PREFIXES_EXPORT = ['export_'];

    // ─── Constantes de permissions (évite les fautes de frappe) ─────────────
    public const PERMISSION_VOIR             = 'peutVoir';
    public const PERMISSION_AUTH_2           = 'peutVoirListeAvecDebiteur'; // peut voir la liste des demandes qui sont débités par les agences et services autorisés
    public const PERMISSION_MULTI_SUCCURSALE = 'peutMultiSuccursale'; // peut voir tous sur la liste sans restriction
    public const PERMISSION_SUPPRIMER        = 'peutSupprimer';
    public const PERMISSION_EXPORTER         = 'peutExporter';

    /**
     * Route courante mémorisée lors de controlerAcces().
     * Permet d'appeler verifierPermission() sans paramètre depuis les contrôleurs.
     */
    private ?string $routeCourrante = null;

    private ?bool $estAdmin = null;
    private ?bool $estAtelier = null;
    private ?bool $estCreateurDaDirecte = null;
    private ?bool $estAppro = null;
    private ?bool $estFinance = null;
    private ?bool $estEnergie = null;

    public function __construct(UserDataService $dataService)
    {
        $this->dataService = $dataService;
    }

    public function getRouteCourrante(): ?string
    {
        return $this->routeCourrante;
    }

    /**
     * Get the value of dataService
     */
    public function getDataService(): UserDataService
    {
        return $this->dataService;
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
    public function controlerAcces(Request $request): ?\Symfony\Component\HttpFoundation\Response
    {
        $nomRoute = $request->attributes->get('_route');

        // Mémoriser la route pour les appels depuis les contrôleurs
        $this->routeCourrante = $nomRoute;

        // Route publique ou Route Export → laisse passer sans aucun contrôle
        if ($this->estRoutePublique($nomRoute) || $this->estRouteExport($nomRoute)) {
            return null;
        }

        // Routes API : contrôle JWT ou Session (Intranet)
        if ($this->estRouteApi($nomRoute)) {
            if ($nomRoute === 'api_login') {
                return null;
            }

            // Si l'utilisateur est déjà connecté par session PHP (appels internes JS), on autorise
            if ($this->dataService->isUserConnected()) {
                return null;
            }

            // Sinon, on exige un token JWT (appels externes, ex: React)
            $authHeader = $request->headers->get('Authorization');
            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'Accès refusé. Token manquant ou mal formaté'], 401);
            }

            $jwtService = new \App\Service\security\JwtService();
            $payload = $jwtService->decode($matches[1]);

            if (!$payload) {
                return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'Token invalide ou expiré'], 401);
            }

            return null; // Token valide, on laisse passer
        }

        // Non connecté → redirection vers login (avec URL de retour)
        if (!$this->dataService->isUserConnected()) {
            return new RedirectResponse($this->genererUrlConnexion());
        }

        // Connecté et route semi-privee → laisse passer
        if ($this->estRouteSemiPrivee($nomRoute)) {
            return null;
        }

        // Connecté mais profil non selectionné → redirection vers login
        if ($this->dataService->getProfilId() === null) {
            return new RedirectResponse($this->genererUrlConnexion());
        }

        // Si accueil
        if ($nomRoute === self::ROUTE_ACCUEIL) {
            return null;
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
     * Vérifie si l'utilisateur connecté est un administrateur par son profil
     */
    public function estAdmin(): bool
    {
        if ($this->estAdmin === null) {
            $this->estAdmin = $this->dataService->getProfilName() === Profil::HFF_ADMIN;
        }
        return $this->estAdmin;
    }

    /** 
     * Vérifie si l'utilisateur connecté est ATELIER par le fait qu'il peut créer un DIT (ie qui a accès à la page de création de DIT)
     */
    public function estAtelier(): bool
    {
        if ($this->estAtelier === null) {
            $this->estAtelier = $this->dataService->getCodeServiceUser() === 'ATE';
        }
        return $this->estAtelier;
    }

    /** 
     * Vérifie si l'utilisateur connecté est CREATEUR DA DIRECTE par le fait qu'il peut créer un DA DIRECTE (ie qui a accès à la page de création de DA)
     */
    public function estCreateurDaDirecte(): bool
    {
        if ($this->estCreateurDaDirecte === null) {
            $this->estCreateurDaDirecte = $this->verifierPermission(self::PERMISSION_VOIR, 'da_new_achat');
        }
        return $this->estCreateurDaDirecte;
    }

    /**
     * Vérifie si l'utilisateur connecté est APPRO par le fait de son agence et service par défaut (80 - APP)
     */
    public function estAppro(): bool
    {
        if ($this->estAppro === null) {
            $this->estAppro = $this->dataService->getCodeAgenceUser() === '80' && $this->dataService->getCodeServiceUser() === 'APP';
        }
        return $this->estAppro;
    }

    /**
     * Vérifie si l'utilisateur connecté est dans le service FINANCE par le fait de son agence et service par défaut (80 - FIN)
     */
    public function estFinance(): bool
    {
        if ($this->estFinance === null) {
            $this->estFinance = $this->dataService->getCodeAgenceUser() === '80' && $this->dataService->getCodeServiceUser() === 'FIN';
        }
        return $this->estFinance;
    }

    /**
     * Vérifie si l'utilisateur connecté est ENERGIE par le fait de son agence par défaut (90/91/92)
     */
    public function estEnergie(): bool
    {
        if ($this->estEnergie === null) {
            $this->estEnergie = in_array($this->dataService->getCodeAgenceUser(), ['90', '91', '92']);
        }
        return $this->estEnergie;
    }

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
     * Vérifie l'accès à une route
     */
    public function hasAccesRoute(string $route): bool
    {
        return $this->dataService->peutVoir($route);
    }

    /** 
     * Retourne la liste de tous les agences et services
     */
    public function getAllAgenceServices(): array
    {
        return $this->dataService->getAllAgenceService();
    }

    /**
     * Retourne la liste des agences et services groupés par id pour une application donnée
     */
    public function getAgenceServices(string $codeApp, bool $groupById = true): array
    {
        return $groupById ? $this->dataService->getAgenceServiceGroupById($codeApp) : $this->dataService->getAgenceServiceGroupByCode($codeApp);
    }

    /**
     * Retourne la liste des id de couple agence-service pour une application donnée
     */
    public function getAgenceServiceIds(string $codeApp): array
    {
        return array_keys($this->dataService->getAgenceServiceGroupById($codeApp));
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

    /** 
     * Retourne le code agence de l'utilisateur
     */
    public function getCodeAgenceUser(): ?string
    {
        return $this->dataService->getCodeAgenceUser();
    }

    /** 
     * Retourne le code service de l'utilisateur
     */
    public function getCodeServiceUser(): ?string
    {
        return $this->dataService->getCodeServiceUser();
    }

    /**
     * Retourne l'id de l'agence de l'utilisateur
     */
    public function getAgenceIdUser(): ?int
    {
        return $this->dataService->getAgenceIdUser();
    }

    /**
     * Retourne l'id du service de l'utilisateur
     */
    public function getServiceIdUser(): ?int
    {
        return $this->dataService->getServiceIdUser();
    }

    /** 
     * Retourne le code société de l'utilisateur
     */
    public function getCodeSocieteUser(): ?string
    {
        return $this->dataService->getCodeSociete();
    }

    /** 
     * Retourne le nom de l'utilisateur
     */
    public function getUserName(): ?string
    {
        return $this->dataService->getUserName();
    }

    /** 
     * Retourne le mail de l'utilisateur
     */
    public function getUserEmail(): ?string
    {
        return $this->dataService->getUserMail();
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

    private function estRouteExport(?string $nomRoute): bool
    {
        if ($nomRoute === null) return false;

        foreach (self::PREFIXES_EXPORT as $prefix) {
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
            self::PERMISSION_VOIR              => false,
            self::PERMISSION_AUTH_2            => false,
            self::PERMISSION_MULTI_SUCCURSALE  => false,
            self::PERMISSION_SUPPRIMER         => false,
            self::PERMISSION_EXPORTER          => false,
        ];
    }
}
