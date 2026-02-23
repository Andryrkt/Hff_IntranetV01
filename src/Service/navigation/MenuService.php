<?php

namespace App\Service\navigation;

use App\Service\UserData\UserDataService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class MenuService
{
    // ─── Configuration du cache persistant ───────────────────────────────────
    public const CACHE_KEY_PRINCIPAL = 'menu.principal.profil_';
    public const CACHE_KEY_ADMIN     = 'menu.admin.profil_';
    public const CACHE_TAG_PREFIX    = 'menu.profil_'; // tag partagé → invalidation groupée

    public UserDataService $userDataService;
    private TagAwareCacheInterface $cache;
    private string $basePath;

    /**
     * Cache intra-requête — évite de reconstruire les menus plusieurs fois
     * dans la même requête HTTP (breadcrumb + dropdown + page d'accueil…).
     */
    private ?array $cacheMenuStructure      = null;
    private ?array $cacheAdminMenuStructure = null;

    public function __construct(UserDataService $userDataService, TagAwareCacheInterface $cache)
    {
        $this->userDataService = $userDataService;
        $this->cache           = $cache;
        $this->basePath        = $_ENV['BASE_PATH_FICHIER_COURT'];
    }

    // =========================================================================
    //  API PUBLIQUE
    // =========================================================================

    /** 
     * Écrase et reconstruit le menu principal pour un profil donné.
     */
    public function ecraserMenuStructure(int $profilId): void
    {
        $cle = self::CACHE_KEY_PRINCIPAL . $profilId;
        $tag = self::CACHE_TAG_PREFIX    . $profilId;

        $this->cache->delete($cle);
        $this->cache->get($cle, function (ItemInterface $item) use ($tag): array {
            $item->expiresAfter(null); // Pas d'expiration automatique : invalidation via tag uniquement
            $item->tag($tag);
            return $this->construireMenuPrincipal();
        });
    }

    /**
     * Retourne la structure du menu principal filtré par peutVoir.
     */
    public function getMenuStructure(): array
    {
        // Couche 1 : cache intra-requête
        if ($this->cacheMenuStructure !== null) {
            return $this->cacheMenuStructure;
        }

        $profilId = $this->getProfilId();

        // Pas de profil connecté → on construit sans cacher (résultat vide de toute façon)
        if ($profilId === null) {
            return $this->cacheMenuStructure = [];
        }

        // Couche 2 : cache persistant
        $cle = self::CACHE_KEY_PRINCIPAL . $profilId;
        $tag = self::CACHE_TAG_PREFIX    . $profilId;

        return $this->cacheMenuStructure = $this->cache->get($cle, function (ItemInterface $item) use ($tag): array {
            $item->expiresAfter(null);
            $item->tag($tag);
            return $this->construireMenuPrincipal();
        });
    }

    /**
     * Construit le menu principal en itérant sur tous les modules déclaratifs.
     * Chaque module retourne sa définition via xxxGroupes(), ce builder filtre et assemble.
     */
    public function construireMenuPrincipal(): array
    {
        $modules = [
            ['id' => 'documentationModal', 'title' => 'Documentation', 'icon' => 'book',           'groupes' => $this->documentationGroupes()],
            ['id' => 'reportingModal',     'title' => 'Reporting',     'icon' => 'chart-line',     'groupes' => $this->reportingBIGroupes()],
            ['id' => 'comptaModal',        'title' => 'Compta',        'icon' => 'calculator',     'groupes' => $this->comptaGroupes()],
            ['id' => 'rhModal',            'title' => 'RH',            'icon' => 'users',          'groupes' => $this->rhGroupes()],
            ['id' => 'materielModal',      'title' => 'Matériel',      'icon' => 'snowplow',       'groupes' => $this->materielGroupes()],
            ['id' => 'atelierModal',       'title' => 'Atelier',       'icon' => 'tools',          'groupes' => $this->atelierGroupes()],
            ['id' => 'magasinModal',       'title' => 'Magasin',       'icon' => 'dolly',          'groupes' => $this->magasinGroupes()],
            ['id' => 'approModal',         'title' => 'Appro',         'icon' => 'shopping-cart',  'groupes' => $this->approGroupes()],
            ['id' => 'itModal',            'title' => 'IT',            'icon' => 'laptop-code',    'groupes' => $this->itGroupes()],
            ['id' => 'polModal',           'title' => 'POL',           'icon' => 'ring rotate-90', 'groupes' => $this->polGroupes()],
            ['id' => 'energieModal',       'title' => 'Energie',       'icon' => 'bolt',           'groupes' => $this->energieGroupes()],
            ['id' => 'hseModal',           'title' => 'HSE',           'icon' => 'shield-alt',     'groupes' => $this->hseGroupes()],
        ];

        $vignettes = [];

        foreach ($modules as $module) {
            $items = $this->filtrerGroupes($module['groupes']);
            if (!empty($items)) {
                $vignettes[] = $this->createMenuItem($module['id'], $module['title'], $module['icon'], $items);
            }
        }

        return $vignettes;
    }

    // =========================================================================
    //  DONNÉES STATIQUES — MENUS PRINCIPAUX
    //
    //  Structure d'un groupe :
    //  [
    //    'route'    => string|null   // route pour filtrage (null = toujours visible)
    //    'label'    => string        // texte affiché
    //    'icon'     => string        // icône FontAwesome (sans "fa-")
    //    'link'     => string        // lien externe ou '#' (si absent, on génère depuis 'route')
    //    'target'   => string        // '_blank' optionnel
    //    'params'   => array         // paramètres de route optionnels
    //    'modal_id' => string|null   // id du modal à ouvrir
    //    'is_modal' => bool          // true si déclencheur de modal
    //    'subitems' => array         // sous-items (même structure récursive)
    //  ]
    // =========================================================================

    private function documentationGroupes(): array
    {
        return [
            // Bloc documentation générale : contrôlé par documentation_interne
            // Les items sans 'route' (Annuaire, PDF) sont toujours inclus si le bloc est visible
            [
                'label'    => 'Annuaire',
                'icon'     => 'address-book',
                'link'     => '#',
            ],
            [
                'label'    => 'Plan analytique HFF',
                'icon'     => 'ruler-vertical',
                'link'     => '{basePath}/documentation/Structure%20analytique%20HFF.pdf',
                'route'    => 'documentation_interne',
                'target'   => '_blank',
            ],
            [
                'route'    => 'documentation_interne',
                'label'    => 'Documentation interne',
                'icon'     => 'folder-tree',
            ],
            // Bloc Contrat : accès indépendant, avec sous-menu
            [
                'label'    => 'Contrat',
                'icon'     => 'file-contract',
                'subitems' => [
                    ['label' => 'Nouveau contrat', 'icon' => 'plus-circle', 'route' => 'new_contrat', 'target' => '_blank'],
                    ['label' => 'Consultation',    'icon' => 'search',      'link' => '#'],
                ],
            ],
        ];
    }

    private function reportingBIGroupes(): array
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

    private function comptaGroupes(): array
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

    private function rhGroupes(): array
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

    private function materielGroupes(): array
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

    private function atelierGroupes(): array
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

    private function magasinGroupes(): array
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
                    ['label' => 'Devis',                        'icon' => 'file-invoice', 'route' => 'devis_magasin_liste'],
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

    private function approGroupes(): array
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
                'route' => 'da_reporting_ips',
                'label' => 'Reporting IPS DA reappro',
                'icon'  => 'chart-bar',
            ],
        ];
    }

    private function itGroupes(): array
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

    private function polGroupes(): array
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

    private function energieGroupes(): array
    {
        return [
            [
                'label' => 'Rapport de production centrale',
                'icon'  => 'file-alt',
            ],
        ];
    }

    private function hseGroupes(): array
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

    // =========================================================================
    //  MOTEUR DE FILTRAGE — cœur du pattern
    //
    //  filtrerGroupes() parcourt les définitions statiques et :
    //  1. Filtre chaque groupe selon sa 'route' de contrôle (hasAccesRoute)
    //  2. Si le groupe a des 'subitems', filtre récursivement chaque enfant
    //  3. Un groupe avec subitems est supprimé si aucun enfant n'est accessible
    //  4. Un item sans 'route' (lien '#' ou externe) passe toujours
    // =========================================================================

    private function filtrerGroupes(array $groupes): array
    {
        $items = [];

        foreach ($groupes as $groupe) {
            // Groupe avec sous-items → s'affiche si au moins un enfant est accessible.
            // La 'route' du groupe n'est PAS utilisée comme condition d'accès ici :
            // c'est le filtrage des enfants qui décide.
            if (!empty($groupe['subitems'])) {
                $subitemsAccessibles = $this->filtrerSousItems($groupe['subitems']);
                if (empty($subitemsAccessibles)) {
                    continue;
                }
                $items[] = $this->createSubMenuItem(
                    $groupe['label'],
                    $groupe['icon'] ?? 'file',
                    $subitemsAccessibles
                );
                continue;
            }

            // Item simple → filtré par sa propre 'route' (null = toujours visible)
            $route = $groupe['route'] ?? null;
            if ($route !== null && !$this->hasAccesRoute($route)) {
                continue;
            } else {
                $link = $groupe['link'] ?? '#';
                if ($route === null && $link === '#') {
                    continue;
                }
            }

            $items[] = $this->buildSimpleItem($groupe);
        }

        return $items;
    }

    /**
     * Filtre et construit les sous-items d'un groupe.
     * Un sous-item sans 'route' est toujours inclus (lien '#' ou externe).
     */
    private function filtrerSousItems(array $subitems): array
    {
        $result = [];

        foreach ($subitems as $subitem) {
            $route = $subitem['route'] ?? null;

            if ($route !== null && !$this->hasAccesRoute($route)) {
                continue;
            } else {
                $link = $subitem['link'] ?? '#';
                if ($route === null && $link === '#') {
                    continue;
                }
            }

            $result[] = $this->createSubItem(
                $subitem['label'],
                $subitem['icon'] ?? 'file',
                $this->resoudreLink($subitem),
                $subitem['params'] ?? [],
                $subitem['target'] ?? '',
                $subitem['modal_id'] ?? null,
                $subitem['is_modal'] ?? false,
            );
        }

        return $result;
    }

    /**
     * Construit un item simple à partir d'une définition statique.
     */
    private function buildSimpleItem(array $groupe): array
    {
        return $this->createSimpleItem(
            $groupe['label'],
            $groupe['icon'] ?? null,
            $this->resoudreLink($groupe),
            $groupe['params'] ?? [],
            $groupe['target'] ?? '',
        );
    }

    /**
     * Résout le lien d'un item :
     * - 'link' explicite (externe, '#', chemin avec {basePath}) → retourne tel quel après substitution
     * - 'route' → retourne le nom de route (les builders Twig/contrôleur génèrent l'URL)
     * - ni l'un ni l'autre → '#'
     */
    private function resoudreLink(array $definition): string
    {
        if (isset($definition['link'])) {
            return str_replace('{basePath}', $this->basePath, $definition['link']);
        }

        return $definition['route'] ?? '#';
    }

    // =========================================================================
    //  API PUBLIQUE — MENU ADMIN
    // =========================================================================

    /**
     * Écrase et reconstruit le menu Administrateur pour un profil donné.
     */
    public function ecraserAdminMenuStructure(int $profilId): void
    {
        $cle = self::CACHE_KEY_ADMIN  . $profilId;
        $tag = self::CACHE_TAG_PREFIX . $profilId;

        $this->cache->delete($cle);
        $this->cache->get($cle, function (ItemInterface $item) use ($tag): array {
            $item->expiresAfter(null);
            $item->tag($tag);
            return $this->construireMenuAdmin();
        });
    }

    /**
     * Retourne la structure du menu Administrateur, filtrée par peutVoir.
     * Chaque groupe n'est inclus que s'il contient au moins un lien accessible.
     */
    public function getAdminMenuStructure(): array
    {
        // Couche 1 : cache intra-requête
        if ($this->cacheAdminMenuStructure !== null) {
            return $this->cacheAdminMenuStructure;
        }

        $profilId = $this->getProfilId();

        if ($profilId === null) {
            return $this->cacheAdminMenuStructure = [];
        }

        // Couche 2 : cache persistant
        $cle = self::CACHE_KEY_ADMIN  . $profilId;
        $tag = self::CACHE_TAG_PREFIX . $profilId;

        return $this->cacheAdminMenuStructure = $this->cache->get($cle, function (ItemInterface $item) use ($tag): array {
            $item->expiresAfter(null);
            $item->tag($tag);
            return $this->construireMenuAdmin();
        });
    }

    /**
     * Construit le menu Admin sans mise en cache.
     * Appelé uniquement par getAdminMenuStructure() via le cache persistant.
     */
    public function construireMenuAdmin(): array
    {
        $groupes  = $this->adminMenuGroupes();
        $resultat = [];

        foreach ($groupes as $groupe) {
            $linksAccessibles = array_values(array_filter(
                $groupe['links'],
                fn(array $link) => $this->hasAccesRoute($link['route'])
            ));

            if (!empty($linksAccessibles)) {
                $resultat[] = [
                    'header' => $groupe['header'],
                    'icon'   => $groupe['icon'],
                    'links'  => $linksAccessibles,
                ];
            }
        }

        return $resultat;
    }

    /**
     * Définition statique des groupes et liens du menu Admin.
     * Modifiez ici pour ajouter / réordonner des entrées admin.
     */
    private function adminMenuGroupes(): array
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
                    ['label' => "Contacts d'agence",     'icon' => 'fa-address-book', 'route' => 'contact_agence_ate_index'],
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

    // =========================================================================
    //  NAVIGATION — recherche du chemin vers une route (breadcrumb)
    // =========================================================================

    public function findChemin(string $nomRoute): array
    {
        foreach ($this->getMenuStructure() as $module) {
            foreach ($module['items'] as $item) {
                if (($item['link'] ?? null) === $nomRoute) {
                    return [
                        ['title' => $module['title'], 'icon' => $module['icon']],
                        ['title' => $item['title'],   'icon' => $item['icon'], 'route' => $nomRoute],
                    ];
                }

                if (!empty($item['subitems'])) {
                    foreach ($item['subitems'] as $subitem) {
                        if (($subitem['link'] ?? null) === $nomRoute) {
                            return [
                                ['title' => $module['title'], 'icon' => $module['icon']],
                                ['title' => $item['title'],   'icon' => $item['icon']],
                                ['title' => $subitem['title'], 'icon' => $subitem['icon'], 'route' => $nomRoute],
                            ];
                        }
                    }
                }
            }
        }

        return [];
    }

    // =========================================================================
    //  INVALIDATION DU CACHE PERSISTANT
    // =========================================================================

    /**
     * Invalide les deux menus (principal + admin) d'un profil donné.
     * À appeler après toute modification des droits/permissions d'un profil.
     *
     * Exemple depuis un contrôleur :
     *   $menuService->invaliderCacheProfil($profilId);
     */
    public function invaliderCacheProfil(int $profilId): void
    {
        $this->cache->invalidateTags([self::CACHE_TAG_PREFIX . $profilId]);

        // Vide aussi le cache intra-requête pour que la même requête soit cohérente
        $this->cacheMenuStructure      = null;
        $this->cacheAdminMenuStructure = null;
    }

    // =========================================================================
    //  HELPERS DE VÉRIFICATION (via UserDataService — zéro BDD)
    // =========================================================================

    private function getProfilId(): ?int
    {
        return $this->userDataService->getProfilId();
    }

    private function hasAccesRoute(string $route): bool
    {
        return $this->userDataService->peutVoir($route);
    }

    // =========================================================================
    //  BUILDERS D'ITEMS
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
