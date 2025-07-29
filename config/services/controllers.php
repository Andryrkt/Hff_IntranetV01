<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

return function (ContainerBuilder $containerBuilder) {
    $finder = new Finder();

    // Définition des dossiers à scanner
    $directories = [dirname(__DIR__, 2) . '/src/Controller', dirname(__DIR__, 2) . '/src/Service', dirname(__DIR__, 2) . '/src/Api'];

    // Chercher tous les fichiers PHP dans ces dossiers
    $finder->files()->in($directories)->name('*.php');

    foreach ($finder as $file) {
        $relativePath = substr($file->getRealPath(), strlen(dirname(__DIR__, 2)) + 5, -4);
        $className = 'App\\' . str_replace(['/', '\\'], '\\', $relativePath);

        // Vérifier si la classe existe (évite les erreurs de chargement)
        if (! class_exists($className, false)) {
            require_once $file->getRealPath();
        }

        if (! class_exists($className)) {
            continue;
        }

        // Exclure la classe Controller de l'autowiring automatique si elle est déjà définie ailleurs
        if ($className === 'App\\Controller\\Controller') {
            continue;
        }

        // Enregistrement de la classe dans le conteneur
        $containerBuilder->register($className, $className)
            ->setAutowired(true) // Injection automatique des dépendances
            ->setAutoconfigured(true) // Active l'injection des dépendances
            ->setPublic(true);
    }
};
