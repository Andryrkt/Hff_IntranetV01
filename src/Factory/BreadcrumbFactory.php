<?php

namespace App\Factory;

use App\Service\navigation\BreadcrumbMenuService;

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

        // Toujours ajouter l'accueil en premier
        $homeItem = $this->createItem('Accueil', $this->baseUrl ?: '/', false, 'fas fa-home');

        // Ajouter dropdown pour l'accueil si configuré
        if (isset($this->menuConfig['accueil'])) {
            $homeItem['dropdown'] = $this->createSubItems($path, 'accueil');
        }

        $breadcrumbs[] = $homeItem;

        // Traiter chaque segment de l'URL
        foreach ($segments as $index => $segment) {
            if ($index == 0 || is_numeric($segment) || preg_match('/\d$/', $segment)) {
                continue;
            }
            $isLast = ($index === count($segments) - 1);
            $label = $this->formatLabel($segment);
            $icon = $this->getIconForSegment($segment);

            $item = $this->createItem($label, $isLast ? null : '#', $isLast, $icon);

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
            'title' => $label,
            'link' => $url,
            'icon' => $icon,
            'is_active' => $isActive
        ];
    }

    private function createSubItems(string $currentPath, string $slug): array
    {
        return array_map(function ($sub) use ($currentPath) {
            $subLink = isset($sub['link']) ? $sub['link'] : null;
            return [
                'id' => $sub['id'] ?? null,
                'title' => $sub['title'],
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
            'list-dit'                              => 'Sélection de DIT',
            'da-first-form'                         => 'Sélection de choix',
            'new-avec-dit'                          => 'Création DA avec DIT',
            'new-da-direct'                         => 'Création DA directe',
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
