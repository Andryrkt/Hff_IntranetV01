<?php

namespace App\Service\navigation;

class MenuGroupe
{
    /**=========================================================================
     * 
     * DONNÉES STATIQUES — MENUS PRINCIPAUX
     * 
     * Structure d'un groupe :
     * [
     *   'route'    => string|null   // route pour filtrage (null = toujours visible)
     *   'label'    => string        // texte affiché
     *   'icon'     => string        // icône FontAwesome (sans "fa-")
     *   'link'     => string        // lien externe ou '#' (si absent, on génère depuis 'route')
     *   'target'   => string        // '_blank' optionnel
     *   'params'   => array         // paramètres de route optionnels
     *   'modal_id' => string|null   // id du modal à ouvrir
     *   'is_modal' => bool          // true si déclencheur de modal
     *   'subitems' => array         // sous-items (même structure récursive)
     * ]
     * 
     * Si link
     *==========================================================================*/


    public static function documentationGroupes(): array
    {
        return [
            [
                'label'    => 'Annuaire',
                'icon'     => 'address-book',
                'route'    => 'sso_annuaire',
                'target'   => '_blank',
            ],
            [
                'label'    => 'Plan analytique HFF',
                'icon'     => 'ruler-vertical',
                'link'     => '{basePath}/documentation/Structure%20analytique%20HFF.pdf',
                'route'    => 'documentation_interne', // accessible si 'documentation_interne' est accesible
                'target'   => '_blank',
            ],
            [
                'route'    => 'documentation_interne',
                'label'    => 'Documentation interne',
                'icon'     => 'folder-tree',
            ],
            [
                'label'    => 'Contrat',
                'icon'     => 'file-contract',
                'subitems' => [
                    ['label' => 'Nouveau contrat', 'icon' => 'plus-circle', 'route' => 'new_contrat', 'target' => '_blank'],
                    ['label' => 'Consultation',    'icon' => 'search',      'route' => 'contrat_liste'],
                ],
            ],
        ];
    }

    public static function reportingBIGroupes(): array
    {
        return [
            [
                'label'    => 'Reporting Power BI',
                'icon'     => null,
                'link'     => '#',
            ],
            [
                'label'    => 'Reporting Excel',
                'icon'     => null,
                'link'     => '#',
            ],
        ];
    }

    public static function comptaGroupes(): array
    {
        return [
            [
                'label'    => 'Cours de change',
                'icon'     => 'money-bill-wave',
                'link'     => '#',
            ],
            [
                'label'    => 'Demande de paiement',
                'icon'     => 'file-invoice-dollar',
                'subitems' => [
                    ['label' => 'Nouvelle demande de paiement à l’avance', 'icon' => 'plus-circle', 'route' => 'new_demande_paiement', 'params' => ['id' => 1]],
                    ['label' => 'Nouvelle demande de paiement après arrivage', 'icon' => 'plus-circle', 'route' => 'new_demande_paiement', 'params' => ['id' => 2]],
                    ['label' => 'Consultation',     'icon' => 'search',      'route' => 'ddp_liste'],
                ],
            ],
            [
                'label'    => 'Bon de caisse',
                'icon'     => 'receipt',
                'subitems' => [
                    ['label' => 'Nouveau bon de caisse', 'icon' => 'plus-circle', 'route' => 'new_bon_caisse'],
                    ['label' => 'Consultation',     'icon' => 'search',      'route' => 'bon_caisse_liste'],
                ],
            ],
        ];
    }

    public static function rhGroupes(): array
    {
        return [
            [
                'label'    => 'Ordre de mission',
                'icon'     => 'file-signature',
                'subitems' => [
                    ['label' => 'Nouvelle demande', 'icon' => 'plus-circle', 'route' => 'dom_first_form'],
                    ['label' => 'Consultation',     'icon' => 'search',      'route' => 'doms_liste'],
                ],
            ],
            [
                'label'    => 'Mutations',
                'icon'     => 'user-friends',
                'subitems' => [
                    ['label' => 'Nouvelle demande', 'icon' => 'plus-circle', 'route' => 'mutation_nouvelle_demande'],
                    ['label' => 'Consultation',     'icon' => 'search',      'route' => 'mutation_liste'],
                ],
            ],
            [
                'label'    => 'Congés',
                'icon'     => 'umbrella-beach',
                'subitems' => [
                    ['label' => 'Nouvelle demande',                'icon' => 'plus-circle',  'route' => 'new_conge',             'target' => '_blank'],
                    ['label' => 'Annulation de congés validés',    'icon' => 'calendar-xmark', 'route' => 'annulation_conge',      'target' => '_blank'],
                    ['label' => 'Annulation de congé dédiée RH',   'icon' => 'calendar-xmark', 'route' => 'annulation_conge_rh',   'target' => '_blank'],
                    ['label' => 'Consultation',                    'icon' => 'search',        'route' => 'conge_liste'],
                ],
            ],
            [
                'label'    => 'Temporaires',
                'icon'     => 'user-clock',
                'subitems' => [
                    ['label' => 'Nouvelle demande', 'icon' => 'plus-circle', 'link' => '#'],
                    ['label' => 'Consultation',     'icon' => 'search',      'link' => '#'],
                ],
            ],
        ];
    }

