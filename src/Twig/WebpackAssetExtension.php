<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebpackAssetExtension extends AbstractExtension
{
    private $manifestPath;
    private $manifestData = null;

    public function __construct()
    {
        // Chemin vers le manifest généré par Webpack pur
        $this->manifestPath = dirname(__DIR__, 2) . '/Public/build/manifest.json';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('webpack_asset', [$this, 'getAssetPath']),
        ];
    }

    public function getAssetPath(string $assetName): string
    {
        // On récupère le chemin de base court défini dans bootstrap_di.php (par défaut /Hffintranet)
        $basePath = $_ENV['BASE_PATH_COURT'] ?? '/Hffintranet';
        
        if ($this->manifestData === null) {
            if (!file_exists($this->manifestPath)) {
                return rtrim($basePath, '/') . '/Public/build/' . $assetName;
            }
            $this->manifestData = json_decode(file_get_contents($this->manifestPath), true);
        }

        if (isset($this->manifestData[$assetName])) {
            // Le manifest contient déjà Public/build/ (d'après webpack.config.js)
            return rtrim($basePath, '/') . '/' . ltrim($this->manifestData[$assetName], '/');
        }

        return rtrim($basePath, '/') . '/Public/build/' . $assetName;
    }
}
