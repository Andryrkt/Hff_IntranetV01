<?php

namespace App\Service\navigation;

use App\Entity\da\DemandeAppro;
use App\Entity\admin\Application;
use App\Entity\admin\Service;
use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MenuService
{
    private $em;
    private $session;
    private $connectedUser;
    private bool $estAdmin = false;
    private bool $estAtelier = false;
    private bool $estAppro = false;
    private bool $estRH = false;
    private bool $estCreateurDeDADirecte = false;
    private $basePath;
    private $applicationIds = [];
    private $codeAgenceAutorisers = [];

    public function __construct($entityManager, SessionInterface $session)
    {
        $this->em = $entityManager;
        $this->session = $session;
        $this->basePath = $_ENV['BASE_PATH_FICHIER_COURT']; // Chemin de base pour les liens de téléchargement --> /Upload
    }

    /**
     * Get the value of estAdmin
     */
    public function getEstAdmin()
    {
        return $this->estAdmin;
    }

    /**
     * Set the value of estAdmin
     *
     * @return  self
     */
    public function setEstAdmin($estAdmin)
    {
        $this->estAdmin = $estAdmin;

        return $this;
    }

    /**
     * Get the value of estAtelier
     */
    public function getEstAtelier()
    {
        return $this->estAtelier;
    }

    /**
     * Set the value of estAtelier
     *
     * @return  self
     */
    public function setEstAtelier($estAtelier)
    {
        $this->estAtelier = $estAtelier;

        return $this;
    }

    /**
     * Get the value of estAppro
     */
    public function getEstAppro()
    {
        return $this->estAppro;
    }

    /**
     * Set the value of estAppro
     *
     * @return  self
     */
    public function setEstAppro($estAppro)
    {
        $this->estAppro = $estAppro;

        return $this;
    }

    /**
     * Get the value of applicationIds
     */
    public function getApplicationIds()
    {
        return $this->applicationIds;
    }

    /**
     * Set the value of applicationIds
     *
     * @return  self
     */
    public function setApplicationIds($applicationIds)
    {
        $this->applicationIds = $applicationIds;

        return $this;
    }

    /**
     * Get the value of codeAgenceAutorisers
     */
    public function getCodeAgenceAutorisers()
    {
        return $this->codeAgenceAutorisers;
    }

    /**
     * Set the value of codeAgenceAutorisers
     */
    public function setCodeAgenceAutorisers($codeAgenceAutorisers): self
    {
        $this->codeAgenceAutorisers = $codeAgenceAutorisers;

        return $this;
    }

    /**
     * Get the value of connectedUser
     */
    public function getConnectedUser()
    {
        return $this->connectedUser;
    }

    /**
     * Set the value of connectedUser
     *
     * @return  self
     */
    public function setConnectedUser($connectedUser)
    {
        $this->connectedUser = $connectedUser;

        return $this;
    }

    /**
     * Get the value of estCreateurDeDADirecte
     */
    public function getEstCreateurDeDADirecte()
    {
        return $this->estCreateurDeDADirecte;
    }

    /**
     * Set the value of estCreateurDeDADirecte
     *
     * @return  self
     */
    public function setEstCreateurDeDADirecte($estCreateurDeDADirecte)
    {
        $this->estCreateurDeDADirecte = $estCreateurDeDADirecte;

        return $this;
    }

    /**
     * Get the value of estRH
     */
    public function getEstRH()
    {
        return $this->estRH;
    }

    /**
     * Set the value of estRH
     *
     * @return  self
     */
    public function setEstRH($estRH)
    {
        $this->estRH = $estRH;

        return $this;
    }

    /**
     * Définit les informations de l'utilisateur connecté :
     * - son statut admin
     * - la liste de ses applications
     */
    private function setConnectedUserContext()
    {
        if ($this->session->has('user_id')) {
            /** @var User|null $connectedUser */
            $connectedUser = $this->em->getRepository(User::class)->find($this->session->get('user_id'));

            if ($connectedUser) {
                $roleIds = $connectedUser->getRoleIds();
                $serviceIds = $connectedUser->getServiceAutoriserIds();

                $this->setConnectedUser($connectedUser);
                $this->setEstAdmin(in_array(Role::ROLE_ADMINISTRATEUR, $roleIds, true)); // estAdmin
                $this->setEstAppro(in_array(DemandeAppro::ID_APPRO, $serviceIds)); // est appro
                $this->setEstAtelier(in_array(DemandeAppro::ID_ATELIER, $serviceIds)); // est atelier
                $this->setEstRH(in_array(Service::ID_RH, $serviceIds)); // est RH
                $this->setEstCreateurDeDADirecte(in_array(Role::ROLE_DA_DIRECTE, $roleIds, true)); // est créateur de DA directe
                $this->setApplicationIds($connectedUser->getApplicationsIds()); // Les applications autorisées de l'utilisateur connecté
                $this->setCodeAgenceAutorisers($connectedUser->getAgenceAutoriserCode()); // codes des agences autoriser del'utilisateur connecté

            }
        }
    }

    /**
     * Vérifie si l’utilisateur a accès via ses applications
     */
    private function hasAccess(array $requiredIds, array $userApplications): bool
    {
        return !empty(array_intersect($requiredIds, $userApplications));
    }

    /**
     * Retourne la structure du menu organiséegit a
     */
    public function getMenuStructure(): array
    {
        $this->setConnectedUserContext();

        $vignettes = [$this->menuDocumentation()]; // tout le monde
        $estAdmin = $this->getEstAdmin(); // estAdmin
        $applicationIds = $this->getApplicationIds(); // les ids des applications autorisées de l'utilisateur connecté

        // Définition des règles d’accès pour chaque menu
        $menus = [
            [$this->menuReportingBI(), $estAdmin],
            [$this->menuCompta(), $estAdmin || $this->hasAccess([Application::ID_DDP, Application::ID_DDR, Application::ID_BCS], $applicationIds)], // DDP + DDR
            [$this->menuRH(), $estAdmin || $this->hasAccess([Application::ID_DOM, Application::ID_MUT, Application::ID_DDC], $applicationIds)],     // DOM + MUT + DDC
            [$this->menuMateriel(), $estAdmin || $this->hasAccess([Application::ID_BADM, Application::ID_CAS], $applicationIds)], // BDM + CAS
            [$this->menuAtelier(), $estAdmin || $this->hasAccess([Application::ID_DIT, Application::ID_REP], $applicationIds)], // DIT + REP
            [$this->menuMagasin(), $estAdmin || $this->hasAccess([Application::ID_MAG, Application::ID_INV, Application::ID_BDL, Application::ID_CFR, Application::ID_LCF], $applicationIds)], // MAG + INV + BDL + CFR + LCF
            [$this->menuAppro(), $estAdmin || in_array(Application::ID_DAP, $applicationIds, true)],         // DAP
            [$this->menuIT(), $estAdmin || in_array(Application::ID_TIK, $applicationIds, true)],             // TIK
            [$this->menuPOL(), $estAdmin || in_array('60', $this->getCodeAgenceAutorisers())],
            [$this->menuEnergie(), $estAdmin],
            [$this->menuHSE(), $estAdmin],
        ];

        // Ajout uniquement des menus accessibles
        foreach ($menus as [$menu, $condition]) {
            if ($condition) {
                $vignettes[] = $menu;
            }
        }

        return $vignettes;
    }

    public function menuDocumentation()
    {
        $subitems = [
            $this->createSimpleItem('Annuaire', 'address-book', '#'),
            $this->createSimpleItem('Plan analytique HFF', 'ruler-vertical', "{$this->basePath}/documentation/Structure%20analytique%20HFF.pdf", [], "_blank"),
            $this->createSimpleItem('Documentation interne', 'folder-tree', 'documentation_interne'),
        ];
        if ($this->getEstAdmin()) {
            $subitems[] = $this->createSubMenuItem(
                'Contrat',
                'file-contract',
                [
                    $this->createSubItem('Nouveau contrat', 'plus-circle', 'new_contrat', [], "_blank"),
                    $this->createSubItem('Consultation', 'search', '#')
                ]
            );
        }
        return $this->createMenuItem(
            'documentationModal',
            'Documentation',
            'book',
            $subitems,
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
        $subitems = [];

        $subitems[] = $this->createSimpleItem('Cours de change', 'money-bill-wave');
        $subitems[] = $this->createSubMenuItem(
            'Demande de paiement',
            'file-invoice-dollar',
            [
                $this->createSubItem('Nouvelle demande', 'plus-circle', '#', [], 'modalTypeDemande', true),
                $this->createSubItem('Consultation', 'search', 'ddp_liste')
            ]
        );

        if ($this->getEstAdmin() || in_array(Application::ID_BCS, $this->getApplicationIds())) {
            $subitems[] = $this->createSubMenuItem(
                'Bon de caisse',
                'receipt',
                [
                    $this->createSubItem('Nouvelle demande', 'plus-circle', 'new_bon_caisse'),
                    $this->createSubItem('Consultation', 'search', 'bon_caisse_liste')
                ]
            );
        }

        return $this->createMenuItem(
            'comptaModal',
            'Compta',
            'calculator',
            $subitems
        );
    }

    public function menuRH()
    {
        $subitems = [];
        $nomUtilisateur = $this->getConnectedUser() ? $this->getConnectedUser()->getNomUtilisateur() : '';
        if ($this->getEstAdmin() || in_array(Application::ID_DOM, $this->getApplicationIds())) { // DOM
            $subSubitems = [];
            if ($nomUtilisateur != 'roddy') {
                $subSubitems[] = $this->createSubItem('Nouvelle demande', 'plus-circle', 'dom_first_form');
            }
            $subSubitems[] = $this->createSubItem('Consultation', 'search', 'doms_liste');
            $subitems[] = $this->createSubMenuItem(
                'Ordre de mission',
                'file-signature',
                $subSubitems
            );
        }
        if ($this->getEstAdmin() || in_array(Application::ID_MUT, $this->getApplicationIds())) { // MUT
            $subitems[] = $this->createSubMenuItem(
                'Mutations',
                'user-friends',
                [
                    $this->createSubItem('Nouvelle demande', 'plus-circle', 'mutation_nouvelle_demande'),
                    $this->createSubItem('Consultation', 'search', 'mutation_liste')
                ]
            );
        }
        if ($this->getEstAdmin() || in_array(Application::ID_DDC, $this->getApplicationIds())) { // DDC
            $subSubitems = [];
            $subSubitems[] = $this->createSubItem('Nouvelle demande', 'plus-circle', 'new_conge', [], '_blank');
            $subSubitems[] = $this->createSubItem('Annulation de congés validés', 'calendar-xmark', 'annulation_conge', [], '_blank');
            if ($this->getEstAdmin() || $this->getEstRH()) {
                $subSubitems[] = $this->createSubItem('Annulation de congé dédiée RH', 'calendar-xmark', 'annulation_conge_rh', [], '_blank');
            }
            $subSubitems[] = $this->createSubItem('Consultation', 'search', 'conge_liste');
            $subitems[] = $this->createSubMenuItem(
                'Congés',
                'umbrella-beach',
                $subSubitems
            );
        }
        if ($this->getEstAdmin()) {

            $subitems[] = $this->createSubMenuItem(
                'Temporaires',
                'user-clock',
                [
                    $this->createSubItem('Nouvelle demande', 'plus-circle', '#'),
                    $this->createSubItem('Consultation', 'search', '#')
                ]
            );
        }

        return $this->createMenuItem(
            'rhModal',
            'RH',
            'users',
            $subitems,
        );
    }

    public function menuMateriel()
    {
        $subitems = [
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
        ];
        if ($this->getEstAdmin()) {
            $subitems[] = $this->createSimpleItem('Commandes matériels', 'shopping-basket');
            $subitems[] = $this->createSimpleItem('Suivi administratif des matériels', 'clipboard-check');
        }
        return $this->createMenuItem(
            'materielModal',
            'Matériel',
            'snowplow',
            $subitems
        );
    }

    public function menuAtelier()
    {
        $subitems = [];
        $nomUtilisateur = $this->getConnectedUser() ? $this->getConnectedUser()->getNomUtilisateur() : '';
        if ($this->getEstAdmin() || in_array(Application::ID_DIT, $this->getApplicationIds())) { // DIT
            $subSubitems = [];
            if ($nomUtilisateur != 'stg.iaro' && $nomUtilisateur != 'roddy') {
                $subSubitems[] = $this->createSubItem('Nouvelle demande', 'plus-circle', 'dit_new');
                $subSubitems[] = $this->createSubItem('Consultation', 'search', 'dit_index');
            }
            $subSubitems[] = $this->createSubItem('Dossier DIT', 'folder', 'dit_dossier_intervention_atelier');
            $subSubitems[] = $this->createSubItem('Matrice des responsabilités', 'table', "{$this->basePath}/documentation/MATRICE DE RESPONSABILITES OR v9.xlsx");
            $subitems[] = $this->createSubMenuItem(
                'Demande d\'intervention',
                'toolbox',
                $subSubitems
            );
            if ($nomUtilisateur != 'stg.iaro') {
                $subitems[] = $this->createSimpleItem('Glossaire OR', 'book', "{$this->basePath}/dit/glossaire_or/Glossaire_OR.pdf", [], '_blank');
            }
        }
        if ($this->getEstAdmin() || in_array(Application::ID_REP, $this->getApplicationIds())) { // REP
            $subitems[] = $this->createSimpleItem('Planning', 'calendar-alt', 'planning_vue', ['action' => 'oui']);
            $subitems[] = $this->createSimpleItem('Planning détaillé', 'calendar-day', 'liste_planning', ['action' => 'oui']);
        }
        if ($this->getEstAdmin() || in_array(Application::ID_PAT, $this->getApplicationIds())) { // PAT
            $subitems[] = $this->createSimpleItem('Planning interne Atelier', 'calendar-alt', 'planningAtelier_vue');
        }
        if ($this->getEstAdmin()) {
            $subitems[] = $this->createSimpleItem('Satisfaction client (Atelier excellence survey)', 'smile', '#');
        }
        return $this->createMenuItem(
            'atelierModal',
            'Atelier',
            'tools',
            $subitems
        );
    }


    public function menuMagasin()
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
            'id'    => $id,
            'title' => $title,
            'icon'  => 'fas fa-' . $icon,
            'items' => $items,
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
