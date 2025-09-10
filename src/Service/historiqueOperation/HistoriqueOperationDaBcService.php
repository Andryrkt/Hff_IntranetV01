<?php

namespace App\Service\historiqueOperation;

use Doctrine\ORM\EntityManagerInterface;
use App\Service\SessionManagerService;
use App\Entity\admin\historisation\documentOperation\TypeDocument;

class HistoriqueOperationDaBcService extends HistoriqueOperationService
{
    public function __construct(EntityManagerInterface $em, SessionManagerService $sessionService)
    {
        parent::__construct($em, $sessionService, TypeDocument::TYPE_DOCUMENT_OR_ID);
    }
}
