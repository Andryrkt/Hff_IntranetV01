<?php

namespace App\Factory;

use App\Service\BreadcrumbMenuService;

class BreadcrumbFactory
{
    private string $baseUrl;
    private array $menuConfig;

    public function __construct(string $baseUrl = '/', BreadcrumbMenuService $breadcrumbMenuService)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->menuConfig = $breadcrumbMenuService->getFullMenuConfig();
    }

    public function createFromCurrentUrl(): array
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = array_filter(explode('/', trim($path, '/')));
        $breadcrumbs = [];
        $currentPath = $this->baseUrl;

        // Toujours ajouter l'accueil en premier
        $homeItem = $this->createItem('Accueil', $this->baseUrl ?: '/', false, 'fas fa-home');

        // Ajouter dropdown pour l'accueil si configuré
        if (isset($this->menuConfig['accueil'])) {
            $homeItem['dropdown'] = $this->createSubItems($path, 'accueil');
        }

        $breadcrumbs[] = $homeItem;

        // Traiter chaque segment de l'URL
        foreach ($segments as $index => $segment) {
            if ($index == 0) continue;
            $currentPath .= '/' . $segment;
            $isLast = ($index === count($segments) - 1);
            $label = $this->formatLabel($segment);
            $icon = $this->getIconForSegment($segment);

            $item = $this->createItem($label, $isLast ? null : $currentPath, $isLast, $icon);

            // Ajouter dropdown si configuré pour ce segment
            $slug = strtolower($segment);
            if (isset($this->menuConfig[$slug])) {
                $item['dropdown'] = $this->createSubItems($path, $slug);
            }

            $breadcrumbs[] = $item;
        }

        return $breadcrumbs;
    }

    private function createItem(string $label, ?string $url, bool $isActive, string $icon): array
    {
        return [
            'label' => $label,
            'url' => $url,
            'icon' => $icon,
            'is_active' => $isActive
        ];
    }

    private function createSubItems(string $currentPath, string $slug): array
    {
        return array_map(function ($sub) use ($currentPath) {
            $subLink = isset($sub['link']) ? $this->baseUrl . $sub['link'] : '#';
            return [
                'id' => $sub['id'] ?? null,
                'label' => $sub['label'],
                'link' => $subLink,
                'icon' => $sub['icon'] ?? '',
                'is_active' => ($subLink === $currentPath),
                'items' => $sub['items'] ?? [] // Ajouter les items pour le modal
            ];
        }, $this->menuConfig[$slug]);
    }

    private function formatLabel(string $segment): string
    {
        $specialLabels = [
            'new' => 'Nouvelle demande',
            'dit' => 'Demande d\'intervention',
            'demande-dintervention' => 'Demande d\'intervention',
            'atelier' => 'Atelier',
            'demandes' => 'Demandes',
            'planning' => 'Planning',
            'history' => 'Historique'
        ];

        $cleanSegment = str_replace(['-', '_'], ' ', $segment);
        return $specialLabels[$segment] ?? ucwords($cleanSegment);
    }

    private function getIconForSegment(string $segment): string
    {
        $iconMapping = [
            'accueil' => 'fas fa-home',
            'atelier' => 'fas fa-tools',
            'demande-dintervention' => 'fas fa-clipboard-list',
            'demandes' => 'fas fa-list-alt',
            'planning' => 'fas fa-calendar-alt',
            'new' => 'fas fa-plus-circle',
            'history' => 'fas fa-history',
            'edit' => 'fas fa-edit',
            'show' => 'fas fa-eye',
            'delete' => 'fas fa-trash',
            'settings' => 'fas fa-cog',
            'users' => 'fas fa-users',
            'profile' => 'fas fa-user',
            'reports' => 'fas fa-chart-bar',
            'dashboard' => 'fas fa-tachometer-alt',
            'documents' => 'fas fa-file-alt',
            'messages' => 'fas fa-envelope',
            'notifications' => 'fas fa-bell',
            'admin' => 'fas fa-shield-alt',
            'maintenance' => 'fas fa-wrench'
        ];

        return $iconMapping[$segment] ?? 'fas fa-folder';
    }
}