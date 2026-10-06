<?php

namespace App\Service\historiqueOperation;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\admin\historisation\documentOperation\TypeDocument;

class HistoriqueOperationRIService extends HistoriqueOperationService
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, TypeDocument::TYPE_DOCUMENT_RI_ID);
    }
}
