<?php

namespace App\Bootstrap;

use App\Service\ServiceContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Bootstrap pour initialiser le service container
 */
class ServiceContainerBootstrap
{
    private static bool $initialized = false;

    /**
     * Initialise le service container
     */
    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        try {
            // Créer le conteneur de services
            $container = new ContainerBuilder();

            // Charger la configuration des services
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
            $loader->load('services/services_custom.yaml');

            // Compiler le conteneur
            $container->compile();

            // Définir le conteneur global
            ServiceContainer::setContainer($container);

            self::$initialized = true;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to initialize service container: ' . $e->getMessage(), 0, $e);
        }
    }
}
