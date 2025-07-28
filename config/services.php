<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;

// 1️⃣ On instancie le conteneur
$containerBuilder = new ContainerBuilder();

// 2️⃣ Charger tous les fichiers de configuration
$services = [
    'controllers',
    'doctrine',
    'extensionTwig',
    'ldap',
    'logger',
    'parameters',
    'request',
    'routing',
    'session',
    'twig',
    'twigForm',
    'custom_services',
];

foreach ($services as $serviceFile) {
    $serviceLoader = require __DIR__ . "/services/{$serviceFile}.php";
    $serviceLoader($containerBuilder); // Exécuter la fonction retournée
}


// 3️⃣ Compiler et retourner le conteneur
$containerBuilder->compile();

date_default_timezone_set('Indian/Antananarivo');

return $containerBuilder;
