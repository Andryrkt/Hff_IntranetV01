<?php

namespace App\Twig;

use Twig\TwigFunction;
use App\Factory\BreadcrumbFactory;
use Twig\Extension\AbstractExtension;
use App\Service\navigation\MenuService;
use App\Controller\Traits\lienGenerique;
use App\Service\security\SecurityService;

class BreadcrumbExtension extends AbstractExtension
{
    use lienGenerique;

    private MenuService $menuService;
    private SecurityService $securityService;
    private string $baseUrl;

    public function __construct(MenuService $menuService)
    {
        $this->menuService     = $menuService;
        $this->securityService = $menuService->getSecurityService();
        $this->baseUrl         = $this->urlGenerique($_ENV['BASE_PATH_COURT']);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('breadcrumbs',     [$this, 'generateBreadcrumbs']),
            new TwigFunction('navigationAdmin', [$this, 'generateNavigationAdmin']),
            new TwigFunction('hasAcces',        [$this, 'hasAcces']),
        ];
    }

    public function generateBreadcrumbs(): array
    {
        return (new BreadcrumbFactory($this->baseUrl, $this->menuService))
            ->createFromCurrentUrl();
    }

    /**
     * Retourne les groupes de liens du menu admin, filtrés par accès.
     * Utilisé dans _navigation.html.twig pour construire le dropdown Administrateur.
     */
    public function generateNavigationAdmin(): array
    {
        $groupes = [
            [
                'header' => 'Accès & Sécurité',
                'icon'   => 'fa-user-shield',
                'links'  => [
                    ['label' => 'Utilisateurs',             'icon' => 'fa-user',        'route' => 'utilisateur_index'],
                    ['label' => 'Profils ( ~ Applications)', 'icon' => 'fa-users-gear',  'route' => 'profil_index'],
                    ['label' => 'Droits et permissions',    'icon' => 'fa-key',         'route' => 'permission_index'],
                ],
            ],
            [
                'header' => 'Applications & Intégrations',
                'icon'   => 'fa-cubes',
                'links'  => [
                    ['label' => 'Pages',                    'icon' => 'fa-globe',       'route' => 'page_hff_index'],
                    ['label' => 'Applications ( ~ Pages)',  'icon' => 'fa-layer-group', 'route' => 'application_index'],
                    ['label' => 'Vignettes ( ~ Applications)', 'icon' => 'fa-clone',    'route' => 'vignette_index'],
                ],
            ],
            [
                'header' => 'Organisation',
                'icon'   => 'fa-sitemap',
                'links'  => [
                    ['label' => 'Sociétés',                 'icon' => 'fa-building',    'route' => 'societte_index'],
                    ['label' => 'Services',                 'icon' => 'fa-briefcase',   'route' => 'service_index'],
                    ['label' => 'Agences ( ~ Services)',    'icon' => 'fa-city',        'route' => 'agence_index'],
                    ['label' => 'Personnels',               'icon' => 'fa-id-card',     'route' => 'personnel_index'],
                    ['label' => 'Contacts d\'agence',       'icon' => 'fa-address-book', 'route' => 'contact_agence_ate_index'],
                ],
            ],
            [
                'header' => 'Historique',
                'icon'   => 'fa-clock-rotate-left',
                'links'  => [
                    ['label' => 'Consultation de pages',       'icon' => 'fa-eye',              'route' => 'consultation_page_index'],
                    ['label' => 'Historique des opérations',   'icon' => 'fa-file-circle-check', 'route' => 'operation_document_index'],
                ],
            ],
            [
                'header' => 'Tickets',
                'icon'   => 'fa-ticket',
                'links'  => [
                    ['label' => 'Toutes les catégories',    'icon' => 'fa-list',        'route' => 'tki_all_categorie_index'],
                ],
            ],
        ];

        $resultat = [];

        foreach ($groupes as $groupe) {
            $linksAccessibles = array_filter(
                $groupe['links'],
                fn(array $link) => $this->securityService->verifierPermission(
                    SecurityService::PERMISSION_VOIR,
                    $link['route']
                )
            );

            // N'inclure le groupe que s'il a au moins un lien accessible
            if (!empty($linksAccessibles)) {
                $resultat[] = [
                    'header' => $groupe['header'],
                    'icon'   => $groupe['icon'],
                    'links'  => array_values($linksAccessibles),
                ];
            }
        }

        return $resultat;
    }

    /**
     * Vérifie si une route est accessible (peutVoir) pour le profil connecté.
     * Utilisé dans les templates Twig pour des vérifications ponctuelles.
     */
    public function hasAcces(string $nomRoute): bool
    {
        return $this->securityService->verifierPermission(
            SecurityService::PERMISSION_VOIR,
            $nomRoute
        );
    }
}
