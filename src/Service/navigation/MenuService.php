<?php

namespace App\Service\navigation;

class MenuService
{
    /**
     * Retourne la structure du menu organisée
     */
    public function getMenuStructure(): array
    {
        return [
            $this->menuDocumentation(),
            $this->menuReportingBI(),
            $this->menuCompta(),
            $this->menuRH(),
            $this->menuMateriel(),
            $this->menuAtelier(),
            $this->menuMagasin(),
            $this->menuAppro(),
            $this->menuIT(),
            $this->menuPOL(),
            $this->menuEnergie(),
            $this->menuHSE()
        ];
    }

    public function menuDocumentation()
    {
        return $this->createMenuItem(
            'documentationModal',
            'Documentation',
            'book',
            [
                $this->createSimpleItem('Annuaire', 'address-book', '#'),
                $this->createSimpleItem('Plan analytique HFF', 'ruler-vertical', '/Upload/documentation/Structure%20analytique%20HFF.pdf', [], "_blank"),
                $this->createSimpleItem('Documentation interne', 'folder-tree', 'documentation_interne'),
                $this->createSimpleItem('Contrat', 'file-contract', 'https://hffc.docuware.cloud/docuware/formsweb/enregistrement-contrats?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be', [], "_blank")
            ]
        );
    }

    public function menuReportingBI()
    {
        return $this->createMenuItem(
            'reportingModal',
            'Reporting',
            'chart-line',
            [
                $this->createSimpleItem('Reporting Power BI', null, '#'),
                $this->createSimpleItem('Reporting Excel', null, '#')
            ]
        );
    }

