<?php

namespace App\Factory;

use App\Service\navigation\MenuService;

class BreadcrumbFactory
{
    private string $baseUrl;
    private MenuService $menuService;

    public function __construct(string $baseUrl, MenuService $menuService)
    {
        $this->baseUrl      = rtrim($baseUrl, '/');
        $this->menuService  = $menuService;
    }

    /**
     * Construit le fil d'ariane pour la requête courante.
     */
    public function createFromCurrentUrl(?string $nomRoute): array
    {
        // ─── Item Accueil avec dropdown ───────────────────────────────────────
        $modules  = $this->menuService->getMenuStructure();
        $accueil  = [
            'title'     => 'Accueil',
            'link'      => $this->baseUrl ?: '/',
            'icon'      => 'fas fa-home',
            'is_active' => false,
            'dropdown'  => $this->buildDropdownAccueil($modules),
        ];

        // ─── Pas de route connue → breadcrumb minimal ────────────────────────
        if ($nomRoute === null) {
            return [$accueil];
        }

        // ─── Cherche le chemin dans l'arbre MenuService ───────────────────────
        $chemin = $this->menuService->findChemin($nomRoute);

        if (empty($chemin)) {
            // Route non trouvée dans le menu (page admin, etc.) → Accueil seul
            return [$accueil];
        }

        // ─── Construit les miettes depuis le chemin ───────────────────────────
        $breadcrumbs = [$accueil];
        $dernierIndex = count($chemin) - 1;

        foreach ($chemin as $index => $etape) {
            $isLast        = ($index === $dernierIndex);
            $breadcrumbs[] = [
                'title'     => $etape['title'],
                'link'      => $isLast ? null : null, // items intermédiaires non cliquables
                'icon'      => $etape['icon'],
                'is_active' => $isLast,
            ];
        }

        return $breadcrumbs;
    }

    // =========================================================================
    //  DROPDOWN ACCUEIL — même structure que les vignettes de la page d'accueil
    // =========================================================================

    /**
     * Construit le dropdown de l'item Accueil.
     * Chaque entrée correspond à un module du menu avec ses items complets,
     * pour que les modals fonctionnent exactement comme sur la page d'accueil.
     */
    private function buildDropdownAccueil(array $modules): array
    {
        return array_map(fn(array $module) => [
            'id'    => $module['id'],
            'title' => $module['title'],
            'icon'  => $module['icon'],
            'link'  => '#',
            'items' => $module['items'],   // conservés pour les modals
        ], $modules);
    }
}
