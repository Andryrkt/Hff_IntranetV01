<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

return function (ContainerBuilder $containerBuilder) {
    // 1) Définition du service "request"
    $requestDefinition = new Definition(Request::class);
    // On utilise la factory statique createFromGlobals()
    $requestDefinition->setFactory([Request::class, 'createFromGlobals']);
    // (facultatif) Rendez-le public si vous voulez le récupérer directement
    $requestDefinition->setPublic(true);

    $containerBuilder->setDefinition('request', $requestDefinition);

    // 2) Définition du service "request_stack"
    $requestStackDefinition = new Definition(RequestStack::class);
    // On empile la requête via un appel de méthode
    $requestStackDefinition->addMethodCall('push', [
        // Référence au service "request"
        new Reference('request'),
    ]);
    $requestStackDefinition->setPublic(true);

    $containerBuilder->setDefinition('request_stack', $requestStackDefinition);
};
