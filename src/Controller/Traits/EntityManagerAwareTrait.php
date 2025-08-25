<?php

namespace App\Controller\Traits;

trait EntityManagerAwareTrait
{
    protected $entityManager;

    public function setEntityManager($entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
