<?php

namespace App\Utils;

use Doctrine\ORM\EntityManagerInterface;

class EntityManagerHelper
{
    public static function getEntityManager(): ?EntityManagerInterface
    {
        global $kernel;
        if ($kernel && $kernel->getContainer()) {
            try {
                return $kernel->getContainer()->get('doctrine.orm.default_entity_manager');
            } catch (\Exception $e) {
                // Essayer avec le nom de service alternatif
                try {
                    return $kernel->getContainer()->get('doctrine.orm.default_entity_manager');
                } catch (\Exception $e2) {
                    return null;
                }
            }
        }
        return null;
    }

    public static function getRepository(string $entityClass)
    {
        $em = self::getEntityManager();
        return $em ? $em->getRepository($entityClass) : null;
    }
}