    public function menuCompta()
    {
        return $this->createMenuItem(
            'comptaModal',
            'Compta',
            'calculator',
            [
                $this->createSimpleItem('Cours de change', 'money-bill-wave'),
                $this->createSubMenuItem(
                    'Demande de paiement',
                    'file-invoice-dollar',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', '#', [], 'modalTypeDemande', true),
                        $this->createSubItem('Consultation', 'search', 'ddp_liste')
                    ]
                ),
                $this->createSubMenuItem(
                    'Bon de caisse',
                    'receipt',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', '#'),
                        $this->createSubItem('Consultation', 'search', '#')
                    ]
                )
            ]
        );
    }

    public function menuRH()
    {
        return $this->createMenuItem(
            'rhModal',
            'RH',
            'users',
            [
                $this->createSubMenuItem(
                    'Ordre de mission',
                    'file-signature',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', 'dom_first_form'),
                        $this->createSubItem('Consultation', 'search', 'doms_liste')
                    ]
                ),
                $this->createSubMenuItem(
                    'Mutations',
                    'user-friends',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', 'mutation_nouvelle_demande'),
                        $this->createSubItem('Consultation', 'search', 'mutation_liste')
                    ]
                ),
                $this->createSubMenuItem(
                    'Congés',
                    'umbrella-beach',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', 'https://hffc.docuware.cloud/docuware/formsweb/demande-de-conges-new?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be', [], '_blank'),
                        $this->createSubItem('Consultation', 'search', '#')
                    ]
                ),
                $this->createSubMenuItem(
                    'Temporaires',
                    'user-clock',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', '#'),
                        $this->createSubItem('Consultation', 'search', '#')
                    ]
                )
            ]
        );
    }

    public function menuMateriel()
    {
        return $this->createMenuItem(
            'materielModal',
            'Matériel',
            'snowplow',
            [
                $this->createSubMenuItem(
                    'Mouvement matériel',
                    'exchange-alt',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', 'badms_newForm1'),
                        $this->createSubItem('Consultation', 'search', 'badmListe_AffichageListeBadm')
                    ]
                ),
                $this->createSubMenuItem(
                    'Casier',
                    'box-open',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', 'casier_nouveau'),
                        $this->createSubItem('Consultation', 'search', 'listeTemporaire_affichageListeCasier')
                    ]
                ),
                $this->createSimpleItem('Commandes matériels', 'shopping-basket'),
                $this->createSimpleItem('Suivi administratif des matériels', 'clipboard-check'),
            ]
        );
    }

    public function menuAtelier()
    {
        return $this->createMenuItem(
            'atelierModal',
            'Atelier',
            'tools',
            [
                $this->createSubMenuItem(
                    'Demande d\'intervention',
                    'toolbox',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', 'dit_new'),
                        $this->createSubItem('Consultation', 'search', 'dit_index'),
                        $this->createSubItem('Dossier DIT', 'folder', 'dit_dossier_intervention_atelier')
                    ]
                ),
                $this->createSimpleItem('Glossaire OR', 'book', '/Upload/dit/glossaire_or/Glossaire_OR.pdf', [], '_blank'),
                $this->createSimpleItem('Planning', 'calendar-alt', 'planning_vue', ['action' => 'oui']),
                $this->createSimpleItem('Planning détaillé', 'calendar-day', 'liste_planning', ['action' => 'oui']),
                $this->createSimpleItem('Satisfaction client (Atelier excellence survey)', 'smile', '#'),
            ]
        );
    }


    public function menuMagasin()
    {
        return $this->createMenuItem(
            'magasinModal',
            'Magasin',
            'dolly',
            [
                $this->createSubMenuItem(
                    'OR',
                    'warehouse',
                    [
                        $this->createSubItem('Liste à traiter', 'tasks', 'magasinListe_index'),
                        $this->createSubItem('Liste à livrer', 'truck-loading', 'magasinListe_or_Livrer')
                    ]
                ),
                $this->createSubMenuItem(
                    'CIS',
                    'pallet',
                    [
                        $this->createSubItem('Liste à traiter', 'tasks', 'cis_liste_a_traiter'),
                        $this->createSubItem('Liste à livrer', 'truck-loading', 'cis_liste_a_livrer')
                    ]
                ),
                $this->createSubMenuItem(
                    'INVENTAIRE',
                    'file-alt',
                    [
                        $this->createSubItem('Liste inventaire', 'file-alt', 'liste_inventaire', ['action' => 'oui']),
                        $this->createSubItem('Inventaire détaillé', 'file-alt', 'liste_detail_inventaire', ['action' => 'oui']),
                    ]
                ),
                $this->createSubMenuItem(
                    'SORTIE DE PIECES / LUBS',
                    'arrow-left',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', 'bl_soumission'),
                    ]
                ),
                $this->createSimpleItem('Commandes fournisseur', 'list-alt', 'cde_fournisseur'),
                $this->createSimpleItem('Liste des cmds non placées', 'exclamation-circle', 'liste_Cde_Frn_Non_Placer'),
                $this->createSimpleItem('Commandes clients', 'shopping-basket'),
                $this->createSimpleItem('Planning magasin', 'calendar-alt'),
            ]
        );
    }

    public function menuAppro()
    {
        return $this->createMenuItem(
            'approModal',
            'Appro',
            'shopping-cart',
            [
                $this->createSimpleItem('Nouvelle DA', 'file-alt', 'da_first_form'),
                $this->createSimpleItem('Consultation des DA', 'search', 'list_da'),
                $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul', 'da_list_cde_frn'),
            ]
        );
    }


    public function menuIT()
    {
        return $this->createMenuItem(
            'itModal',
            'IT',
            'laptop-code',
            [
                $this->createSimpleItem('Nouvelle Demande', 'plus-circle', 'demande_support_informatique'),
                $this->createSimpleItem('Consultation', 'search', 'liste_tik_index'),
                $this->createSimpleItem('Planning', 'file-alt', 'tik_calendar_planning'),
            ]
        );
    }

    public function menuPOL()
    {
        return $this->createMenuItem(
            'polModal',
            'POL',
            'ring',
            [
                $this->createSimpleItem('Nouvelle DLUB', 'file-alt'),
                $this->createSimpleItem('Consultation des DLUB', 'search'),
                $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul'),
                $this->createSimpleItem('Pneumatiques', 'ring'),
            ]
        );
    }

    public function menuEnergie()
    {
        return $this->createMenuItem(
            'energieModal',
            'Energie',
            'bolt',
            [
                $this->createSimpleItem('Rapport de production centrale'),
            ]
        );
    }


    public function menuHSE()
    {
        return $this->createMenuItem(
            'hseModal',
            'HSE',
            'shield-alt',
            [
                $this->createSimpleItem('Rapport d\'incident'),
                $this->createSimpleItem('Documentation'),
            ]
        );
    }
    /**
     * Crée un élément de menu principal
     */
    public function createMenuItem(string $id, string $title, string $icon, array $items): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'icon' => 'fas fa-' . $icon,
            'items' => $items
        ];
    }

    /**
     * Crée un item simple sans sous-menu
     */
    public function createSimpleItem(string $label, ?string $icon = null, string $link = '#', array $routeParams = [], string $target = ""): array
    {
        return [
            'title' => $label,
            'link' => $link,
            'icon' => 'fas fa-' . ($icon ?? 'file'),
            'target' => $target,
            'routeParams' => $routeParams
        ];
    }

    /**
     * Crée un item avec sous-menu
     */
    public function createSubMenuItem(string $label, string $icon, array $subitems): array
    {
        return [
            'title' => $label,
            'icon' => 'fas fa-' . $icon,
            'subitems' => $subitems
        ];
    }

    /**
     * Crée un sous-item
     */
    public function createSubItem(
        string $label,
        string $icon,
        ?string $link = null,
        array $routeParams = [],
        string $target = "",
        ?string $modalId = null,
        bool $isModalTrigger = false
    ): array {
        return [
            'title' => $label,
            'link' => $link,
            'icon' => 'fas fa-' . $icon,
            'routeParams' => $routeParams,
            'target' => $target,
            'modal_id' => $modalId,
            'is_modal' => $isModalTrigger
        ];
    }
}
