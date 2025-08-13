<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;


class ProfilControl extends Controller
{
    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->logUserVisit('profil_acceuil'); // historisation du page visité par l'utilisateur

        $menuItems = $this->getMenuStructure();

        self::$twig->display(
            'main/accueil.html.twig',
            [
                'menuItems' => $menuItems
            ]
        );
    }

    /**
     * Retourne la structure du menu organisée
     */
    private function getMenuStructure(): array
    {
        return [
            $this->menuDocumentation(),
            $this->menuReportingBI(),
            $this->menuCompta(),
            $this->menuRH(),
            $this->menuMateriel(),
            $this->menuAtelier(),
            $this->menuMagasin(),
            $this->menuAppro(),
            $this->menuIT(),
            $this->menuPOL(),
            $this->menuEnergie(),
            $this->menuHSE()
        ];
    }

    private function menuDocumentation()
    {
        return $this->createMenuItem(
            'documentationModal',
            'Documentation',
            'folder',
            [
                $this->createSimpleItem('Annuaire', 'address-book', '#'),
                $this->createSimpleItem('Plan analytique HFF', 'ruler-vertical', '#'),
                $this->createSimpleItem('Documentation interne', 'folder-tree', '#')
            ]
        );
    }

    private function menuReportingBI()
    {
        return $this->createMenuItem(
            'reportingModal',
            'Reporting',
            'chart-bar',
            [
                $this->createSimpleItem('Reporting Power BI', null, '#'),
                $this->createSimpleItem('Reporting Excel', null, '#')
            ]
        );
    }

    private function menuCompta()
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
                        $this->createSubItem('Nouvelle demande', 'plus-circle'),
                        $this->createSubItem('Consultation', 'search')
                    ]
                ),
                $this->createSubMenuItem(
                    'Bon de caisse',
                    'receipt',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle'),
                        $this->createSubItem('Consultation', 'search')
                    ]
                )
            ]
        );
    }

    private function menuRH()
    {
        return $this->createMenuItem(
            'rhModal',
            'RH',
            'users',
            [
                $this->createSubMenuItem(
                    'Ordre de mission',
                    'file-signature',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle'),
                        $this->createSubItem('Consultation', 'search')
                    ]
                ),
                $this->createSubMenuItem(
                    'Mutations',
                    'user-friends',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle'),
                        $this->createSubItem('Consultation', 'search')
                    ]
                ),
                $this->createSubMenuItem(
                    'Congés',
                    'umbrella-beach',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle'),
                        $this->createSubItem('Consultation', 'search')
                    ]
                ),
                $this->createSubMenuItem(
                    'Temporaires',
                    'user-clock',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle'),
                        $this->createSubItem('Consultation', 'search')
                    ]
                )
            ]
        );
    }

    private function menuMateriel()
    {
        return $this->createMenuItem(
            'materielModal',
            'Matériel',
            'boxes',
            [
                $this->createSubMenuItem(
                    'Mouvement matériel',
                    'exchange-alt',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle'),
                        $this->createSubItem('Consultation', 'search')
                    ]
                ),
                $this->createSubMenuItem(
                    'Casier',
                    'box-open',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle'),
                        $this->createSubItem('Consultation', 'search')
                    ]
                ),
                $this->createSimpleItem('Commandes matériels', 'shopping-basket'),
                $this->createSimpleItem('Suivi administratif des matériels', 'clipboard-check'),
            ]
        );
    }

    private function menuAtelier()
    {
        return $this->createMenuItem(
            'atelierModal',
            'Atelier',
            'tools',
            [
                $this->createSubMenuItem(
                    'Demande d\'intervention',
                    'toolbox',
                    [
                        $this->createSubItem('Nouvelle demande', 'plus-circle'),
                        $this->createSubItem('Consultation', 'search'),
                        $this->createSubItem('Dossier DIT', 'folder')
                    ]
                ),
                $this->createSimpleItem('Glossaire OR', 'book'),
                $this->createSimpleItem('Planning', 'calendar-alt'),
                $this->createSimpleItem('Planning détaillé', 'calendar-day'),
                $this->createSimpleItem('Satisfaction client (Atelier excellence survey)', 'smile'),
            ]
        );
    }


    private function menuMagasin()
    {
        return $this->createMenuItem(
            'magasinModal',
            'Magasin',
            'store',
            [
                $this->createSubMenuItem(
                    'OR',
                    'warehouse',
                    [
                        $this->createSubItem('Liste à traiter', 'tasks'),
                        $this->createSubItem('Liste à livrer', 'truck-loading')
                    ]
                ),
                $this->createSubMenuItem(
                    'CIS',
                    'pallet',
                    [
                        $this->createSubItem('Liste à traiter', 'tasks'),
                        $this->createSubItem('Liste à livrer', 'truck-loading')
                    ]
                ),
                $this->createSubMenuItem(
                    'Commandes fournisseur',
                    'list-alt',
                    [
                        $this->createSubItem('Liste des cmds non placées', 'exclamation-circle'),
                    ]
                ),
                $this->createSimpleItem('Commandes clients', 'shopping-basket'),
                $this->createSimpleItem('Planning magasin', 'calendar-alt'),
            ]
        );
    }

    private function menuAppro()
    {
        return $this->createMenuItem(
            'approModal',
            'Appro',
            'shopping-cart',
            [
                $this->createSimpleItem('Nouvelle DA', 'file-alt'),
                $this->createSimpleItem('Consultation des DA', 'search'),
                $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul'),
            ]
        );
    }


    private function menuIT()
    {
        return $this->createMenuItem(
            'itModal',
            'IT',
            'server',
            [
                $this->createSimpleItem('support Info')
            ]
        );
    }

    private function menuPOL()
    {
        return $this->createMenuItem(
            'polModal',
            'POL',
            'oil-can',
            [
                $this->createSimpleItem('Nouvelle DLUB', 'file-alt'),
                $this->createSimpleItem('Consultation des DLUB', 'search'),
                $this->createSimpleItem('Liste des commandes fournisseurs', 'list-ul'),
                $this->createSimpleItem('Pneumatiques', 'tire'),
            ]
        );
    }

    private function menuEnergie()
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


    private function menuHSE()
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
    private function createMenuItem(string $id, string $title, string $icon, array $items): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'icon' => $icon,
            'icon_type' => 'fas',
            'items' => $items
        ];
    }

    /**
     * Crée un item simple sans sous-menu
     */
    private function createSimpleItem(string $label, string $icon = null, string $link = '#'): array
    {
        return [
            'label' => $label,
            'link' => $link,
            'icon' => $icon ?? 'file',
            'icon_type' => 'fas'
        ];
    }

    /**
     * Crée un item avec sous-menu
     */
    private function createSubMenuItem(string $label, string $icon, array $subitems): array
    {
        return [
            'label' => $label,
            'icon' => $icon,
            'icon_type' => 'fas',
            'subitems' => $subitems
        ];
    }

    /**
     * Crée un sous-item
     */
    private function createSubItem(string $label, string $icon, string $link = '#'): array
    {
        return [
            'label' => $label,
            'link' => $link,
            'icon' => $icon,
            'icon_type' => 'fas'
        ];
    }
}
