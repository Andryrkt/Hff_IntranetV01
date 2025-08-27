<?php

namespace App\Service\historiqueOperation\HistoriqueOperationDaBcService;

use App\Service\historiqueOperation\HistoriqueOperationService;

class HistoriqueOperationDaBcService extends HistoriqueOperationService
{
    public function __construct()
    {
        parent::__construct(2);
    }
}
