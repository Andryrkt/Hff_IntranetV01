<?php

namespace App\Service\historiqueOperation;

class HistoriqueOperationDEVService extends HistoriqueOperationService
{
    public function __construct()
    {
        parent::__construct(11);
    }

    public function sendNotificationSoumissionSansRedirection(string $message, string $numeroDocument, bool $success = false)
    {
        $this->sendNotificationCore($message, $numeroDocument, 1, $success);
    }
}
