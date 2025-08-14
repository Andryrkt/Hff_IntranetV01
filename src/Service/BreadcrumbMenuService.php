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
            'atelier' => $this->getAtelierSubMenu(),
            'dit' => $this->getDitSubMenu()
        ];
    }

    private function getMainMenuItems(): array
    {
        $menuStructure = $this->menuService->getMenuStructure();

        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'label' => $item['title'],
                'icon' => 'fas fa-' . $item['icon'],
                'url' => '#',
                'items' => $item['items']
            ];
        }, $menuStructure);
    }

    private function getAtelierSubMenu(): array
    {
        // This part is context-dependent and should be built from the URL.
        // For now, I will keep the existing logic.
        return [
            ['id' => null, 'label' => 'Demandes', 'url' => '/atelier/demandes', 'icon' => 'fas fa-list'],
            ['id' => null, 'label' => 'Planning', 'url' => '/atelier/planning', 'icon' => 'fas fa-calendar']
        ];
    }

    private function getDitSubMenu(): array
    {
        // This part is context-dependent and should be built from the URL.
        // For now, I will keep the existing logic.
        return [
            ['id' => null, 'title' => 'Nouvelle', 'link' => '/atelier/dit/new', 'icon' => 'fas fa-plus'],
            ['id' => null, 'title' => 'Historique', 'link' => '/atelier/dit/history', 'icon' => 'fas fa-history']
        ];
    }
}