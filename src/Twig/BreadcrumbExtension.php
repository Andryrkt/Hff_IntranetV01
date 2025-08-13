<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BreadcrumbExtension extends AbstractExtension
{
    private string $baseUrl;

    public function __construct()
    {
        $baseUrl = $_ENV['BASE_PATH_APPLICATION'];
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('breadcrumbs', [$this, 'generateBreadcrumbs']),
        ];
    }

    public function generateBreadcrumbs(): array
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $parts = array_filter(explode('/', trim($path, '/')));

        $breadcrumbs = [];
        $url = $this->baseUrl ?: '';

        // Ajoute l'accueil
        $breadcrumbs[] = [
            'label' => 'Accueil',
            'url'   => $this->baseUrl ?: '/'
        ];

        foreach ($parts as $index => $part) {
            $url .= '/' . $part;
            $label = ucfirst(str_replace('-', ' ', $part));

            if ($index === array_key_last($parts)) {
                $breadcrumbs[] = ['label' => $label, 'url' => null];
            } else {
                $breadcrumbs[] = ['label' => $label, 'url' => $url];
            }
        }

        return $breadcrumbs;
    }
}
