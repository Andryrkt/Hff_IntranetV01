<?php
namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebpackAssetExtension extends AbstractExtension
{
    private $manifest;

    public function __construct()
    {
        $manifestPath = $_ENV['BASE_PATH_LONG'] . '/public/build/manifest.json';

        if (file_exists($manifestPath)) {
            $this->manifest = json_decode(file_get_contents($manifestPath), true);
        } else {
            $this->manifest = [];
        }
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('webpack_asset', [$this, 'getAssetPath']),
        ];
    }

    public function getAssetPath(string $path): string
    {
        return $this->manifest[$path] ?? $path;
    }
}
