<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // 1) Loader
$loaderDefinition = new Definition(FilesystemLoader::class);
$loaderDefinition->setArguments([ 
    [realpath(dirname(__DIR__) . '/../views/templates'), realpath(dirname(__DIR__) . '/../vendor').'/symfony/twig-bridge'. '/Resources/views/Form'] 
]);
$containerBuilder->setDefinition('twig.loader', $loaderDefinition);

// 2) L'environnement Twig
$twigDefinition = new Definition(Environment::class);
$twigDefinition->setArguments([
    // le loader
    new Reference('twig.loader'),
    // options (ex. ['debug' => true])
    ['debug' => true],
]);

// 3.1) Ajouter des extensions (ex. DebugExtension)
$twigDefinition->addMethodCall('addExtension', [new Reference('twig.extension.debug')]);

// 3.2) RoutingExtension
$twigDefinition->addMethodCall('addExtension', [new Reference('twig.extension.routing')]);

// 3.3) FormExtension
$twigDefinition->addMethodCall('addExtension', [new Reference('twig.extension.form')]);

//3.4)AppExtension
$twigDefinition->addMethodCall('addExtension', [ new Reference('app.app_extension') ]);

//3.5)DeleteWordExtension
$twigDefinition->addMethodCall('addExtension', [ new Reference('app.delete_word_extension') ]);

//3.6) AssetExtension
$twigDefinition->addMethodCall('addExtension', [ new Reference('twig.extension.asset')]);

// etc. vous pouvez ajouter d'autres extensions de la même façon
$containerBuilder->setDefinition('twig', $twigDefinition)->setPublic(true);

// 6) Déclarer les extensions elles-mêmes
$containerBuilder->register('twig.extension.debug', DebugExtension::class);
$containerBuilder->register('twig.extension.routing', RoutingExtension::class)
    ->setArguments([
        // le générateur d'URL, à supposer que vous l'ayez enregistré
        new Reference('routing.url_generator')
    ]);
$containerBuilder->register('twig.extension.form', FormExtension::class);

// Alias pour autowiring de Twig
$containerBuilder->setAlias(Environment::class, 'twig')
    ->setPublic(true);
};