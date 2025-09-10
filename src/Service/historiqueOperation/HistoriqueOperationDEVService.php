<?php

namespace App\Service\historiqueOperation;

use Doctrine\ORM\EntityManagerInterface;
use App\Service\SessionManagerService;
use App\Entity\admin\historisation\documentOperation\TypeDocument;

class HistoriqueOperationDEVService extends HistoriqueOperationService
{
    public function __construct(EntityManagerInterface $em, SessionManagerService $sessionService)
    {
        parent::__construct($em, $sessionService, TypeDocument::TYPE_DOCUMENT_DEV_ID);
    }

    public function sendNotificationSoumissionSansRedirection(string $message, string $numeroDocument, bool $success = false)
    {
        $this->sendNotificationCore($message, $numeroDocument, 1, $success);
    }
}
