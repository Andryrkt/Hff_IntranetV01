<?php

namespace core;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\AbstractManagerRegistry;

class SimpleManagerRegistry extends AbstractManagerRegistry
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct('default', [], ['default' => 'default'], '', '', 'Doctrine\ORM\Proxy\Proxy');
    }

    protected function getService($name)
    {
        return $this->entityManager;
    }

    protected function resetService($name)
    {
        // Implémentation non nécessaire pour ce cas
    }

    public function getAliasNamespace($alias)
    {
        throw new \RuntimeException("Aliases not supported.");
    }

    public function getManager($name = null)
    {
        return $this->entityManager;
    }

    public function getManagerNames()
    {
        return ['default'];
    }

    public function getDefaultManagerName()
    {
        return 'default';
    }
}
