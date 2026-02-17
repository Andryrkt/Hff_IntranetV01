<?php

namespace App\Factory;

use App\Service\navigation\MenuService;

class BreadcrumbFactory
{
    private string $baseUrl;
    private MenuService $menuService;

    /**
     * Cache de la config menu pour la requête courante.
     * getMenuStructure() peut être appelé plusieurs fois (breadcrumb + vignettes) :
     * on ne le calcule qu'une seule fois.
     */
    private ?array $menuConfig = null;

    public function __construct(string $baseUrl = '/', MenuService $menuService)
    {
        $this->baseUrl     = rtrim($baseUrl, '/');
        $this->menuService = $menuService;
    }

    public function createFromCurrentUrl(): array
    {
        $path     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = array_filter(explode('/', trim($path, '/')));

        // ─── Accueil ──────────────────────────────────────────────────────────
        $homeItem  = $this->createItem('Accueil', $this->baseUrl ?: '/', false, 'fas fa-home');
        $config    = $this->getMenuConfig();

        // Le premier item (Accueil) reçoit un dropdown avec tous les modules
        if (!empty($config)) {
            $homeItem['dropdown'] = $this->buildDropdownFromMenu($path, $config);
        }

        $breadcrumbs = [$homeItem];

        // ─── Segments de l'URL ───────────────────────────────────────────────
        foreach ($segments as $index => $segment) {
            // Ignorer le premier segment (base path), les IDs numériques et les segments finissant par un chiffre
            if ($index == 0 || is_numeric($segment) || preg_match('/\d$/', $segment)) {
                continue;
            }

            $isLast = ($index === array_key_last($segments));
            $slug   = strtolower($segment);
            $item   = $this->createItem(
                $this->formatLabel($segment),
                $isLast ? null : '#',
                $isLast,
                $this->getIconForSegment($segment)
            );

            // Dropdown sur le segment si un sous-menu existe pour ce slug
            $subMenu = $this->findSubMenuBySlug($slug, $config);
            if ($subMenu !== null) {
                $item['dropdown'] = $this->buildSubItemsDropdown($path, $subMenu);
            }

            $breadcrumbs[] = $item;
        }

        return $breadcrumbs;
    }

    // =========================================================================
    //  CONSTRUCTION DES DROPDOWNS
    // =========================================================================

    /**
     * Construit le dropdown de l'Accueil : liste tous les modules du menu.
     */
    private function buildDropdownFromMenu(string $currentPath, array $menuModules): array
    {
        $dropdown = [];
        foreach ($menuModules as $module) {
            $dropdown[] = [
                'id'       => $module['id'],
                'title'    => $module['title'],
                'link'     => '#',
                'icon'     => $module['icon'],
                'is_active' => false,
                'items'    => $module['items'],
            ];
        }
        return $dropdown;
    }

    /**
     * Construit le dropdown d'un segment : items du sous-menu correspondant.
     */
    private function buildSubItemsDropdown(string $currentPath, array $subMenu): array
    {
        return array_map(function ($item) use ($currentPath) {
            $link = $item['link'] ?? null;
            return [
                'id'          => $item['id'] ?? null,
                'title'       => $item['title'],
                'link'        => $link,
                'icon'        => $item['icon'] ?? '',
                'is_active'   => ($link === $currentPath),
                'routeParams' => $item['routeParams'] ?? [],
                'items'       => $item['items'] ?? [],
            ];
        }, $subMenu);
    }

    /**
     * Trouve le sous-menu d'un module par son slug (nom en minuscules).
     * Ex : 'magasin' → items du module Magasin.
     * Retourne null si aucun module ne correspond.
     */
    private function findSubMenuBySlug(string $slug, array $menuModules): ?array
    {
        foreach ($menuModules as $module) {
            if (strtolower($module['title']) === $slug) {
                return $module['items'];
            }
        }
        return null;
    }

    // =========================================================================
    //  HELPERS
    // =========================================================================

    private function getMenuConfig(): array
    {
        if ($this->menuConfig === null) {
            $this->menuConfig = $this->menuService->getMenuStructure();
        }
        return $this->menuConfig;
    }

    private function createItem(string $label, ?string $url, bool $isActive, string $icon): array
    {
        return [
            'title'     => $label,
            'link'      => $url,
            'icon'      => $icon,
            'is_active' => $isActive,
        ];
    }

