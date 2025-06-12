<?php

namespace App\Service\historiqueOperation;

class HistoriqueOperationDDPService extends HistoriqueOperationService
{
    public function __construct()
    {
        parent::__construct(15); // type Document SW
    }
}