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
            return $this->createBreadcrumbFromPath($accueil);
        }

        // ─── Construit les miettes depuis le chemin ───────────────────────────
        $breadcrumbs = [$accueil];
        $dernierIndex = count($chemin) - 1;

        foreach ($chemin as $index => $etape) {
            $isLast        = ($index === $dernierIndex);
            $breadcrumbs[] = [
                'title'     => $etape['title'],
                'icon'      => $etape['icon'],
                'is_active' => $isLast,
            ];
        }

        return $breadcrumbs;
    }

    // =========================================================================
    //  NAVIGATION ADMIN — menu latéral / dropdown Administrateur
    // =========================================================================

    /**
     * Construit la navigation Admin pour la requête courante.
     * Délègue à MenuService le filtrage des accès (peutVoir).
     */
    public function createAdminNavigation(): array
    {
        return $this->menuService->getAdminMenuStructure();
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

    private function createBreadcrumbFromPath(array $accueil): array
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // 1. Découper + nettoyer
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        // 2. Supprimer le premier élément
        array_shift($segments);

        // 3. Filtrer les segments non numériques
        $segments = array_values(array_filter($segments, function ($segment) {
            return !is_numeric($segment) && !preg_match('/\d$/', $segment);
        }));

        $breadcrumbs = [$accueil];

        // 4. Construire les breadcrumbs
        foreach ($segments as $segment) {
            $breadcrumbs[] = [
                'title'     => $this->formatLabel($segment),
                'icon'      => $this->getIconForSegment($segment),
                'is_active' => false,
            ];
        }

        // 5. Mettre le dernier en actif
        if (count($breadcrumbs) > 0) $breadcrumbs[count($breadcrumbs) - 1]['is_active'] = true;

        return $breadcrumbs;
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
            'dom-list-annuler'                      => 'Consultation liste DOM annulés',
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
            'conge-liste'                           => ' Liste des demandes de congés'
        ];

        $cleanSegment = str_replace(['-', '_'], ' ', $segment);
        return $specialLabels[$segment] ?? ucwords($cleanSegment);
    }

    private function getIconForSegment(string $segment): string
    {
        $iconMapping = [
            'accueil'               => 'fas fa-home',
            'rh'                    => 'fas fa-users',
            'ordre-de-mission'      => 'fas fa-file-signature',
            'dom-list-annuler'      => 'fas fa-search',
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
            'maintenance'           => 'fas fa-wrench'
        ];

        return $iconMapping[$segment] ?? 'fas fa-folder';
    }
}
