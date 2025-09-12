<?php

namespace App\Service\historiqueOperation;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\admin\historisation\documentOperation\TypeDocument;

class HistoriqueOperationDevisMagasinService extends HistoriqueOperationService
{
    private const TYPE_OPERATION_SOUMISSION = 1;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, TypeDocument::TYPE_DOCUMENT_DEV_ID);
    }

    public function sendNotificationSoumissionSansRedirection(string $message, string $numeroDocument, bool $success = false)
    {
        $this->sendNotificationCore($message, $numeroDocument, self::TYPE_OPERATION_SOUMISSION, $success);
    }
}