    public static function materielGroupes(): array
    {
        return [
            [
                'label'    => 'Logistique',
                'icon'     => 'truck-fast',
                'subitems' => [
                    ['label' => 'Nouvelle demande', 'icon' => 'plus-circle', 'route' => 'new_logistique'],
                ],
            ],
            [
                'label'    => 'Mouvement matériel',
                'icon'     => 'exchange-alt',
                'subitems' => [
                    ['label' => 'Nouvelle demande', 'icon' => 'plus-circle', 'route' => 'badms_newForm1'],
                    ['label' => 'Consultation',     'icon' => 'search',      'route' => 'badmListe_AffichageListeBadm'],
                ],
            ],
            [
                'label'    => 'Casier',
                'icon'     => 'box-open',
                'subitems' => [
                    ['label' => 'Nouvelle demande', 'icon' => 'plus-circle', 'route' => 'casier_nouveau'],
                    ['label' => 'Consultation',     'icon' => 'search',      'route' => 'listeTemporaire_affichageListeCasier'],
                ],
            ],
        ];
    }

    public static function atelierGroupes(): array
    {
        return [
            [
                'label'    => "Demande d'intervention",
                'icon'     => 'toolbox',
                'subitems' => [
                    ['label' => 'Nouvelle demande',            'icon' => 'plus-circle', 'route' => 'dit_new'],
                    ['label' => 'Consultation',                'icon' => 'search',      'route' => 'dit_index'],
                    ['label' => 'Dossier DIT',                 'icon' => 'folder',      'route' => 'dit_dossier_intervention_atelier'],
                    ['label' => 'Matrice des responsabilités', 'icon' => 'table',       'route' => 'dit_new', 'link'  => '{basePath}/documentation/MATRICE DE RESPONSABILITES OR v9.xlsx'],
                ],
            ],
            [
                'route'    => 'dit_new',
                'label'    => 'Glossaire OR',
                'icon'     => 'book',
                'link'     => '{basePath}/dit/glossaire_or/Glossaire_OR.pdf',
                'target'   => '_blank',
            ],
            [
                'route'    => 'planning_vue',
                'label'    => 'Planning',
                'icon'     => 'calendar-alt',
                'params'   => ['action' => 'oui'],
            ],
            [
                'route'    => 'liste_planning',
                'label'    => 'Planning détaillé',
                'icon'     => 'calendar-day',
                'params'   => ['action' => 'oui'],
            ],
            [
                'route'    => 'planningAtelier_vue',
                'label'    => 'Planning interne Atelier',
                'icon'     => 'calendar-alt',
            ],
        ];
    }

    public static function magasinGroupes(): array
    {
        return [
            [
                'label'    => 'OR',
                'icon'     => 'warehouse',
                'subitems' => [
                    ['label' => 'Liste à traiter', 'icon' => 'tasks',        'route' => 'magasinListe_index'],
                    ['label' => 'Liste à livrer',  'icon' => 'truck-loading', 'route' => 'magasinListe_or_Livrer'],
                ],
            ],
            [
                'label'    => 'CIS',
                'icon'     => 'pallet',
                'subitems' => [
                    ['label' => 'Liste à traiter', 'icon' => 'tasks',        'route' => 'cis_liste_a_traiter'],
                    ['label' => 'Liste à livrer',  'icon' => 'truck-loading', 'route' => 'cis_liste_a_livrer'],
                ],
            ],
            [
                'label'    => 'INVENTAIRE',
                'icon'     => 'file-alt',
                'subitems' => [
                    ['label' => 'Liste inventaire',    'icon' => 'file-alt', 'route' => 'liste_inventaire',        'params' => ['action' => 'oui']],
                    ['label' => 'Inventaire détaillé', 'icon' => 'file-alt', 'route' => 'liste_detail_inventaire'],
                ],
            ],
            [
                'label'    => 'SORTIE DE PIECES',
                'icon'     => 'arrow-left',
                'subitems' => [
                    ['label' => 'Nouvelle demande', 'icon' => 'plus-circle', 'route' => 'bl_soumission'],
                ],
            ],
            [
                'label'    => 'DEMATERIALISATION',
                'icon'     => 'cloud-arrow-up',
                'subitems' => [
                    ['label' => 'Devis',                        'icon' => 'file-invoice', 'route' => 'liste_devis_neg'],
                    ['label' => 'Planning de commande Magasin', 'icon' => 'calendar-alt', 'route' => 'interface_planningMag'],
                ],
            ],
            [
                'route'    => 'cde_fournisseur',
                'label'    => 'Soumission commandes fournisseur',
                'icon'     => 'list-alt',
            ],
            [
                'route'    => 'liste_Cde_Frn_Non_Placer',
                'label'    => 'Liste des cmds non placées',
                'icon'     => 'exclamation-circle',
            ],
        ];
    }

