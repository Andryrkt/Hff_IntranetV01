<?php

namespace App\Service\navigation;

use App\Service\security\SecurityService;

class MenuService
{
    private SecurityService $securityService;
    private string $basePath;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
        $this->basePath        = $_ENV['BASE_PATH_FICHIER_COURT'];
    }

    // =========================================================================
    //  API PUBLIQUE
    // =========================================================================

    /**
     * Retourne la structure du menu : uniquement les modules ayant au moins un item accessible.
     */
    public function getMenuStructure(): array
    {
        $vignettes = [];

        $modules = [
            fn() => $this->menuDocumentation(),
            fn() => $this->menuReportingBI(),
            fn() => $this->menuCompta(),
            fn() => $this->menuRH(),
            fn() => $this->menuMateriel(),
            fn() => $this->menuAtelier(),
            fn() => $this->menuMagasin(),
            fn() => $this->menuAppro(),
            fn() => $this->menuIT(),
            fn() => $this->menuPOL(),
            fn() => $this->menuEnergie(),
            fn() => $this->menuHSE(),
        ];

        foreach ($modules as $factory) {
            $menu = $factory();
            if (!empty($menu['items'])) {
                $vignettes[] = $menu;
            }
        }

        return $vignettes;
    }

    // =========================================================================
    //  API PUBLIQUE — MENU ADMIN
    // =========================================================================

    /**
     * Retourne la structure du menu Administrateur, filtrée par peutVoir.
     * Chaque groupe n'est inclus que s'il contient au moins un lien accessible.
     */
    public function getAdminMenuStructure(): array
    {
        $groupes = $this->adminMenuGroupes();
        $resultat = [];

        foreach ($groupes as $groupe) {
            $linksAccessibles = array_values(array_filter(
                $groupe['links'],
                fn(array $link) => $this->hasAccesRoute($link['route'])
            ));

            if (!empty($linksAccessibles)) {
                $resultat[] = [
                    'header' => $groupe['header'],
                    'icon'   => $groupe['icon'],
                    'links'  => $linksAccessibles,
                ];
            }
        }

        return $resultat;
    }

    /**
     * Définition statique des groupes et liens du menu Admin.
     * Modifiez ici pour ajouter / réordonner des entrées admin.
     */
    private function adminMenuGroupes(): array
    {
        return [
            [
                'header' => 'Accès & Sécurité',
                'icon'   => 'fa-user-shield',
                'links'  => [
                    ['label' => 'Utilisateurs',              'icon' => 'fa-user',        'route' => 'utilisateur_index'],
                    ['label' => 'Profils ( ~ Applications)', 'icon' => 'fa-users-gear',  'route' => 'profil_index'],
                    ['label' => 'Droits et permissions',     'icon' => 'fa-key',         'route' => 'permission_index'],
                ],
            ],
            [
                'header' => 'Applications & Intégrations',
                'icon'   => 'fa-cubes',
                'links'  => [
                    ['label' => 'Pages',                       'icon' => 'fa-globe',       'route' => 'page_hff_index'],
                    ['label' => 'Applications ( ~ Pages)',     'icon' => 'fa-layer-group', 'route' => 'application_index'],
                    ['label' => 'Vignettes ( ~ Applications)', 'icon' => 'fa-clone',       'route' => 'vignette_index'],
                ],
            ],
            [
                'header' => 'Organisation',
                'icon'   => 'fa-sitemap',
                'links'  => [
                    ['label' => 'Sociétés',              'icon' => 'fa-building',     'route' => 'societte_index'],
                    ['label' => 'Services',              'icon' => 'fa-briefcase',    'route' => 'service_index'],
                    ['label' => 'Agences ( ~ Services)', 'icon' => 'fa-city',         'route' => 'agence_index'],
                    ['label' => 'Personnels',            'icon' => 'fa-id-card',      'route' => 'personnel_index'],
                    ['label' => "Contacts d'agence",     'icon' => 'fa-address-book', 'route' => 'contact_agence_ate_index'],
                ],
            ],
            [
                'header' => 'Historique',
                'icon'   => 'fa-clock-rotate-left',
                'links'  => [
                    ['label' => 'Consultation de pages',     'icon' => 'fa-eye',               'route' => 'consultation_page_index'],
                    ['label' => 'Historique des opérations', 'icon' => 'fa-file-circle-check',  'route' => 'operation_document_index'],
                ],
            ],
            [
                'header' => 'Tickets',
                'icon'   => 'fa-ticket',
                'links'  => [
                    ['label' => 'Toutes les catégories', 'icon' => 'fa-list', 'route' => 'tki_all_categorie_index'],
                ],
            ],
        ];
    }

    // =========================================================================
    //  CONSTRUCTION DES MENUS
    //
    //  Règle stricte :
    //  - hasAccesModule(r1, r2, ...) → décide si le BLOC PARENT s'affiche.
    //    On ne revérifie PAS les routes à l'intérieur du bloc.
    //  - hasAccesRoute(r) → décide si un ITEM INDIVIDUEL s'affiche,
    //    uniquement quand les routes d'un même bloc ont des droits différents.
    //  - Un bloc avec une seule route utilise directement hasAccesRoute(),
    //    pas hasAccesModule().
    // =========================================================================

    public function menuDocumentation(): array
    {
        $subitems = [];

        // Un seul accès contrôle tout le bloc documentation générale
        if ($this->hasAccesRoute('documentation_interne')) {
            $subitems[] = $this->createSimpleItem('Annuaire', 'address-book', '#');
            $subitems[] = $this->createSimpleItem('Plan analytique HFF', 'ruler-vertical', "{$this->basePath}/documentation/Structure%20analytique%20HFF.pdf", [], '_blank');
            $subitems[] = $this->createSimpleItem('Documentation interne', 'folder-tree', 'documentation_interne');
        }

        // Contrat : accès indépendant
        if ($this->hasAccesRoute('new_contrat')) {
            $subitems[] = $this->createSubMenuItem('Contrat', 'file-contract', [
                $this->createSubItem('Nouveau contrat', 'plus-circle', 'new_contrat', [], '_blank'),
                $this->createSubItem('Consultation', 'search', '#'),
            ]);
        }

        return $this->createMenuItem('documentationModal', 'Documentation', 'book', $subitems);
    }

    public function menuReportingBI(): array
    {
        // Un seul accès contrôle tout le module
        if (!$this->hasAccesRoute('da_reporting_ips')) {
            return $this->createMenuItem('reportingModal', 'Reporting', 'chart-line', []);
        }

        return $this->createMenuItem('reportingModal', 'Reporting', 'chart-line', [
            $this->createSimpleItem('Reporting Power BI', null, '#'),
            $this->createSimpleItem('Reporting Excel', null, '#'),
        ]);
    }

    public function menuCompta(): array
    {
        $subitems = [];

        if ($this->hasAccesRoute('ddp_liste')) {
            $subitems[] = $this->createSimpleItem('Cours de change', 'money-bill-wave');
            $subitems[] = $this->createSubMenuItem('Demande de paiement', 'file-invoice-dollar', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', '#', [], '', 'modalTypeDemande', true),
                $this->createSubItem('Consultation', 'search', 'ddp_liste'),
            ]);
        }

        // Bon de caisse : accès indépendant de DDP
        if ($this->hasAccesModule('new_bon_caisse', 'bon_caisse_liste')) {
            $subitems[] = $this->createSubMenuItem('Bon de caisse', 'receipt', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'new_bon_caisse'),
                $this->createSubItem('Consultation', 'search', 'bon_caisse_liste'),
            ]);
        }

        return $this->createMenuItem('comptaModal', 'Compta', 'calculator', $subitems);
    }

    public function menuRH(): array
    {
        $subitems = [];

        // Ordre de mission : le bloc s'affiche si au moins une des deux routes est accessible
        if ($this->hasAccesModule('dom_first_form', 'doms_liste')) {
            $subSubitems = [];
            if ($this->hasAccesRoute('dom_first_form')) {
                $subSubitems[] = $this->createSubItem('Nouvelle demande', 'plus-circle', 'dom_first_form');
            }
            // doms_liste est la condition du hasAccesModule : forcément accessible si on est ici
            $subSubitems[] = $this->createSubItem('Consultation', 'search', 'doms_liste');
            $subitems[]    = $this->createSubMenuItem('Ordre de mission', 'file-signature', $subSubitems);
        }

        // Mutations : les deux routes ont le même niveau d'accès, le bloc suffit
        if ($this->hasAccesModule('mutation_nouvelle_demande', 'mutation_liste')) {
            $subitems[] = $this->createSubMenuItem('Mutations', 'user-friends', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'mutation_nouvelle_demande'),
                $this->createSubItem('Consultation', 'search', 'mutation_liste'),
            ]);
        }

        // Congés : plusieurs routes avec des droits indépendants → vérification par item
        if ($this->hasAccesModule('new_conge', 'annulation_conge', 'annulation_conge_rh', 'conge_liste')) {
            $subSubitems = [];
            if ($this->hasAccesRoute('new_conge')) {
                $subSubitems[] = $this->createSubItem('Nouvelle demande', 'plus-circle', 'new_conge', [], '_blank');
            }
            if ($this->hasAccesRoute('annulation_conge')) {
                $subSubitems[] = $this->createSubItem('Annulation de congés validés', 'calendar-xmark', 'annulation_conge', [], '_blank');
            }
            if ($this->hasAccesRoute('annulation_conge_rh')) {
                $subSubitems[] = $this->createSubItem('Annulation de congé dédiée RH', 'calendar-xmark', 'annulation_conge_rh', [], '_blank');
            }
            if ($this->hasAccesRoute('conge_liste')) {
                $subSubitems[] = $this->createSubItem('Consultation', 'search', 'conge_liste');
            }
            $subitems[] = $this->createSubMenuItem('Congés', 'umbrella-beach', $subSubitems);
        }

        // Temporaires : accès unique contrôle tout le bloc
        if ($this->hasAccesRoute('temporaires_liste')) {
            $subitems[] = $this->createSubMenuItem('Temporaires', 'user-clock', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', '#'),
                $this->createSubItem('Consultation', 'search', '#'),
            ]);
        }

        return $this->createMenuItem('rhModal', 'RH', 'users', $subitems);
    }

    public function menuMateriel(): array
    {
        $subitems = [];

        // Logistique : accès unique contrôle tout le bloc
        if ($this->hasAccesRoute('new_logistique')) {
            $subitems[] = $this->createSubMenuItem('Logistique', 'truck-fast', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'new_logistique'),
            ]);
        }

        // Mouvement matériel : le bloc suffit, droits identiques sur les deux routes
        if ($this->hasAccesModule('badms_newForm1', 'badmListe_AffichageListeBadm')) {
            $subitems[] = $this->createSubMenuItem('Mouvement matériel', 'exchange-alt', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'badms_newForm1'),
                $this->createSubItem('Consultation', 'search', 'badmListe_AffichageListeBadm'),
            ]);
        }

        // Casier : le bloc suffit, droits identiques sur les deux routes
        if ($this->hasAccesModule('casier_nouveau', 'listeTemporaire_affichageListeCasier')) {
            $subitems[] = $this->createSubMenuItem('Casier', 'box-open', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'casier_nouveau'),
                $this->createSubItem('Consultation', 'search', 'listeTemporaire_affichageListeCasier'),
            ]);
        }

        return $this->createMenuItem('materielModal', 'Matériel', 'snowplow', $subitems);
    }

    public function menuAtelier(): array
    {
        $subitems = [];

        // DIT : création et consultation ont des droits potentiellement différents → vérification par item
        if ($this->hasAccesModule('dit_new', 'dit_index', 'dit_dossier_intervention_atelier')) {
            $subSubitems = [];
            if ($this->hasAccesRoute('dit_new')) {
                $subSubitems[] = $this->createSubItem('Nouvelle demande', 'plus-circle', 'dit_new');
            }
            if ($this->hasAccesRoute('dit_index')) {
                $subSubitems[] = $this->createSubItem('Consultation', 'search', 'dit_index');
            }
            // dit_dossier et glossaire : accessibles si le bloc DIT est accessible
            $subSubitems[] = $this->createSubItem('Dossier DIT', 'folder', 'dit_dossier_intervention_atelier');
            $subSubitems[] = $this->createSubItem('Matrice des responsabilités', 'table', "{$this->basePath}/documentation/MATRICE DE RESPONSABILITES OR v9.xlsx");
            $subitems[]    = $this->createSubMenuItem('Demande d\'intervention', 'toolbox', $subSubitems);
            $subitems[]    = $this->createSimpleItem('Glossaire OR', 'book', "{$this->basePath}/dit/glossaire_or/Glossaire_OR.pdf", [], '_blank');
        }

        // REP : le bloc suffit, planning et planning détaillé ont les mêmes droits
        if ($this->hasAccesModule('planning_vue', 'liste_planning')) {
            $subitems[] = $this->createSimpleItem('Planning', 'calendar-alt', 'planning_vue', ['action' => 'oui']);
            $subitems[] = $this->createSimpleItem('Planning détaillé', 'calendar-day', 'liste_planning', ['action' => 'oui']);
        }

        // PAT : accès unique contrôle tout le bloc
        if ($this->hasAccesRoute('planningAtelier_vue')) {
            $subitems[] = $this->createSimpleItem('Planning interne Atelier', 'calendar-alt', 'planningAtelier_vue');
        }

        return $this->createMenuItem('atelierModal', 'Atelier', 'tools', $subitems);
    }

    public function menuMagasin(): array
    {
        $subitems = [];

        // OR : le bloc suffit, droits identiques sur les deux routes
        if ($this->hasAccesModule('magasinListe_index', 'magasinListe_or_Livrer')) {
            $subitems[] = $this->createSubMenuItem('OR', 'warehouse', [
                $this->createSubItem('Liste à traiter', 'tasks', 'magasinListe_index'),
                $this->createSubItem('Liste à livrer', 'truck-loading', 'magasinListe_or_Livrer'),
            ]);
        }

        // CIS : le bloc suffit, droits identiques sur les deux routes
        if ($this->hasAccesModule('cis_liste_a_traiter', 'cis_liste_a_livrer')) {
            $subitems[] = $this->createSubMenuItem('CIS', 'pallet', [
                $this->createSubItem('Liste à traiter', 'tasks', 'cis_liste_a_traiter'),
                $this->createSubItem('Liste à livrer', 'truck-loading', 'cis_liste_a_livrer'),
            ]);
        }

        // Inventaire : le bloc suffit, droits identiques sur les deux routes
        if ($this->hasAccesModule('liste_inventaire', 'liste_detail_inventaire')) {
            $subitems[] = $this->createSubMenuItem('INVENTAIRE', 'file-alt', [
                $this->createSubItem('Liste inventaire', 'file-alt', 'liste_inventaire', ['action' => 'oui']),
                $this->createSubItem('Inventaire détaillé', 'file-alt', 'liste_detail_inventaire'),
            ]);
        }

        // Sortie de pièces : accès unique contrôle tout le bloc
        if ($this->hasAccesRoute('bl_soumission')) {
            $subitems[] = $this->createSubMenuItem('SORTIE DE PIECES', 'arrow-left', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'bl_soumission'),
            ]);
        }

        // Dématérialisation : le bloc suffit, droits identiques sur les deux routes
        if ($this->hasAccesModule('devis_magasin_liste', 'interface_planningMag')) {
            $subitems[] = $this->createSubMenuItem('DEMATERIALISATION', 'cloud-arrow-up', [
                $this->createSubItem('Devis', 'file-invoice', 'devis_magasin_liste'),
                $this->createSubItem('Planning de commande Magasin', 'calendar-alt', 'interface_planningMag'),
            ]);
        }

        // Items simples : chacun a son propre accès
        if ($this->hasAccesRoute('cde_fournisseur')) {
            $subitems[] = $this->createSimpleItem('Soumission commandes fournisseur', 'list-alt', 'cde_fournisseur');
        }
        if ($this->hasAccesRoute('liste_Cde_Frn_Non_Placer')) {
            $subitems[] = $this->createSimpleItem('Liste des cmds non placées', 'exclamation-circle', 'liste_Cde_Frn_Non_Placer');
        }

        return $this->createMenuItem('magasinModal', 'Magasin', 'dolly', $subitems);
    }

    public function menuAppro(): array
    {
        $subitems = [];

        // Nouvelle DA : accès indépendant de la consultation
        if ($this->hasAccesRoute('da_first_form')) {
            $subitems[] = $this->createSimpleItem('Nouvelle DA', 'file-alt', 'da_first_form');
        }
        if ($this->hasAccesRoute('list_da')) {
            $subitems[] = $this->createSimpleItem('Consultation des DA', 'search', 'list_da');
        }
        if ($this->hasAccesRoute('da_list_cde_frn')) {
            $subitems[] = $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul', 'da_list_cde_frn');
        }
        if ($this->hasAccesRoute('da_reporting_ips')) {
            $subitems[] = $this->createSimpleItem('Reporting IPS DA reappro', 'chart-bar', 'da_reporting_ips');
        }

        return $this->createMenuItem('approModal', 'Appro', 'shopping-cart', $subitems);
    }

    public function menuIT(): array
    {
        $subitems = [];

        if ($this->hasAccesRoute('demande_support_informatique')) {
            $subitems[] = $this->createSimpleItem('Nouvelle Demande', 'plus-circle', 'demande_support_informatique');
        }
        if ($this->hasAccesRoute('liste_tik_index')) {
            $subitems[] = $this->createSimpleItem('Consultation', 'search', 'liste_tik_index');
        }
        if ($this->hasAccesRoute('tik_calendar_planning')) {
            $subitems[] = $this->createSimpleItem('Planning', 'file-alt', 'tik_calendar_planning');
        }

        return $this->createMenuItem('itModal', 'IT', 'laptop-code', $subitems);
    }

    public function menuPOL(): array
    {
        $subitems = [];

        // OR POL : le bloc suffit, droits identiques sur les deux routes
        if ($this->hasAccesModule('pol_or_liste_a_traiter', 'pol_or_liste_a_livrer')) {
            $subitems[] = $this->createSubMenuItem('OR', 'warehouse', [
                $this->createSubItem('Liste à traiter', 'tasks', 'pol_or_liste_a_traiter'),
                $this->createSubItem('Liste à livrer', 'truck-loading', 'pol_or_liste_a_livrer'),
            ]);
        }

        // CIS POL : le bloc suffit, droits identiques sur les deux routes
        if ($this->hasAccesModule('pol_cis_liste_a_traiter', 'pol_cis_liste_a_livrer')) {
            $subitems[] = $this->createSubMenuItem('CIS', 'pallet', [
                $this->createSubItem('Liste à traiter', 'tasks', 'pol_cis_liste_a_traiter'),
                $this->createSubItem('Liste à livrer', 'truck-loading', 'pol_cis_liste_a_livrer'),
            ]);
        }

        if ($this->hasAccesRoute('devis_magasin_pol_liste')) {
            $subitems[] = $this->createSimpleItem('Devis negoce pol', 'list-ul', 'devis_magasin_pol_liste');
        }

        return $this->createMenuItem('polModal', 'POL', 'ring rotate-90', $subitems);
    }

    public function menuEnergie(): array
    {
        $subitems = [];

        if ($this->hasAccesRoute('energie_rapport_production')) {
            $subitems[] = $this->createSimpleItem('Rapport de production centrale');
        }

        return $this->createMenuItem('energieModal', 'Energie', 'bolt', $subitems);
    }

    public function menuHSE(): array
    {
        $subitems = [];

        if ($this->hasAccesRoute('hse_rapport_incident')) {
            $subitems[] = $this->createSimpleItem('Rapport d\'incident');
        }
        if ($this->hasAccesRoute('hse_documentation')) {
            $subitems[] = $this->createSimpleItem('Documentation');
        }

        return $this->createMenuItem('hseModal', 'HSE', 'shield-alt', $subitems);
    }

    // =========================================================================
    //  NAVIGATION — recherche du chemin vers une route
    // =========================================================================

    /**
     * Retourne le chemin hiérarchique vers la route donnée dans l'arbre du menu.
     * Utilisé par BreadcrumbFactory pour construire le fil d'ariane sans parser l'URL.
     */
    public function findChemin(string $nomRoute): array
    {
        foreach ($this->getMenuStructure() as $module) {
            foreach ($module['items'] as $item) {
                // Item simple directement dans le module
                if (($item['link'] ?? null) === $nomRoute) {
                    return [
                        ['title' => $module['title'], 'icon' => $module['icon']],
                        ['title' => $item['title'],   'icon' => $item['icon'], 'route' => $nomRoute],
                    ];
                }

                // Item avec sous-items (createSubMenuItem)
                if (!empty($item['subitems'])) {
                    foreach ($item['subitems'] as $subitem) {
                        if (($subitem['link'] ?? null) === $nomRoute) {
                            return [
                                ['title' => $module['title'], 'icon' => $module['icon']],
                                ['title' => $item['title'],   'icon' => $item['icon']],
                                ['title' => $subitem['title'], 'icon' => $subitem['icon'], 'route' => $nomRoute],
                            ];
                        }
                    }
                }
            }
        }

        return [];
    }

    // =========================================================================
    //  HELPERS DE VÉRIFICATION (via SecurityService — zéro BDD)
    // =========================================================================

    /**
     * True si la route est visible pour le profil connecté.
     * Résultat depuis le cache intra-requête de SecurityService — zéro BDD.
     */
    private function hasAccesRoute(string $route): bool
    {
        return $this->securityService->verifierPermission(
            SecurityService::PERMISSION_VOIR,
            $route
        );
    }

    /**
     * True si AU MOINS UNE des routes listées est visible.
     * Utilisé pour décider si un BLOC entier s'affiche.
     * Ne pas revérifier les routes individuelles à l'intérieur du bloc
     * sauf si leurs droits peuvent différer.
     */
    private function hasAccesModule(string ...$routes): bool
    {
        foreach ($routes as $route) {
            if ($this->hasAccesRoute($route)) {
                return true;
            }
        }
        return false;
    }

    // =========================================================================
    //  BUILDERS D'ITEMS
    // =========================================================================

    public function createMenuItem(string $id, string $title, string $icon, array $items): array
    {
        return [
            'id'    => $id,
            'title' => $title,
            'icon'  => 'fas fa-' . $icon,
            'items' => $items,
        ];
    }

    public function createSimpleItem(string $label, ?string $icon = null, string $link = '#', array $routeParams = [], string $target = ''): array
    {
        return [
            'title'       => $label,
            'link'        => $link,
            'icon'        => 'fas fa-' . ($icon ?? 'file'),
            'target'      => $target,
            'routeParams' => $routeParams,
        ];
    }

    public function createSubMenuItem(string $label, string $icon, array $subitems): array
    {
        return [
            'title'    => $label,
            'icon'     => 'fas fa-' . $icon,
            'subitems' => $subitems,
        ];
    }

    public function createSubItem(
        string $label,
        string $icon,
        ?string $link = null,
        array $routeParams = [],
        string $target = '',
        ?string $modalId = null,
        bool $isModalTrigger = false
    ): array {
        return [
            'title'       => $label,
            'link'        => $link,
            'icon'        => 'fas fa-' . $icon,
            'routeParams' => $routeParams,
            'target'      => $target,
            'modal_id'    => $modalId,
            'is_modal'    => $isModalTrigger,
        ];
    }
}
