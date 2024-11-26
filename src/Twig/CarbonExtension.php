<?php

namespace App\Twig;

use Carbon\Carbon;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CarbonExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('diffForHumans', [$this, 'diffForHumans']),
        ];
    }

    public function diffForHumans($date): string
    {
        Carbon::setLocale('fr'); // Configure la langue
        return Carbon::parse($date)->diffForHumans();
    }
}
