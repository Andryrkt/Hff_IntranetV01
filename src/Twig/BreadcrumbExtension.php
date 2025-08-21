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
    private BreadcrumbFactory $breadcrumbFactory;

    public function __construct(BreadcrumbMenuService $breadcrumbMenuService)
    {
        $this->breadcrumbFactory = new BreadcrumbFactory($this->urlGenerique($_ENV['BASE_PATH_COURT']), $breadcrumbMenuService);
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
