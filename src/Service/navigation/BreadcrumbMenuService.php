<?php

namespace App\Service\navigation;

class BreadcrumbMenuService
{
    private MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function getFullMenuConfig(): array
    {
        return [
            // Accueil - Sous-menus accueils
            'accueil'              => $this->getMainMenuItems(),
            'documentation'        => $this->getDocumentationSubMenu(),
            'reporting'            => $this->getReportingSubMenu(),
            'compta'               => $this->getComptaSubMenu(),
            'rh'                   => $this->getRhSubMenu(),
            'materiel'             => $this->getMaterielSubMenu(),
            'atelier'              => $this->getAtelierSubMenu(),
            'magasin'              => $this->getMagasinSubMenu(),
            'appro'                => $this->getApproSubMenu(),
            'it'                   => $this->getItSubMenu(),
            'pol'                  => $this->getPolSubMenu(),
            'energie'              => $this->getEnergieSubMenu(),
            'hse'                  => $this->getHseSubMenu(),
            // RH - Sous-menus spécifiques
            'ordre-de-mission'     => $this->getOdmSubMenu(),
            'mutation'             => $this->getMutationSubMenu(),
            'conges'               => $this->getCongesSubMenu(),
            'temporaires'          => $this->getTemporairesSubMenu(),
            // Magasin - Sous-menus spécifiques
            'or'                    => $this->getOrSubMenu(),
            'cis'                   => $this->getCisSubMenu(),
            'sortie-de-pieces-lubs' => $this->getSortiePieceLubSubMenu(),
            'inventaire'            => $this->getInventaireSubMenu(),
            'dematerialisation'     => $this->getDematerialisationSubMenu(),
            // Matériel - Sous-menus spécifiques
            'logistique'            => $this->getLogistiqueSubMenu(),
            'mouvement-materiel'   => $this->getMouvementMaterielSubMenu(),
            'casier'               => $this->getCasierSubMenu(),
            // Atelier - Sous-menus spécifiques
            'demande-intervention' => $this->getDemandeInterventionSubMenu(),
            // Compta - Sous-menus spécifiques
            'demande-de-paiement'  => $this->getDemandePaiementSubMenu(),
            'bon-de-caisse'        => $this->getBonCaisseSubMenu(),
            // Appro - Sous-menus spécifiques
            'demande-appro'        => $this->getDemandeApproSubMenu(),
            // pol - sous menus spécifiques
            'or-pol'            => $this->getOrPolSubMenu(),
            'cis-pol'            => $this->getCisPolSubMenu(),
        ];
    }

    private function getMainMenuItems(): array
    {
        $menuStructure = $this->menuService->getMenuStructure();
        return array_map(function ($item) {
            return [
                'id'    => $item['id'],
                'title' => $item['title'],
                'icon'  => $item['icon'],
                'link'  => '#',
                'items' => $item['items']
            ];
        }, $menuStructure);
    }

    private function getDocumentationSubMenu(): array
    {
        $menuDocumentation = $this->menuService->menuDocumentation();
        return $this->extractSubMenuItems($menuDocumentation['items']);
    }

    private function getReportingSubMenu(): array
    {
        $menuReporting = $this->menuService->menuReportingBI();
        return $this->extractSubMenuItems($menuReporting['items']);
    }

    private function getComptaSubMenu(): array
    {
        $menuCompta = $this->menuService->menuCompta();
        return $this->extractSubMenuItems($menuCompta['items']);
    }

    private function getRhSubMenu(): array
    {
        $menuRh = $this->menuService->menuRH();
        return $this->extractSubMenuItems($menuRh['items']);
    }

    private function getMaterielSubMenu(): array
    {
        $menuMateriel = $this->menuService->menuMateriel();
        return $this->extractSubMenuItems($menuMateriel['items']);
    }

    private function getAtelierSubMenu(): array
    {
        $menuAtelier = $this->menuService->menuAtelier();
        return $this->extractSubMenuItems($menuAtelier['items']);
    }

