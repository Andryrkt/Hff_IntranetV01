<?php

namespace App\Twig;

use Twig\TwigFunction;
use App\Factory\BreadcrumbFactory;
use Twig\Extension\AbstractExtension;
use App\Service\navigation\MenuService;
use App\Controller\Traits\lienGenerique;

class BreadcrumbExtension extends AbstractExtension
{
    use lienGenerique;

    private MenuService $menuService;
    private string $baseUrl;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
        $this->baseUrl     = $this->urlGenerique($_ENV['BASE_PATH_COURT']);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('breadcrumbs', [$this, 'generateBreadcrumbs']),
        ];
    }

    public function generateBreadcrumbs(): array
    {
        return (new BreadcrumbFactory($this->baseUrl, $this->menuService))
            ->createFromCurrentUrl();
    }
}
