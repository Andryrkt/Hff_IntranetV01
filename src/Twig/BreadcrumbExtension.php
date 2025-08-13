<?php

namespace App\Twig;

use App\Factory\BreadcrumbFactory;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BreadcrumbExtension extends AbstractExtension
{
    private BreadcrumbFactory $breadcrumbFactory;

    public function __construct()
    {
        $this->breadcrumbFactory = new BreadcrumbFactory($_ENV['BASE_PATH_APPLICATION']);
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
