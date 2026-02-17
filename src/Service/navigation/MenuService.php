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
     * Retourne la structure complète du menu pour l'utilisateur connecté.
     * Seuls les modules ayant au moins un item accessible sont inclus.
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
    //  CONSTRUCTION DES MENUS
    //  Chaque méthode construit uniquement les items accessibles.
    //  La vérification est faite via hasAccesRoute() — zéro requête BDD.
    // =========================================================================

    public function menuDocumentation(): array
    {
        $subitems = [];

        if ($this->hasAccesRoute('documentation_interne')) {
            $subitems[] = $this->createSimpleItem('Annuaire', 'address-book', '#');
            $subitems[] = $this->createSimpleItem('Plan analytique HFF', 'ruler-vertical', "{$this->basePath}/documentation/Structure%20analytique%20HFF.pdf", [], '_blank');
            $subitems[] = $this->createSimpleItem('Documentation interne', 'folder-tree', 'documentation_interne');
        }

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
        if (!$this->hasAccesModule('ddp_liste', 'new_bon_caisse', 'bon_caisse_liste')) {
            return $this->createMenuItem('comptaModal', 'Compta', 'calculator', []);
        }

        $subitems = [
            $this->createSimpleItem('Cours de change', 'money-bill-wave'),
        ];

        if ($this->hasAccesRoute('ddp_liste')) {
            $subitems[] = $this->createSubMenuItem('Demande de paiement', 'file-invoice-dollar', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', '#', [], '', 'modalTypeDemande', true),
                $this->createSubItem('Consultation', 'search', 'ddp_liste'),
            ]);
        }

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
        if (!$this->hasAccesModule('dom_first_form', 'doms_liste', 'mutation_nouvelle_demande', 'mutation_liste', 'new_conge', 'conge_liste')) {
            return $this->createMenuItem('rhModal', 'RH', 'users', []);
        }

        $subitems = [];

        // Ordre de mission (DOM)
        if ($this->hasAccesModule('dom_first_form', 'doms_liste')) {
            $subSubitems = [];
            if ($this->hasAccesRoute('dom_first_form')) {
                $subSubitems[] = $this->createSubItem('Nouvelle demande', 'plus-circle', 'dom_first_form');
            }
            $subSubitems[] = $this->createSubItem('Consultation', 'search', 'doms_liste');
            $subitems[]    = $this->createSubMenuItem('Ordre de mission', 'file-signature', $subSubitems);
        }

        // Mutations
        if ($this->hasAccesModule('mutation_nouvelle_demande', 'mutation_liste')) {
            $subitems[] = $this->createSubMenuItem('Mutations', 'user-friends', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'mutation_nouvelle_demande'),
                $this->createSubItem('Consultation', 'search', 'mutation_liste'),
            ]);
        }

        // Congés
        if ($this->hasAccesModule('new_conge', 'conge_liste')) {
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
            $subSubitems[] = $this->createSubItem('Consultation', 'search', 'conge_liste');
            $subitems[]    = $this->createSubMenuItem('Congés', 'umbrella-beach', $subSubitems);
        }

        // Temporaires
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

        if ($this->hasAccesRoute('new_logistique')) {
            $subitems[] = $this->createSubMenuItem('Logistique', 'truck-fast', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'new_logistique'),
            ]);
        }

        if ($this->hasAccesModule('badms_newForm1', 'badmListe_AffichageListeBadm')) {
            $subitems[] = $this->createSubMenuItem('Mouvement matériel', 'exchange-alt', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'badms_newForm1'),
                $this->createSubItem('Consultation', 'search', 'badmListe_AffichageListeBadm'),
            ]);
        }

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
        if (!$this->hasAccesModule(
            'dit_new',
            'dit_index',
            'dit_dossier_intervention_atelier',
            'planning_vue',
            'liste_planning',
            'planningAtelier_vue'
        )) {
            return $this->createMenuItem('atelierModal', 'Atelier', 'tools', []);
        }

        $subitems = [];

        // DIT
        if ($this->hasAccesModule('dit_new', 'dit_index', 'dit_dossier_intervention_atelier')) {
            $subSubitems = [];
            if ($this->hasAccesRoute('dit_new')) {
                $subSubitems[] = $this->createSubItem('Nouvelle demande', 'plus-circle', 'dit_new');
            }
            if ($this->hasAccesRoute('dit_index')) {
                $subSubitems[] = $this->createSubItem('Consultation', 'search', 'dit_index');
            }
            $subSubitems[] = $this->createSubItem('Dossier DIT', 'folder', 'dit_dossier_intervention_atelier');
            $subSubitems[] = $this->createSubItem('Matrice des responsabilités', 'table', "{$this->basePath}/documentation/MATRICE DE RESPONSABILITES OR v9.xlsx");
            $subitems[]    = $this->createSubMenuItem('Demande d\'intervention', 'toolbox', $subSubitems);
            $subitems[]    = $this->createSimpleItem('Glossaire OR', 'book', "{$this->basePath}/dit/glossaire_or/Glossaire_OR.pdf", [], '_blank');
        }

        // REP
        if ($this->hasAccesModule('planning_vue', 'liste_planning')) {
            $subitems[] = $this->createSimpleItem('Planning', 'calendar-alt', 'planning_vue', ['action' => 'oui']);
            $subitems[] = $this->createSimpleItem('Planning détaillé', 'calendar-day', 'liste_planning', ['action' => 'oui']);
        }

        // PAT
        if ($this->hasAccesRoute('planningAtelier_vue')) {
            $subitems[] = $this->createSimpleItem('Planning interne Atelier', 'calendar-alt', 'planningAtelier_vue');
        }

        return $this->createMenuItem('atelierModal', 'Atelier', 'tools', $subitems);
    }

    public function menuMagasin(): array
    {
        if (!$this->hasAccesModule(
            'magasinListe_index',
            'magasinListe_or_Livrer',
            'cis_liste_a_traiter',
            'cis_liste_a_livrer',
            'liste_inventaire',
            'liste_detail_inventaire',
            'bl_soumission',
            'devis_magasin_liste',
            'cde_fournisseur',
            'liste_Cde_Frn_Non_Placer'
        )) {
            return $this->createMenuItem('magasinModal', 'Magasin', 'dolly', []);
        }

        $subitems = [];

        if ($this->hasAccesModule('magasinListe_index', 'magasinListe_or_Livrer')) {
            $subitems[] = $this->createSubMenuItem('OR', 'warehouse', [
                $this->createSubItem('Liste à traiter', 'tasks', 'magasinListe_index'),
                $this->createSubItem('Liste à livrer', 'truck-loading', 'magasinListe_or_Livrer'),
            ]);
        }

        if ($this->hasAccesModule('cis_liste_a_traiter', 'cis_liste_a_livrer')) {
            $subitems[] = $this->createSubMenuItem('CIS', 'pallet', [
                $this->createSubItem('Liste à traiter', 'tasks', 'cis_liste_a_traiter'),
                $this->createSubItem('Liste à livrer', 'truck-loading', 'cis_liste_a_livrer'),
            ]);
        }

        if ($this->hasAccesModule('liste_inventaire', 'liste_detail_inventaire')) {
            $subitems[] = $this->createSubMenuItem('INVENTAIRE', 'file-alt', [
                $this->createSubItem('Liste inventaire', 'file-alt', 'liste_inventaire', ['action' => 'oui']),
                $this->createSubItem('Inventaire détaillé', 'file-alt', 'liste_detail_inventaire'),
            ]);
        }

        if ($this->hasAccesRoute('bl_soumission')) {
            $subitems[] = $this->createSubMenuItem('SORTIE DE PIECES', 'arrow-left', [
                $this->createSubItem('Nouvelle demande', 'plus-circle', 'bl_soumission'),
            ]);
        }

        if ($this->hasAccesModule('devis_magasin_liste', 'interface_planningMag')) {
            $subitems[] = $this->createSubMenuItem('DEMATERIALISATION', 'cloud-arrow-up', [
                $this->createSubItem('Devis', 'file-invoice', 'devis_magasin_liste'),
                $this->createSubItem('Planning de commande Magasin', 'calendar-alt', 'interface_planningMag'),
            ]);
        }

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
        if (!$this->hasAccesModule('da_first_form', 'list_da', 'da_list_cde_frn')) {
            return $this->createMenuItem('approModal', 'Appro', 'shopping-cart', []);
        }

        $subitems = [];

        if ($this->hasAccesRoute('da_first_form')) {
            $subitems[] = $this->createSimpleItem('Nouvelle DA', 'file-alt', 'da_first_form');
        }

        $subitems[] = $this->createSimpleItem('Consultation des DA', 'search', 'list_da');

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
        if (!$this->hasAccesModule('demande_support_informatique', 'liste_tik_index', 'tik_calendar_planning')) {
            return $this->createMenuItem('itModal', 'IT', 'laptop-code', []);
        }

        return $this->createMenuItem('itModal', 'IT', 'laptop-code', [
            $this->createSimpleItem('Nouvelle Demande', 'plus-circle', 'demande_support_informatique'),
            $this->createSimpleItem('Consultation', 'search', 'liste_tik_index'),
            $this->createSimpleItem('Planning', 'file-alt', 'tik_calendar_planning'),
        ]);
    }

    public function menuPOL(): array
    {
        if (!$this->hasAccesModule(
            'pol_or_liste_a_traiter',
            'pol_or_liste_a_livrer',
            'pol_cis_liste_a_traiter',
            'pol_cis_liste_a_livrer',
            'devis_magasin_pol_liste'
        )) {
            return $this->createMenuItem('polModal', 'POL', 'ring rotate-90', []);
        }

        $subitems = [
            $this->createSimpleItem('Nouvelle DLUB', 'file-alt'),
            $this->createSimpleItem('Consultation des DLUB', 'search'),
            $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul'),
        ];

        if ($this->hasAccesModule('pol_or_liste_a_traiter', 'pol_or_liste_a_livrer')) {
            $subitems[] = $this->createSubMenuItem('OR', 'warehouse', [
                $this->createSubItem('Liste à traiter', 'tasks', 'pol_or_liste_a_traiter'),
                $this->createSubItem('Liste à livrer', 'truck-loading', 'pol_or_liste_a_livrer'),
            ]);
        }

        if ($this->hasAccesModule('pol_cis_liste_a_traiter', 'pol_cis_liste_a_livrer')) {
            $subitems[] = $this->createSubMenuItem('CIS', 'pallet', [
                $this->createSubItem('Liste à traiter', 'tasks', 'pol_cis_liste_a_traiter'),
                $this->createSubItem('Liste à livrer', 'truck-loading', 'pol_cis_liste_a_livrer'),
            ]);
        }

        $subitems[] = $this->createSimpleItem('Devis negoce pol', 'list-ul', 'devis_magasin_pol_liste');
        $subitems[] = $this->createSimpleItem('Pneumatiques', 'ring');

        return $this->createMenuItem('polModal', 'POL', 'ring rotate-90', $subitems);
    }

    public function menuEnergie(): array
    {
        if (!$this->hasAccesRoute('energie_rapport_production')) {
            return $this->createMenuItem('energieModal', 'Energie', 'bolt', []);
        }

        return $this->createMenuItem('energieModal', 'Energie', 'bolt', [
            $this->createSimpleItem('Rapport de production centrale'),
        ]);
    }

    public function menuHSE(): array
    {
        if (!$this->hasAccesModule('hse_rapport_incident', 'hse_documentation')) {
            return $this->createMenuItem('hseModal', 'HSE', 'shield-alt', []);
        }

        return $this->createMenuItem('hseModal', 'HSE', 'shield-alt', [
            $this->createSimpleItem('Rapport d\'incident'),
            $this->createSimpleItem('Documentation'),
        ]);
    }

    // =========================================================================
    //  HELPERS DE VÉRIFICATION (via SecurityService — zéro BDD)
    // =========================================================================

    /**
     * True si la route est visible pour le profil connecté.
     * Résultat depuis le cache intra-requête de SecurityService.
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
     * Détermine si un module entier doit s'afficher.
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
    //  BUILDERS D'ITEMS (inchangés)
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
