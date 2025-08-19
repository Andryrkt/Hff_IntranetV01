<?php

namespace App\Service\navigation;

class BreadcrumbService
{
    private array $items = [];

    public function __construct()
    {
        // Générer automatiquement à partir de l'URL
        $this->generateFromUrl($_ENV['BASE_PATH_APPLICATION']);
    }

    public function add(string $label, ?string $url = null): self
    {
        $this->items[] = ['label' => $label, 'url' => $url];
        return $this;
    }

    public function all(): array
    {
        return $this->items;
    }

    private function generateFromUrl(string $baseUrl): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $parts = array_filter(explode('/', trim($path, '/')));

        $url = rtrim($baseUrl, '/');
        $this->add('Accueil', $baseUrl ?: '/');

        foreach ($parts as $index => $part) {
            $url .= '/' . $part;
            $label = ucfirst(str_replace('-', ' ', $part));

            // Dernier élément = pas de lien
            if ($index === array_key_last($parts)) {
                $this->add($label);
            } else {
                $this->add($label, $url);
            }
        }
    }
}
