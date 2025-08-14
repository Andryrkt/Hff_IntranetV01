<?php

namespace App\Twig;

use Twig\TwigFunction;
use App\Service\MenuService;
use App\Factory\BreadcrumbFactory;
use Twig\Extension\AbstractExtension;
use App\Service\BreadcrumbMenuService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BreadcrumbExtension extends AbstractExtension
{
    private BreadcrumbFactory $breadcrumbFactory;

    public function __construct(MenuService $menuService, BreadcrumbMenuService $breadcrumbMenuService, UrlGeneratorInterface $urlGenerator)
    {
        $this->breadcrumbFactory = new BreadcrumbFactory($_ENV['BASE_PATH_APPLICATION'], $breadcrumbMenuService, $urlGenerator);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('breadcrumbs', [$this, 'generateBreadcrumbs']),
        ];
    }

    public function generateBreadcrumbs(): array
    {
        return $this->breadcrumbFactory->createFromCurrentUrl();
    }
}
