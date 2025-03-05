<?php

namespace App\Service\historiqueOperation;

class HistoriqueOperationDITService extends HistoriqueOperationService
{
    public function __construct()
    {
        $this->setIdTypeDocument(1);
    }
}
