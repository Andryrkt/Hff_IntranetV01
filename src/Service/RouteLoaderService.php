<?php

namespace App\Service;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Service pour charger les routes essentielles depuis des fichiers de configuration
 */
class RouteLoaderService
{
    private $routesConfigPath;

    public function __construct()
    {
        $this->routesConfigPath = dirname(__DIR__, 2) . '/config/routes/essential_routes.php';
    }

    /**
     * Charger seulement les routes CRITIQUES au démarrage
     */
    public function loadEssentialRoutes(): RouteCollection
    {
        $collection = new RouteCollection();

        if (!file_exists($this->routesConfigPath)) {
            throw new \RuntimeException("Fichier de configuration des routes non trouvé: " . $this->routesConfigPath);
        }

        $config = require $this->routesConfigPath;

        // Charger SEULEMENT les routes critiques au démarrage
        if (isset($config['critical_routes'])) {
            $this->addRoutesFromConfig($collection, $config['critical_routes']);
        }

        return $collection;
    }

    /**
     * Charger toutes les routes (critiques + importantes + admin)
     */
    public function loadAllRoutes(): RouteCollection
    {
        $collection = new RouteCollection();

        if (!file_exists($this->routesConfigPath)) {
            throw new \RuntimeException("Fichier de configuration des routes non trouvé: " . $this->routesConfigPath);
        }

        $config = require $this->routesConfigPath;

        // Charger les routes critiques
        if (isset($config['critical_routes'])) {
            $this->addRoutesFromConfig($collection, $config['critical_routes']);
        }

        // Charger les routes importantes
        if (isset($config['important_routes'])) {
            $this->addRoutesFromConfig($collection, $config['important_routes']);
        }

        // Charger les routes d'administration
        if (isset($config['admin_routes'])) {
            $this->addRoutesFromConfig($collection, $config['admin_routes']);
        }

        return $collection;
    }

    /**
     * Ajouter des routes depuis un tableau de configuration
     */
    private function addRoutesFromConfig(RouteCollection $collection, array $routes): void
    {
        foreach ($routes as $name => $config) {
            $route = new Route(
                $config['path'],
                ['_controller' => $config['controller']]
            );

            $collection->add($name, $route);
        }
    }

    /**
     * Obtenir la liste des routes chargées (critiques seulement)
     */
    public function getLoadedRoutes(): array
    {
        $collection = $this->loadEssentialRoutes();
        $routes = [];

        foreach ($collection as $name => $route) {
            $routes[] = [
                'name' => $name,
                'path' => $route->getPath(),
                'controller' => $route->getDefault('_controller')
            ];
        }

        return $routes;
    }

    /**
     * Obtenir toutes les routes disponibles
     */
    public function getAllRoutes(): array
    {
        $collection = $this->loadAllRoutes();
        $routes = [];

        foreach ($collection as $name => $route) {
            $routes[] = [
                'name' => $name,
                'path' => $route->getPath(),
                'controller' => $route->getDefault('_controller')
            ];
        }

        return $routes;
    }
}
