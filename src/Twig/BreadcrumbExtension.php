<?php

namespace App\Twig;

use App\Factory\BreadcrumbFactory;
use App\Service\navigation\MenuService;
use App\Service\security\SecurityService;
use App\Controller\Traits\lienGenerique;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class BreadcrumbExtension extends AbstractExtension
{
    use lienGenerique;

    private MenuService $menuService;
    private SecurityService $securityService;
    private string $baseUrl;

    public function __construct(MenuService $menuService, SecurityService $securityService)
    {
        $this->menuService    = $menuService;
        $this->securityService = $securityService;
        $this->baseUrl        = $this->urlGenerique($_ENV['BASE_PATH_COURT']);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('breadcrumbs',     [$this, 'generateBreadcrumbs']),
            new TwigFunction('navigationAdmin', [$this, 'generateNavigationAdmin']),
            new TwigFunction('hasAcces',        [$this, 'hasAcces']),
        ];
    }

    public function generateBreadcrumbs(): array
    {
        return (new BreadcrumbFactory($this->baseUrl, $this->menuService))
            ->createFromCurrentUrl($this->securityService->getRouteCourrante());
    }

    /**
     * Retourne les groupes de liens du menu admin, filtrés par accès.
     * Utilisé dans _navigation.html.twig pour construire le dropdown Administrateur.
     */
    public function generateNavigationAdmin(): array
    {
        return (new BreadcrumbFactory($this->baseUrl, $this->menuService))
            ->createAdminNavigation();
    }

    /**
     * Vérifie si une route est accessible (peutVoir) pour le profil connecté.
     * Utilisé dans les templates Twig pour des vérifications ponctuelles.
     */
    public function hasAcces(string $nomRoute): bool
    {
        return $this->securityService->verifierPermission(
            SecurityService::PERMISSION_VOIR,
            $nomRoute
        );
    }
}