    private function getMagasinSubMenu(): array
    {
        $menuMagasin = $this->menuService->menuMagasin();
        return $this->extractSubMenuItems($menuMagasin['items']);
    }

    private function getApproSubMenu(): array
    {
        $menuAppro = $this->menuService->menuAppro();
        return $this->extractSubMenuItems($menuAppro['items']);
    }

    private function getItSubMenu(): array
    {
        $menuIt = $this->menuService->menuIT();
        return $this->extractSubMenuItems($menuIt['items']);
    }

    private function getPolSubMenu(): array
    {
        $menuPol = $this->menuService->menuPOL();
        return $this->extractSubMenuItems($menuPol['items']);
    }

    private function getEnergieSubMenu(): array
    {
        $menuEnergie = $this->menuService->menuEnergie();
        return $this->extractSubMenuItems($menuEnergie['items']);
    }

    private function getHseSubMenu(): array
    {
        $menuHse = $this->menuService->menuHSE();
        return $this->extractSubMenuItems($menuHse['items']);
    }

    // ========== RH - Sous-menus spécifiques ==========

    private function getOdmSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => 'dom_first_form',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Consultation',
                'link'        => 'doms_liste',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ]
        ];
    }

    private function getMutationSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => 'mutation_nouvelle_demande',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Consultation',
                'link'        => 'mutation_liste',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ]
        ];
    }

    private function getCongesSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => '#',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Consultation',
                'link'        => 'conge_liste',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ]
        ];
    }

    private function getTemporairesSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => '#',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Consultation',
                'link'        => '#',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ]
        ];
    }

    // ========== Magasin - Sous-menus spécifiques ==========

    private function getOrSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Liste à traiter',
                'link'        => 'magasinListe_index',
                'icon'        => 'fas fa-tasks',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Liste à livrer',
                'link'        => 'magasinListe_or_Livrer',
                'icon'        => 'fas fa-truck-loading',
                'routeParams' => []
            ]
        ];
    }

    private function getCisSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Liste à traiter',
                'link'        => 'cis_liste_a_traiter',
                'icon'        => 'fas fa-tasks',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Liste à livrer',
                'link'        => 'cis_liste_a_livrer',
                'icon'        => 'fas fa-truck-loading',
                'routeParams' => []
            ]
        ];
    }

    private function getSortiePieceLubSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => 'bl_soumission',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ]
        ];
    }

    private function getInventaireSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Liste inventaire',
                'link'        => 'liste_inventaire',
                'icon'        => 'fas fa-tasks',
                'routeParams' => ['action' => 'oui']
            ],
            [
                'id'          => null,
                'title'       => 'Inventaire détaillé',
                'link'        => 'liste_detail_inventaire',
                'icon'        => 'fas fa-tasks',
                'routeParams' => ['action' => 'oui']
            ]
        ];
    }

    public function getDematerialisationSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Devis',
                'link'        => 'devis_magasin_liste',
                'icon'        => 'fas fa-file-invoice',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Commandes clients',
                'link'        => '#',
                'icon'        => 'fas fa-shopping-basket',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Planning de commande Magasin',
                'link'        => 'interface_planningMag',
                'icon'        => 'fas fa-calendar-alt',
                'routeParams' => []
            ]
        ];
    }
    // ========== Matériel - Sous-menus spécifiques ==========

    private function getMouvementMaterielSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => 'badms_newForm1',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Consultation',
                'link'        => 'badmListe_AffichageListeBadm',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ]
        ];
    }

    private function getLogistiqueSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => 'new_logistique',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ]
        ];
    }

    private function getCasierSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => 'casier_nouveau',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Consultation',
                'link'        => 'listeTemporaire_affichageListeCasier',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ]
        ];
    }

    // ========== Atelier - Sous-menus spécifiques ==========

    private function getDemandeInterventionSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => 'dit_new',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Consultation',
                'link'        => 'dit_index',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Dossier DIT',
                'link'        => 'dit_dossier_intervention_atelier',
                'icon'        => 'fas fa-folder',
                'routeParams' => []
            ]
        ];
    }

    // ========== Compta - Sous-menus spécifiques ==========

    private function getDemandePaiementSubMenu(): array
    {
        return [
            [
                'id'          => 'modalTypeDemande',
                'title'       => 'Nouvelle demande',
                'link'        => '#',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => [],
                'is_modal'    => true
            ],
            [
                'id'          => null,
                'title'       => 'Consultation',
                'link'        => 'ddp_liste',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ]
        ];
    }

    private function getBonCaisseSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande',
                'link'        => '#',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Consultation',
                'link'        => 'bon_caisse_liste',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ]
        ];
    }

    // ========== Appro - Sous-menus spécifiques ==========
    private function getDemandeApproSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Nouvelle demande d’achat',
                'link'        => 'da_first_form',
                'icon'        => 'fas fa-plus-circle',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Liste des demandes d’achats',
                'link'        => 'list_da',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Liste des Commandes fournisseur',
                'link'        => 'da_list_cde_frn',
                'icon'        => 'fas fa-search',
                'routeParams' => []
            ]
        ];
    }

    // ========== Pol - Sous-menus spécifiques ==========
    private function getOrPolSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Liste à traiter',
                'link'        => 'pol_or_liste_a_traiter',
                'icon'        => 'fas fa-tasks',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Liste à livrer',
                'link'        => 'pol_or_liste_a_livrer',
                'icon'        => 'fas fa-truck-loading',
                'routeParams' => []
            ]
        ];
    }
    private function getCisPolSubMenu(): array
    {
        return [
            [
                'id'          => null,
                'title'       => 'Liste à traiter',
                'link'        => 'pol_cis_liste_a_traiter',
                'icon'        => 'fas fa-tasks',
                'routeParams' => []
            ],
            [
                'id'          => null,
                'title'       => 'Liste à livrer',
                'link'        => 'pol_cis_liste_a_livrer',
                'icon'        => 'fas fa-truck-loading',
                'routeParams' => []
            ]
        ];
    }

    /**
     * Extrait et transforme les items d'un menu en format breadcrumb
     */
    private function extractSubMenuItems(array $items): array
    {
        $breadcrumbItems = [];

        foreach ($items as $item) {
            // Si l'item a des sous-items, on les traite récursivement
            if (isset($item['subitems'])) {
                // Ajouter l'item parent comme séparateur/groupe
                $breadcrumbItems[] = [
                    'id'       => null,
                    'title'    => $item['title'],
                    'link'     => null,
                    'icon'     => $item['icon'],
                    'is_group' => true
                ];

                // Ajouter les sous-items
                foreach ($item['subitems'] as $subitem) {
                    $breadcrumbItems[] = [
                        'id'          => $subitem['modal_id'] ?? null,
                        'title'       => $subitem['title'], // Titre combiné pour éviter la confusion
                        'short_title' => $subitem['title'], // Titre court pour l'affichage
                        'link'        => $subitem['link'],
                        'icon'        => $subitem['icon'],
                        'routeParams' => $subitem['routeParams'] ?? [],
                        'is_modal'    => $subitem['is_modal'] ?? false,
                        'parent'      => $item['title'],
                        'parent_icon' => $item['icon']
                    ];
                }
            } else {
                // Item simple
                $breadcrumbItems[] = [
                    'id'          => null,
                    'title'       => $item['title'],
                    'link'        => $item['link'],
                    'icon'        => $item['icon'],
                    'routeParams' => $item['routeParams'] ?? []
                ];
            }
        }

        return $breadcrumbItems;
    }

    /**
     * Trouve un item spécifique dans la configuration du menu
     */
    public function findMenuItem(string $section, string $itemTitle): ?array
    {
        $config = $this->getFullMenuConfig();

        if (!isset($config[$section])) {
            return null;
        }

        foreach ($config[$section] as $item) {
            if (
                $item['title'] === $itemTitle ||
                (isset($item['short_title']) && $item['short_title'] === $itemTitle)
            ) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Trouve un sous-item par son lien
     */
    public function findMenuItemByLink(string $section, string $link): ?array
    {
        $config = $this->getFullMenuConfig();

        if (!isset($config[$section])) {
            return null;
        }

        foreach ($config[$section] as $item) {
            if ($item['link'] === $link) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Obtient tous les items d'une catégorie parente
     */
    public function getItemsByParent(string $section, string $parentTitle): array
    {
        $config = $this->getFullMenuConfig();

        if (!isset($config[$section])) {
            return [];
        }

        $items = [];
        foreach ($config[$section] as $item) {
            if (isset($item['parent']) && $item['parent'] === $parentTitle) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Génère le breadcrumb pour une page donnée
     */
    public function generateBreadcrumb(string $section, ?string $currentPage = null): array
    {
        $breadcrumb = [
            ['title' => 'Accueil', 'link' => '/', 'icon' => 'fas fa-home']
        ];

        $config = $this->getFullMenuConfig();

        // Ajouter la section principale
        if ($section !== 'accueil' && isset($config['accueil'])) {
            foreach ($config['accueil'] as $mainItem) {
                if (strtolower($mainItem['title']) === $section) {
                    $breadcrumb[] = [
                        'title' => $mainItem['title'],
                        'link'  => '#',
                        'icon'  => $mainItem['icon']
                    ];
                    break;
                }
            }
        }

        // Ajouter la page courante si spécifiée
        if ($currentPage && isset($config[$section])) {
            $currentItem = $this->findMenuItem($section, $currentPage);
            if ($currentItem) {
                $breadcrumb[] = [
                    'title'   => $currentItem['title'],
                    'link'    => $currentItem['link'],
                    'icon'    => $currentItem['icon'],
                    'current' => true
                ];
            }
        }

        return $breadcrumb;
    }

    /**
     * Génère une structure hiérarchique pour l'affichage des menus
     */
    public function getHierarchicalMenu(string $section): array
    {
        $config = $this->getFullMenuConfig();

        if (!isset($config[$section])) {
            return [];
        }

        $hierarchical = [];
        $groups = [];

        foreach ($config[$section] as $item) {
            if (isset($item['is_group']) && $item['is_group']) {
                // C'est un groupe
                $groups[$item['title']] = [
                    'title'    => $item['title'],
                    'icon'     => $item['icon'],
                    'children' => []
                ];
            } elseif (isset($item['parent'])) {
                // C'est un sous-item
                if (isset($groups[$item['parent']])) {
                    $groups[$item['parent']]['children'][] = $item;
                }
            } else {
                // Item simple
                $hierarchical[] = $item;
            }
        }

        // Ajouter les groupes avec leurs enfants
        foreach ($groups as $group) {
            if (!empty($group['children'])) {
                $hierarchical[] = $group;
            }
        }

        return $hierarchical;
    }

    /**
     * Obtient les liens directs pour une section (sans groupes)
     */
    public function getDirectLinks(string $section): array
    {
        $config = $this->getFullMenuConfig();

        if (!isset($config[$section])) {
            return [];
        }

        $directLinks = [];
        foreach ($config[$section] as $item) {
            if (!isset($item['is_group']) && $item['link'] !== '#') {
                $directLinks[] = [
                    'title'       => $item['short_title'] ?? $item['title'],
                    'full_title'  => $item['title'],
                    'link'        => $item['link'],
                    'icon'        => $item['icon'],
                    'parent'      => $item['parent'] ?? null,
                    'routeParams' => $item['routeParams'] ?? []
                ];
            }
        }

        return $directLinks;
    }
}
