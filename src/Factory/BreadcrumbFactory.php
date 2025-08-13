<?php

namespace App\Factory;

class BreadcrumbFactory
{
    private string $baseUrl;
    private array $menuConfig;

    public function __construct(string $baseUrl = '/')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->menuConfig = $this->dropdownMenu();
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
            $currentPath .= '/' . $segment;
            $isLast = ($index === count($segments) - 1);
            $label = $this->formatLabel($segment);

            $item = $this->createItem($label, $isLast ? null : $currentPath, $isLast, '');

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
            $subUrl = $this->baseUrl . $sub['url'];
            return [
                'label' => $sub['label'],
                'url' => $subUrl,
                'icon' => $sub['icon'] ?? '',
                'is_active' => ($subUrl === $currentPath)
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

        // Nettoyer le segment
        $cleanSegment = str_replace(['-', '_'], ' ', $segment);

        // Retourner le label spécialisé ou une version formatée
        return $specialLabels[$segment] ?? ucwords($cleanSegment);
    }

    private function dropdownMenu(): array
    {
        return [
            'accueil' => [
                ['label' => 'Nouvelle demande', 'url' => '/atelier/demande-dintervention/new', 'icon' => 'fas fa-plus'],
                ['label' => 'Historique', 'url' => '/atelier/demande-dintervention/history', 'icon' => 'fas fa-history']
            ],
            'atelier' => [
                ['label' => 'Demandes', 'url' => '/atelier/demandes', 'icon' => 'fas fa-list'],
                ['label' => 'Planning', 'url' => '/atelier/planning', 'icon' => 'fas fa-calendar']
            ],
            'dit' => [
                ['label' => 'Nouvelle', 'url' => '/atelier/demande-dintervention/new', 'icon' => 'fas fa-plus'],
                ['label' => 'Historique', 'url' => '/atelier/demande-dintervention/history', 'icon' => 'fas fa-history']
            ]
        ];
    }
}