    public static function approGroupes(): array
    {
        return [
            [
                'route' => 'da_first_form',
                'label' => 'Nouvelle DA',
                'icon'  => 'file-alt',
            ],
            [
                'route' => 'list_da',
                'label' => 'Consultation des DA',
                'icon'  => 'search',
            ],
            [
                'route' => 'da_list_cde_frn',
                'label' => 'Liste des commandes fournisseurs',
                'icon'  => 'list-ul',
            ],
            [
                'route' => 'da_bon_a_payer',
                'label' => 'Consultation facture',
                'icon'  => 'list-ul',
            ],
            [
                'route' => 'da_reporting_ips',
                'label' => 'Reporting IPS DA reappro',
                'icon'  => 'chart-bar',
            ],
        ];
    }

    public static function itGroupes(): array
    {
        return [
            [
                'route' => 'demande_support_informatique',
                'label' => 'Nouvelle Demande',
                'icon'  => 'plus-circle',
            ],
            [
                'route' => 'liste_tik_index',
                'label' => 'Consultation',
                'icon'  => 'search',
            ],
            [
                'route' => 'tik_calendar_planning',
                'label' => 'Planning',
                'icon'  => 'file-alt',
            ],
        ];
    }

    public static function polGroupes(): array
    {
        return [
            [
                'label' => 'Nouvelle DLUB',
                'icon'  => 'file-alt',
            ],
            [
                'label' => 'Consultation des DLUB',
                'icon'  => 'search',
            ],
            [
                'label' => 'Liste des commandes fournisseurs',
                'icon'  => 'list-ul',
            ],
            [
                'label'    => 'OR',
                'icon'     => 'warehouse',
                'subitems' => [
                    ['label' => 'Liste à traiter', 'icon' => 'tasks',        'route' => 'pol_or_liste_a_traiter'],
                    ['label' => 'Liste à livrer',  'icon' => 'truck-loading', 'route' => 'pol_or_liste_a_livrer'],
                ],
            ],
            [
                'label'    => 'CIS',
                'icon'     => 'pallet',
                'subitems' => [
                    ['label' => 'Liste à traiter', 'icon' => 'tasks',        'route' => 'pol_cis_liste_a_traiter'],
                    ['label' => 'Liste à livrer',  'icon' => 'truck-loading', 'route' => 'pol_cis_liste_a_livrer'],
                ],
            ],
            [
                'route' => 'devis_magasin_pol_liste',
                'label' => 'Devis negoce pol',
                'icon'  => 'list-ul',
            ],
            [
                'label' => 'Pneumatiques',
                'icon'  => 'ring',
            ],
        ];
    }

    public static function energieGroupes(): array
    {
        return [
            [
                'label' => 'Rapport de production centrale',
                'icon'  => 'file-alt',
            ],
        ];
    }

    public static function hseGroupes(): array
    {
        return [
            [
                'label' => "Rapport d'incident",
                'icon'  => 'file-alt',
            ],
            [
                'label' => 'Documentation',
                'icon'  => 'folder-open',
            ],
        ];
    }

    /**
     * Définition statique des groupes et liens du menu Admin.
     * Modifiez ici pour ajouter / réordonner des entrées admin.
     */
    public static function adminMenuGroupes(): array
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
                ],
            ],
            [
                'header' => 'Historique',
                'icon'   => 'fa-clock-rotate-left',
                'links'  => [
                    ['label' => 'Consultation de pages',     'icon' => 'fa-eye',              'route' => 'consultation_page_index'],
                    ['label' => 'Historique des opérations', 'icon' => 'fa-file-circle-check', 'route' => 'operation_document_index'],
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
}
