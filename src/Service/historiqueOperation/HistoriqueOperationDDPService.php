<?php

namespace App\Service\historiqueOperation;

use Doctrine\ORM\EntityManagerInterface;
use App\Service\SessionManagerService;
use App\Entity\admin\historisation\documentOperation\TypeDocument;

class HistoriqueOperationDDPService extends HistoriqueOperationService
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, TypeDocument::TYPE_DOCUMENT_SW_ID); // type Document SW
    }
}
