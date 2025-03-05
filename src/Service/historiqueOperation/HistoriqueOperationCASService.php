<?php

namespace App\Service\historiqueOperation;

class HistoriqueOperationCASService extends HistoriqueOperationService
{
    public function __construct()
    {
        $this->setIdTypeDocument(9);
    }
}