    private function formatLabel(string $segment): string
    {
        $specialLabels = [
            'new'                                   => 'Nouvelle demande',
            'liste'                                 => 'Consultation',
            'detail'                                => 'Fiche détail',
            'edit'                                  => 'Modification',
            'demande-dintervention'                 => 'Demande d\'intervention',
            'admin'                                 => 'Administration',
            'list-agence'                           => 'Liste des Agences',
            'badm-form1'                            => 'Création BADM - Étape 1',
            'badm-form2'                            => 'Création BADM - Étape 2',
            'cas-form1'                             => 'Création Casier - Étape 1',
            'cas-form2'                             => 'Création Casier - Étape 2',
            'dom-first-form'                        => 'Création DOM - Étape 1',
            'dom-second-form'                       => 'Création DOM - Étape 2',
            'annulation-conges'                     => 'Annulation de congés validés',
            'annulation-conges-rh'                  => 'Annulation de congé dédiée RH',
            'list-dit'                              => 'Sélection de DIT',
            'da-first-form'                         => 'Sélection de choix',
            'new-avec-dit'                          => 'Création DA avec DIT',
            'new-da-direct'                         => 'Création DA directe',
            'new-da-reappro'                        => 'Création DA réappro',
            'edit-avec-dit'                         => 'Modification DA avec DIT',
            'edit-direct'                           => 'Modification DA directe',
            'proposition-avec-dit'                  => 'Proposition / Validation DA avec DIT',
            'proposition-direct'                    => 'Proposition / Validation DA directe',
            'detail-avec-dit'                       => 'Fiche détail DA avec DIT',
            'detail-direct'                         => 'Fiche détail DA directe',
            'da-list'                               => 'Liste des demandes d\'achat',
            'da-list-cde-frn'                       => 'Liste des commandes fournisseurs',
            'soumission-bc'                         => 'Soumission Bon de Commande',
            'soumission-facbl'                      => 'Soumission Facture / Bon de Livraison',
            'cde-fournisseur'                       => 'Soumission Commande Fournisseur',
            'dossierRegul'                          => 'Dossier de régulation',
            'dit-liste'                             => 'Liste des DIT',
            'dw-intervention-atelier-avec-dit'      => 'Dossier du DIT',
            'dit-dossier-intervention-atelier'      => 'Dossier DIT',
            'ditValidation'                         => 'Validation de DIT',
            'natemadit'                             => 'DIT NATEMA',
            'ac-bc-soumis'                          => 'Accusé de réception / Bon de commande',
            'soumission-or'                         => 'Soumission - Ordre de Réparation',
            'soumission-ri'                         => 'Soumission - Rapport d\'intervention',
            'trop-percu'                            => 'DOM Trop perçu',
            'sortie-de-pieces-lubs'                 => 'Sortie de pièces',
            'bl-soumission'                         => 'Soumission Bon de Livraison',
            'cis-liste-a-livrer'                    => 'Liste des CIS à livrer',
            'cis-liste-a-traiter'                   => 'Liste des CIS à traiter',
            'inventaire_detail'                     => 'Liste détaillée des inventaires',
            'inventaire-ctrl'                       => 'Liste des inventaires',
            'detailInventaire'                      => 'Fiche détail',
            'liste_cde_frs_non_generer'             => 'Liste des commandes fournisseurs non générées',
            'liste-commande-fournisseur-non-placer' => 'Liste des commandes fournisseurs non placées',
            'liste-or-livrer'                       => 'Liste des OR à livrer',
            'liste-magasin'                         => 'Liste des OR à traiter',
            'planning-vue'                          => 'Planning des OR',
            'planning-detaille'                     => 'Planning détaillé',
            'planningAtelier'                       => 'Planning Interne de l\'Atelier',
            'planningAte'                           => 'Planning',
            'demande-de-conge'                      => 'Demande de congé',
            'conge-liste'                           => 'Liste des demandes de congés',
        ];

        $cleanSegment = str_replace(['-', '_'], ' ', $segment);
        return $specialLabels[$segment] ?? ucwords($cleanSegment);
    }

    private function getIconForSegment(string $segment): string
    {
        $iconMapping = [
            'accueil'               => 'fas fa-home',
            'atelier'               => 'fas fa-tools',
            'demande-dintervention' => 'fas fa-clipboard-list',
            'demandes'              => 'fas fa-list-alt',
            'planning'              => 'fas fa-calendar-alt',
            'new'                   => 'fas fa-plus-circle',
            'history'               => 'fas fa-history',
            'edit'                  => 'fas fa-edit',
            'show'                  => 'fas fa-eye',
            'delete'                => 'fas fa-trash',
            'settings'              => 'fas fa-cog',
            'users'                 => 'fas fa-users',
            'profile'               => 'fas fa-user',
            'reports'               => 'fas fa-chart-bar',
            'dashboard'             => 'fas fa-tachometer-alt',
            'documents'             => 'fas fa-file-alt',
            'messages'              => 'fas fa-envelope',
            'notifications'         => 'fas fa-bell',
            'admin'                 => 'fas fa-shield-alt',
            'maintenance'           => 'fas fa-wrench',
        ];

        return $iconMapping[$segment] ?? 'fas fa-folder';
    }
}
