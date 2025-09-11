<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service container global pour accéder aux services
 */
class ServiceContainer
{
    private static ?ContainerInterface $container = null;

    /**
     * Définit le conteneur de services
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Récupère un service du conteneur
     */
    public static function get(string $id)
    {
        if (self::$container === null) {
            throw new \RuntimeException('Service container not initialized');
        }

        return self::$container->get($id);
    }

    /**
     * Vérifie si un service existe
     */
    public static function has(string $id): bool
    {
        if (self::$container === null) {
            return false;
        }

        return self::$container->has($id);
    }
}
