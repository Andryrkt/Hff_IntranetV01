<?php

namespace App\Service\navigation;

use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\utilisateur\User;
use App\Service\SessionManagerService;

class MenuService
{
    private $em;
    private $estAdmin;
    private $applicationIds = [];

    public function __construct($entityManager)
    {
        $this->em = $entityManager;
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
     * Retourne la structure du menu organisée
     */
    public function getMenuStructure(): array
    {
        /** @var User $connectedUser objet User correspondant à l'utilisateur connecté en session */
        $estAdmin = false;
        $applicationIds = [];
        $sessionManagerService = new SessionManagerService;
        if ($sessionManagerService->has('user_id')) {
            $connectedUser = $this->em->getRepository(User::class)->find((new SessionManagerService())->get('user_id'));
            $roleIds = $connectedUser->getRoleIds();
            $applicationIds = $connectedUser->getApplicationsIds();
            $this->setApplicationIds($applicationIds);
            $estAdmin = in_array(Role::ROLE_ADMINISTRATEUR, $roleIds);
            $this->setEstAdmin($estAdmin);
        }

        $vignettes = [$this->menuDocumentation()]; // tout le monde

        if ($estAdmin) {
            $vignettes[] = $this->menuReportingBI(); // mbl tsis accès (tsis mahita)
        }
        if ($estAdmin || !empty(array_intersect([9, 14], $applicationIds))) { // DDP + DDR
            $vignettes[] = $this->menuCompta();
        }
        if ($estAdmin || !empty(array_intersect([1, 10], $applicationIds))) { // DOM + MUT
            $vignettes[] = $this->menuRH();
        }
        if ($estAdmin || !empty(array_intersect([2, 3], $applicationIds))) { // BDM + CAS
            $vignettes[] = $this->menuMateriel();
        }
        if ($estAdmin || !empty(array_intersect([4, 6], $applicationIds))) { // DIT + REP
            $vignettes[] = $this->menuAtelier();
        }
        if ($estAdmin || !empty(array_intersect([5, 12, 15, 8, 13], $applicationIds))) { // MAG + INV + BDL + CFR + LCF
            $vignettes[] = $this->menuMagasin();
        }
        if ($estAdmin || in_array(11, $applicationIds)) { // DAP
            $vignettes[] = $this->menuAppro();
        }
        if ($estAdmin || in_array(7, $applicationIds)) { // TIK
            $vignettes[] = $this->menuIT();
        }
        if ($estAdmin) { // tsis mahita
            $vignettes[] = $this->menuPOL();
        }
        if ($estAdmin) { // tsis mahita
            $vignettes[] = $this->menuEnergie();
        }
        if ($estAdmin) { // tsis mahita
            $vignettes[] = $this->menuHSE();
        }

        return $vignettes;
    }

    public function menuDocumentation()
    {
        $subitems = [
            $this->createSimpleItem('Annuaire', 'address-book', '#'),
            $this->createSimpleItem('Plan analytique HFF', 'ruler-vertical', '/Upload/documentation/Structure%20analytique%20HFF.pdf', [], "_blank"),
            $this->createSimpleItem('Documentation interne', 'folder-tree', 'documentation_interne'),
        ];
        if ($this->getEstAdmin()) {
            $subitems[] = $this->createSubMenuItem(
                'Contrat',
                'file-contract',
                [
                    $this->createSubItem('Nouveau contrat', 'plus-circle', 'https://hffc.docuware.cloud/docuware/formsweb/enregistrement-contrats?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be', [], "_blank"),
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
        return $this->createMenuItem(
            'comptaModal',
            'Compta',
            'calculator',
            [
                $this->createSimpleItem('Cours de change', 'money-bill-wave'),
                $this->createSubMenuItem(
                    'Demande de paiement',
                    'file-invoice-dollar',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', '#', [], 'modalTypeDemande', true),
                        $this->createSubItem('Consultation', 'search', 'ddp_liste')
                    ]
                ),
                $this->createSubMenuItem(
                    'Bon de caisse',
                    'receipt',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle', '#'),
                        $this->createSubItem('Consultation', 'search', '#')
                    ]
                )
            ]
        );
    }

    public function menuRH()
    {
        $subitems = [];
        if ($this->getEstAdmin() || in_array(1, $this->getApplicationIds())) { // DOM
            $subitems[] = $this->createSubMenuItem(
                'Ordre de mission',
                'file-signature',
                [
                    $this->createSubItem('Nouvelle demande', 'plus-circle', 'dom_first_form'),
                    $this->createSubItem('Consultation', 'search', 'doms_liste')
                ]
            );
        }
        if ($this->getEstAdmin() || in_array(10, $this->getApplicationIds())) { // MUT
            $subitems[] = $this->createSubMenuItem(
                'Mutations',
                'user-friends',
                [
                    $this->createSubItem('Nouvelle demande', 'plus-circle', 'mutation_nouvelle_demande'),
                    $this->createSubItem('Consultation', 'search', 'mutation_liste')
                ]
            );
        }
        if ($this->getEstAdmin()) {
            $subitems[] = $this->createSubMenuItem(
                'Congés',
                'umbrella-beach',
                [
                    $this->createSubItem('Nouvelle demande', 'plus-circle', 'https://hffc.docuware.cloud/docuware/formsweb/demande-de-conges-new?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be', [], '_blank'),
                    $this->createSubItem('Consultation', 'search', '#')
                ]
            );
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
        if ($this->getEstAdmin() || in_array(4, $this->getApplicationIds())) { // DIT
            $subitems[] = $this->createSubMenuItem(
                'Demande d\'intervention',
                'toolbox',
                [
                    $this->createSubItem('Nouvelle demande', 'plus-circle', 'dit_new'),
                    $this->createSubItem('Consultation', 'search', 'dit_index'),
                    $this->createSubItem('Dossier DIT', 'folder', 'dit_dossier_intervention_atelier')
                ]
            );
            $subitems[] = $this->createSimpleItem('Glossaire OR', 'book', '/Upload/dit/glossaire_or/Glossaire_OR.pdf', [], '_blank');
        }
        if ($this->getEstAdmin() || in_array(6, $this->getApplicationIds())) { // REP
            $subitems[] = $this->createSimpleItem('Planning', 'calendar-alt', 'planning_vue', ['action' => 'oui']);
            $subitems[] = $this->createSimpleItem('Planning détaillé', 'calendar-day', 'liste_planning', ['action' => 'oui']);
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
        if ($this->getEstAdmin() || in_array(5, $this->getApplicationIds())) { // MAG
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
        if ($this->getEstAdmin() || in_array(12, $this->getApplicationIds())) { // INV
            $subitems[] = $this->createSubMenuItem(
                'INVENTAIRE',
                'file-alt',
                [
                    $this->createSubItem('Liste inventaire', 'file-alt', 'liste_inventaire', ['action' => 'oui']),
                    $this->createSubItem('Inventaire détaillé', 'file-alt', 'liste_detail_inventaire', ['action' => 'oui']),
                ]
            );
        }
        if ($this->getEstAdmin() || in_array(15, $this->getApplicationIds())) { // BDL
            $subitems[] = $this->createSubMenuItem(
                'SORTIE DE PIECES / LUBS',
                'arrow-left',
                [
                    $this->createSubItem('Nouvelle demande', 'plus-circle', 'bl_soumission'),
                ]
            );
        }
        if ($this->getEstAdmin() || in_array(8, $this->getApplicationIds())) { // CFR
            $subitems[] = $this->createSimpleItem('Commandes fournisseur', 'list-alt', 'cde_fournisseur');
        }
        if ($this->getEstAdmin() || in_array(13, $this->getApplicationIds())) { // LCF
            $subitems[] = $this->createSimpleItem('Liste des cmds non placées', 'exclamation-circle', 'liste_Cde_Frn_Non_Placer');
        }
        if ($this->getEstAdmin()) {
            $subitems[] = $this->createSimpleItem('Commandes clients', 'shopping-basket');
            $subitems[] = $this->createSimpleItem('Planning magasin', 'calendar-alt');
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
        return $this->createMenuItem(
            'approModal',
            'Appro',
            'shopping-cart',
            [
                $this->createSimpleItem('Nouvelle DA', 'file-alt', 'da_first_form'),
                $this->createSimpleItem('Consultation des DA', 'search', 'list_da'),
                $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul', 'da_list_cde_frn'),
            ]
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
        return $this->createMenuItem(
            'polModal',
            'POL',
            'ring rotate-90',
            [
                $this->createSimpleItem('Nouvelle DLUB', 'file-alt'),
                $this->createSimpleItem('Consultation des DLUB', 'search'),
                $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul'),
                $this->createSimpleItem('Pneumatiques', 'ring'),
            ]
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
            'band'  => 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Ducimus rerum mollitia eius fugiat aut harum ratione ipsum ab suscipit. Eligendi aspernatur tempora minus iusto repellendus a libero officiis, ut provident.',
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
