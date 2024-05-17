<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use App\Service\GlobalVariablesService;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $globalVariablesService;

    public function __construct(GlobalVariablesService $globalVariablesService)
    {
        $this->globalVariablesService = $globalVariablesService;
    }

    public function getGlobals(): array
    {
        return [
            'user_connect' => $this->globalVariablesService->getUserConnect(),
            'info_user_cours' => $this->globalVariablesService->getInfoUserCours(),
            'boolean' => $this->globalVariablesService->getBoolean(),
        ];
    }
}
