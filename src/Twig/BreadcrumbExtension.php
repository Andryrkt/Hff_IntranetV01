<?php

namespace App\Twig;

use App\Controller\Traits\lienGenerique;
use Twig\TwigFunction;
use App\Factory\BreadcrumbFactory;
use Twig\Extension\AbstractExtension;
use App\Service\navigation\BreadcrumbMenuService;

class BreadcrumbExtension extends AbstractExtension
{
    use lienGenerique;
    private BreadcrumbMenuService $breadcrumbService;
    private string $baseUrl;

    public function __construct(BreadcrumbMenuService $breadcrumbMenuService)
    {
        $this->breadcrumbService = $breadcrumbMenuService;
        $this->baseUrl = $this->urlGenerique($_ENV['BASE_PATH_COURT']);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('breadcrumbs', [$this, 'generateBreadcrumbs']),
        ];
    }

    public function generateBreadcrumbs(): array
    {
        return (new BreadcrumbFactory(
            $this->baseUrl,
            $this->breadcrumbService
        ))->createFromCurrentUrl();
    }
}
