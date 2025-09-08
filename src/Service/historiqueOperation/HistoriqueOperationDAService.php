<?php

namespace App\Service\historiqueOperation;

use Doctrine\ORM\EntityManagerInterface;

class HistoriqueOperationDAService extends HistoriqueOperationService
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, 6);
    }
}
