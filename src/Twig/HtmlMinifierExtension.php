<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HtmlMinifierExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('minify_html', [$this, 'minifyHtml'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Minifie une chaîne HTML
     */
    public function minifyHtml(string $html): string
    {
        $search = [
            '/\>[^\S ]+/s',     // Retire les espaces après les balises (sauf espace simple)
            '/[^\S ]+\</s',     // Retire les espaces avant les balises (sauf espace simple)
            '/(\s)+/s',         // Raccourcit les séquences d'espaces multiples
            '/<!--(.|\s)*?-->/' // Supprime les commentaires HTML
        ];

        $replace = ['>', '<', '\\1', ''];

        return preg_replace($search, $replace, $html);
    }
}
