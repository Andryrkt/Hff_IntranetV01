<?php

namespace App\Service\navigation;

use App\Service\navigation\MenuGroupe;
use App\Service\UserData\UserDataService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class MenuService
{
    // ─── Configuration du cache persistant ───────────────────────────────────
    public const SUFFIX_PRINCIPAL = 'principal';
    public const SUFFIX_ADMIN     = 'admin';
    public const SUFFIX_VERSION   = 'version';
    public const CACHE_KEY_PREFIX = 'menu.profil_';

    public UserDataService $userDataService;
    private TagAwareCacheInterface $cache;
    private string $basePath;

    /**
     * Cache intra-requête indexé par profilId — évite de reconstruire les menus
     * plusieurs fois dans la même requête ET empêche qu'un profil serve le menu
     * d'un autre profil dans le même process (CLI multi-profils ou Swoole).
     */
    private ?array $cacheMenuStructure      = [];
    private ?array $cacheAdminMenuStructure = [];

    /** Cache mémoire des versions (évite N lectures de pool par requête) */
    private array $cacheVersions = [];

    public function __construct(UserDataService $userDataService, TagAwareCacheInterface $cache)
    {
        $this->userDataService = $userDataService;
        $this->cache           = $cache;
        $this->basePath        = $_ENV['BASE_PATH_FICHIER_COURT'];
    }

    /**
     * Retourne la version courante du profil.
     * Créée automatiquement si elle n'existe pas encore.
     * Mise en cache mémoire pour n'interroger le pool qu'une seule fois par requête.
     */
    private function getVersion(int $profilId): string
    {
        if (isset($this->cacheVersions[$profilId])) {
            return $this->cacheVersions[$profilId];
        }

        $cleVersion = self::CACHE_KEY_PREFIX . $profilId . '.' . self::SUFFIX_VERSION;

        return $this->cacheVersions[$profilId] = $this->cache->get($cleVersion, function (ItemInterface $item): string {
            $item->expiresAfter(null);
            return uniqid('v', true);
        });
    }

    /**
     * Change la version du profil → toutes les clés de données précédentes
     * ne seront plus jamais lues (leurs clés contiennent l'ancienne version).
     */
    public function invaliderVersion(int $profilId): void
    {
        $cleVersion = self::CACHE_KEY_PREFIX . $profilId . '.' . self::SUFFIX_VERSION;
        $this->cache->delete($cleVersion);
        unset($this->cacheVersions[$profilId]);
    }

    /**
     * Construit une clé de cache versionnée : security.profil_{id}.{version}.{suffix}[.{extra}]
     */
    private function buildKey(int $profilId, string $suffix, string $extra = ''): string
    {
        $version = $this->getVersion($profilId);
        $base    = sprintf('%s%d.%s.%s', self::CACHE_KEY_PREFIX, $profilId, $version, $suffix);
        return $extra !== '' ? $base . '.' . $extra : $base;
    }

    // =========================================================================
    //  API PUBLIQUE
    // =========================================================================

    /** 
     * Écrase et reconstruit le menu principal pour un profil donné.
     */
    public function ecraserMenuStructure(int $profilId): void
    {
        // ⚠️ On vide le cache mémoire pour que construireMenuPrincipal() interroge
        // bien UserDataService avec le profilId courant (sinon la couche 1 court-circuite
        // le pool en CLI quand on boucle sur plusieurs profils).
        $this->cacheMenuStructure = null;

        $cle = $this->buildKey($profilId, self::SUFFIX_PRINCIPAL);

        $this->cache->delete($cle);
        $this->cache->get($cle, function (ItemInterface $item): array {
            $item->expiresAfter(null);
            return $this->construireMenuPrincipal();
        });
    }

    /**
     * Retourne la structure du menu principal filtré par peutVoir.
     */
    public function getMenuStructure(): array
    {
        $profilId = $this->getProfilId();

        // Couche 1 : cache intra-requête, indexé par profilId pour éviter
        // qu'un profil serve le menu d'un autre dans le même process.
        if (isset($this->cacheMenuStructure[$profilId])) {
            return $this->cacheMenuStructure[$profilId];
        }

        // Pas de profil connecté → résultat vide, pas mis en cache
        if ($profilId === null) {
            return [];
        }

        // Couche 2 : cache persistant
        $cle = $this->buildKey($profilId, self::SUFFIX_PRINCIPAL);

        return $this->cacheMenuStructure[$profilId] = $this->cache->get($cle, function (ItemInterface $item): array {
            $item->expiresAfter(null);
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
            ['id' => 'documentationModal', 'title' => 'Documentation', 'icon' => 'book',           'groupes' => MenuGroupe::documentationGroupes()],
            ['id' => 'reportingModal',     'title' => 'Reporting',     'icon' => 'chart-line',     'groupes' => MenuGroupe::reportingBIGroupes()],
            ['id' => 'comptaModal',        'title' => 'Compta',        'icon' => 'calculator',     'groupes' => MenuGroupe::comptaGroupes()],
            ['id' => 'rhModal',            'title' => 'RH',            'icon' => 'users',          'groupes' => MenuGroupe::rhGroupes()],
            ['id' => 'materielModal',      'title' => 'Matériel',      'icon' => 'snowplow',       'groupes' => MenuGroupe::materielGroupes()],
            ['id' => 'atelierModal',       'title' => 'Atelier',       'icon' => 'tools',          'groupes' => MenuGroupe::atelierGroupes()],
            ['id' => 'magasinModal',       'title' => 'Magasin',       'icon' => 'dolly',          'groupes' => MenuGroupe::magasinGroupes()],
            ['id' => 'approModal',         'title' => 'Appro',         'icon' => 'shopping-cart',  'groupes' => MenuGroupe::approGroupes()],
            ['id' => 'itModal',            'title' => 'IT',            'icon' => 'laptop-code',    'groupes' => MenuGroupe::itGroupes()],
            ['id' => 'polModal',           'title' => 'POL',           'icon' => 'ring rotate-90', 'groupes' => MenuGroupe::polGroupes()],
            ['id' => 'energieModal',       'title' => 'Energie',       'icon' => 'bolt',           'groupes' => MenuGroupe::energieGroupes()],
            ['id' => 'hseModal',           'title' => 'HSE',           'icon' => 'shield-alt',     'groupes' => MenuGroupe::hseGroupes()],
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
    //  LOGIQUE DE PRÉCHAUFFAGE
    // =========================================================================

    // Supprimer physiquement les clés menu
    public function supprimerClesPhysiques(int $profilId): void
    {
        $this->cache->delete($this->buildKey($profilId, self::SUFFIX_PRINCIPAL));
        $this->cache->delete($this->buildKey($profilId, self::SUFFIX_ADMIN));
    }

    // Reconstruire SANS invalider
    public function reconstruireMenuProfil(int $profilId): void
    {
        $this->ecraserMenuStructure($profilId);
        $this->ecraserAdminMenuStructure($profilId);
    }

    /** 
     * Préchauffe les caches concernant:
     *   - Les menus sur la page d'accueil (qui est utilisé dans le fil d'Ariane)
     *   - Les menus d'administration
     */
    public function warmupMenuProfil(int $profilId): void
    {
        $this->supprimerClesPhysiques($profilId);

        $this->userDataService->setProfilId($profilId);

        $this->invaliderVersion($profilId);
        $this->reconstruireMenuProfil($profilId);
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
        // ⚠️ Même raison que ecraserMenuStructure : vider la couche mémoire
        // pour ne pas court-circuiter le pool lors de la boucle CLI multi-profils.
        $this->cacheAdminMenuStructure = null;

        $cle = $this->buildKey($profilId, self::SUFFIX_ADMIN);

        $this->cache->delete($cle);
        $this->cache->get($cle, function (ItemInterface $item): array {
            $item->expiresAfter(null);
            return $this->construireMenuAdmin();
        });
    }

    /**
     * Retourne la structure du menu Administrateur, filtrée par peutVoir.
     * Chaque groupe n'est inclus que s'il contient au moins un lien accessible.
     */
    public function getAdminMenuStructure(): array
    {
        $subitems = [];
        /** =====================Magasin OR et CIS========================= */
        if ($this->getEstAdmin() || in_array(Application::ID_MAG, $this->getApplicationIds())) { // MAG
            $subitems[] = $this->createSubMenuItem(
                'OR',
                'warehouse',
                [
                    $this->createSubItem('Liste à traiter', 'tasks', 'magasinListe_index'),
                    $this->createSubItem('Liste à livrer', 'truck-loading', 'magasinListe_or_Livrer')
                ]
            );
            $subitems[] = $this->createSubMenuItem(
                'CIS',
                'pallet',
                [
                    $this->createSubItem('Liste à traiter', 'tasks', 'cis_liste_a_traiter'),
                    $this->createSubItem('Liste à livrer', 'truck-loading', 'cis_liste_a_livrer')
                ]
            );
        }
        /** =====================Inventaire========================= */
        if ($this->getEstAdmin() || in_array(Application::ID_INV, $this->getApplicationIds())) { // INV
            $subitems[] = $this->createSubMenuItem(
                'INVENTAIRE',
                'file-alt',
                [
                    $this->createSubItem('Liste inventaire', 'file-alt', 'liste_inventaire', ['action' => 'oui']),
                    $this->createSubItem('Inventaire détaillé', 'file-alt', 'liste_detail_inventaire'),
                ]
            );
        }
        /** =====================sortie de pieces / lubs========================= */
        if ($this->getEstAdmin() || in_array(Application::ID_BDL, $this->getApplicationIds())) { // BDL
            $subitems[] = $this->createSubMenuItem(
                'SORTIE DE PIECES',
                'arrow-left',
                [
                    $this->createSubItem('Nouvelle demande', 'plus-circle', 'bl_soumission'),
                ]
            );
        }
        /** =====================dematerialisation========================= */
        if ($this->getEstAdmin() || in_array(Application::ID_DVM, $this->getApplicationIds())) {
            $subitems[] = $this->createSubMenuItem(
                'DEMATERIALISATION',
                'cloud-arrow-up',
                [
                    $this->createSubItem('Devis', 'file-invoice', 'devis_magasin_liste'),
                    // $this->createSubItem('Commandes clients', 'shopping-basket', '#'),
                    $this->createSubItem('Planning de commande Magasin', 'calendar-alt', 'interface_planningMag'),
                ]
            );
        }
        /** =====================soumission commande fournisseur========================= */
        if ($this->getEstAdmin() || in_array(Application::ID_CFR, $this->getApplicationIds())) { // CFR
            $subitems[] = $this->createSimpleItem('Soumission commandes fournisseur', 'list-alt', 'cde_fournisseur');
        }
        /** =====================liste des commandes fournisseur non generer========================= */
        if ($this->getEstAdmin() || in_array(Application::ID_LCF, $this->getApplicationIds())) { // LCF
            $subitems[] = $this->createSimpleItem('Liste des cmds non placées', 'exclamation-circle', 'liste_Cde_Frn_Non_Placer');
        }

        return $this->createMenuItem(
            'magasinModal',
            'Magasin',
            'dolly',
            $subitems
        );
    }

    public function menuAppro()
    {
        $subitems = [];
        if ($this->getEstAdmin() || $this->getEstAtelier() || $this->getEstCreateurDeDADirecte()) { // admin OU atelier OU créateur de DA directe
            $subitems[] = $this->createSimpleItem('Nouvelle DA', 'file-alt', 'da_first_form');
        }
        $subitems[] = $this->createSimpleItem('Consultation des DA', 'search', 'list_da');
        if ($this->getEstAdmin() || $this->getEstAppro()) {
            $subitems[] = $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul', 'da_list_cde_frn');
        }
        if ($this->getEstAdmin()) {
            $subitems[] = $this->createSimpleItem('Reporting IPS DA reappro', 'chart-bar', 'da_reporting_ips');
        }
        return $this->createMenuItem(
            'approModal',
            'Appro',
            'shopping-cart',
            $subitems
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
        $subitems = [];
        $subitems[] = $this->createSimpleItem('Nouvelle DLUB', 'file-alt');
        $subitems[] = $this->createSimpleItem('Consultation des DLUB', 'search');
        $subitems[] = $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul');

        /** =====================POL OR et CIS========================= */
        if ($this->getEstAdmin() || in_array('60', $this->getCodeAgenceAutorisers())) { // admin uniquement
            $subitems[] = $this->createSubMenuItem(
                'OR',
                'warehouse',
                [
                    $this->createSubItem('Liste à traiter', 'tasks', 'pol_or_liste_a_traiter'),
                    $this->createSubItem('Liste à livrer', 'truck-loading', 'pol_or_liste_a_livrer')
                ]
            );
            $subitems[] = $this->createSubMenuItem(
                'CIS',
                'pallet',
                [
                    $this->createSubItem('Liste à traiter', 'tasks', 'pol_cis_liste_a_traiter'),
                    $this->createSubItem('Liste à livrer', 'truck-loading', 'pol_cis_liste_a_livrer')
                ]
            );
        }
        /** =====================POL Devis magasin========================= */
        $subitems[] = $this->createSimpleItem('Devis negoce pol', 'list-ul', 'devis_magasin_pol_liste');

        $subitems[] = $this->createSimpleItem('Pneumatiques', 'ring');


        return $this->createMenuItem(
            'polModal',
            'POL',
            'ring rotate-90',
            $subitems
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
