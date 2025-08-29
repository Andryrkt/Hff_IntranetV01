<?php

namespace App\Service\historiqueOperation;

class HistoriqueOperationDevisMagasinService extends HistoriqueOperationService
{
    private const TYPE_OPERATION_SOUMISSION = 1;
    private const TYPE_DOCUMENT = 11;

    public function __construct()
    {
        parent::__construct(self::TYPE_DOCUMENT);
    }

    public function sendNotificationSoumissionSansRedirection(string $message, string $numeroDocument, bool $success = false)
    {
        $this->sendNotificationCore($message, $numeroDocument, self::TYPE_OPERATION_SOUMISSION, $success);
    }
}
