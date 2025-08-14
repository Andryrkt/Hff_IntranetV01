<?php

namespace App\Service;

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
            'accueil' => $this->getMainMenuItems(),
            'documentation' => $this->getDocumentationSubMenu(),
            'reporting' => $this->getReportingSubMenu(),
            'compta' => $this->getComptaSubMenu(),
            'rh' => $this->getRhSubMenu(),
            'materiel' => $this->getMaterielSubMenu(),
            'atelier' => $this->getAtelierSubMenu(),
            'magasin' => $this->getMagasinSubMenu(),
            'appro' => $this->getApproSubMenu(),
            'it' => $this->getItSubMenu(),
            'pol' => $this->getPolSubMenu(),
            'energie' => $this->getEnergieSubMenu(),
            'hse' => $this->getHseSubMenu()
        ];
    }

    private function getMainMenuItems(): array
    {
        $menuStructure = $this->menuService->getMenuStructure();
        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'title' => $item['title'],
                'icon' => $item['icon'],
                'link' => '#',
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
                    'id' => null,
                    'title' => $item['title'],
                    'link' => '#',
                    'icon' => $item['icon'],
                    'is_group' => true
                ];

                // Ajouter les sous-items
                foreach ($item['subitems'] as $subitem) {
                    $breadcrumbItems[] = [
                        'id' => $subitem['modal_id'] ?? null,
                        'title' => $subitem['title'],
                        'link' => $subitem['link'],
                        'icon' => $subitem['icon'],
                        'routeParams' => $subitem['routeParams'] ?? [],
                        'is_modal' => $subitem['is_modal'] ?? false,
                        'parent' => $item['title']
                    ];
                }
            } else {
                // Item simple
                $breadcrumbItems[] = [
                    'id' => null,
                    'title' => $item['title'],
                    'link' => $item['link'],
                    'icon' => $item['icon'],
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
            if ($item['title'] === $itemTitle) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Génère le breadcrumb pour une page donnée
     */
    public function generateBreadcrumb(string $section, string $currentPage = null): array
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
                        'link' => '#',
                        'icon' => $mainItem['icon']
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
                    'title' => $currentItem['title'],
                    'link' => $currentItem['link'],
                    'icon' => $currentItem['icon'],
                    'current' => true
                ];
            }
        }

        return $breadcrumb;
    }
}
