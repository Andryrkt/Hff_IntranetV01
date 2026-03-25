<?php

namespace App\Twig;

use App\Factory\BreadcrumbFactory;
use App\Service\navigation\MenuService;
use App\Service\security\SecurityService;
use App\Controller\Traits\lienGenerique;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Twig\Environment;

class BreadcrumbExtension extends AbstractExtension
{
    use lienGenerique;

    private MenuService $menuService;
    private SecurityService $securityService;
    private BreadcrumbFactory $breadcrumbFactory;
    private TagAwareCacheInterface $cache;
    private ?array $cacheBreadcrumbs = null;

    public function __construct(MenuService $menuService, SecurityService $securityService, TagAwareCacheInterface $cache)
    {
        $baseUrl                 = $this->urlGenerique($_ENV['BASE_PATH_COURT']);
        $this->securityService   = $securityService;
        $this->menuService       = $menuService;
        $this->cache             = $cache;
        $this->breadcrumbFactory = new BreadcrumbFactory($baseUrl, $menuService);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('breadcrumbs',     [$this, 'generateBreadcrumbs']),
            new TwigFunction('navigationAdmin', [$this, 'generateNavigationAdmin']),
            new TwigFunction('hasAcces',        [$this, 'hasAcces']),
            new TwigFunction('renderMenuModals', [$this, 'renderMenuModals'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    public function generateBreadcrumbs(): array
    {
        if ($this->cacheBreadcrumbs === null) {
            $this->cacheBreadcrumbs = $this->breadcrumbFactory->createFromCurrentUrl($this->securityService->getRouteCourrante());
        }
        return $this->cacheBreadcrumbs;
    }

    /**
     * Rend les modals du menu avec mise en cache persistante par profil.
     * Cette fonction met en cache le code HTML final (rendu par Twig).
     */
    public function renderMenuModals(Environment $twig): string
    {
        $profilId = $this->securityService->getProfilId();
        if ($profilId === null) {
            return '';
        }

        // Clé de cache liée au profil et à la version du menu
        $cacheKey = 'rendered_menu_modals_profil_' . $profilId;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($twig) {
            // Le cache expire après 1 heure, ou peut être invalidé par tag
            $item->expiresAfter(3600);
            $item->tag(['menu_modals', 'profil_' . $this->securityService->getProfilId()]);

            return $twig->render('partials/_menu_modals.html.twig');
        });
    }

    /**
     * Retourne les groupes de liens du menu admin, filtrés par accès.
     * Utilisé dans _navigation.html.twig pour construire le dropdown Administrateur.
     */
    public function generateNavigationAdmin(): array
    {
        return $this->breadcrumbFactory->createAdminNavigation();
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
