<?php

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $finder = new Finder();
    
    // Cherche dans tous les sous-dossiers de /src/Controller, /src/Service et /src/Api
    $directories = ['Controller', 'Service', 'Api'];
    
    foreach ($directories as $dir) {
        $finder->files()->in(dirname(__DIR__, 2) . "/src/$dir")->name('*.php');
    }

    foreach ($finder as $file) {
        $relativePath = substr($file->getRealPath(), strlen(dirname(__DIR__, 2)) + 5, -4);
        $className = 'App\\' . str_replace(['/', '\\'], '\\', $relativePath);

        // Charger la classe si elle n'est pas encore incluse
        require_once $file->getRealPath();

        if (!class_exists($className)) {
            continue;
        }

        $containerBuilder->register($className, $className)
            ->setAutowired(true) // Injection automatique des dépendances
            ->setAutoconfigured(true) // Active l'injection des dépendances
            ->setPublic(true);
    }
};
