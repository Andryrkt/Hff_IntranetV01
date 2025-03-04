<?php

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $finder = new Finder();
    $finder->files()->in(dirname(__DIR__, 2) . '/src/Controller')->name('*.php')->depth('>= 0');

    foreach ($finder as $file) {
        $relativePath = substr($file->getRealPath(), strlen(dirname(__DIR__, 2)) + 5, -4); // On enlève "src/"
        $className = 'App\\' . str_replace(['/', '\\'], '\\', $relativePath);

        if (!class_exists($className)) {
            continue;
        }

        $containerBuilder->register($className, $className)
            ->setAutowired(true) // Permet l'injection automatique des dépendances
            ->setAutoconfigured(true) // Active l'injection des dépendances
            ->setPublic(true);
    }
};
