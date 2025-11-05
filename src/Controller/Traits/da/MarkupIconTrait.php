<?php

namespace App\Controller\Traits\da;

use Twig\Markup;

trait MarkupIconTrait
{
    /**
     * Génère un icône Font Awesome "layered" avec badge texte et retourne un Markup Twig.
     *
     * @param string $mainIcon Classe de l'icône principale (ex: 'fa-cart-shopping')
     * @param string|null $badge Texte du badge (ex: 'DIT')
     * @param string|null $mainColor Couleur icône principale (ex: '#1f6feb')
     * @param string|null $badgeColor Couleur du badge (ex: '#ff6f61')
     * @param string|null $bgColor Couleur du fond (ex: '#e6f2ff')
     * @param string|null $secondaryIcon Icône secondaire (ex: 'fa-bolt')
     * @param string|null $secondaryColor Couleur icône secondaire
     * @param string|null $secondaryOffset Transform CSS pour l’offset (ex: 'translate(0.35em,-0.35em) scale(0.55)')
     * @return Markup
     */
    function faIconLayer(
        string $mainIcon,
        ?string $badge = null,
        ?string $mainColor = '#000',
        ?string $badgeColor = '#ff0000',
        ?string $bgColor = '#e6f2ff',
        ?string $secondaryIcon = null,
        ?string $secondaryColor = null,
        ?string $secondaryOffset = null
    ): Markup {
        $html = '<span class="fa-layers custom">';
        $html .= sprintf('<i class="fa-solid fa-circle fa-layer-base" style="color:%s;"></i>', $bgColor);
        $html .= sprintf('<i class="fa-solid %s" style="color:%s;"></i>', $mainIcon, $mainColor);

        if ($badge) {
            $html .= sprintf(
                '<span class="fa-layers-text" style="background:%s;">%s</span>',
                $badgeColor,
                htmlspecialchars($badge, ENT_QUOTES)
            );
        }

        if ($secondaryIcon) {
            $html .= sprintf(
                '<i class="fa-solid %s layer-offset" style="color:%s; transform:%s;"></i>',
                $secondaryIcon,
                $secondaryColor,
                $secondaryOffset
            );
        }

        $html .= '</span>';

        return new Markup($html, 'UTF-8');
    }

    private function getIconDaAvecDIT(): Markup
    {
        return $this->faIconLayer('fa-cart-shopping', 'DIT', '#1f6feb', '#ff6f61', '#cce0ff');
    }

    private function getIconDaDirect(): Markup
    {
        return $this->faIconLayer('fa-cart-shopping', null, '#b97309',  null, '#ffe8cc',  'fa-bolt',  '#ffb703', 'translate(-30%, -85%) scale(0.7)');
    }

    private function getIconDaReappro(): Markup
    {
        return $this->faIconLayer('fa-calendar-days', null, '#0f5132', null, '#d9f0e5', 'fa-arrows-rotate', '#20c997', 'translate(20%, -130%)');
    }

    private function getAllIcons(): array
    {
        return [
            [
                'color' => '#1f6feb',
                'label' => 'Demande d’approvisionnement via OR',
                'icon'  => $this->getIconDaAvecDIT(),
            ],
            [
                'color' => '#b97309',
                'label' => 'Demande d’achat direct',
                'icon'  => $this->getIconDaDirect(),
            ],
            [
                'color' => '#0f5132',
                'label' => 'Demande de réapprovisionnement mensuel',
                'icon'  => $this->getIconDaReappro(),
            ],
        ];
    }
}
