<?php

use App\Factory\FrontController;
use App\Factory\RouteCollectionFactory;
use App\Loader\CustomAnnotationClassLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\Config\FileLocator;


return function (\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) {
    // 2) AnnotationReader
$containerBuilder
->register('annotation_reader', AnnotationReader::class)
;

// 3) CustomAnnotationClassLoader
$containerBuilder
->register('custom_annotation_class_loader', CustomAnnotationClassLoader::class)
->setArgument('$reader', $containerBuilder->get('annotation_reader'))
// Si votre CustomAnnotationClassLoader attend d’autres arguments,
// adaptez ici
;

// 4) FileLocator
$containerBuilder
    ->register('file_locator', FileLocator::class)
    ->setArguments([dirname(dirname(__DIR__)) . '/src/Controller']); 

// 5) AnnotationDirectoryLoader
$containerBuilder
    ->register('annotation_directory_loader', AnnotationDirectoryLoader::class)
    ->setArguments([
        new Reference('file_locator'),
        new Reference('custom_annotation_class_loader')
    ]);

// 6) On définit un service "controller_routes" 
//    qui va être construit via la méthode "load(...)"
$definitionControllerRoutes = new Definition(RouteCollection::class);
$definitionControllerRoutes
->setFactory([$containerBuilder->getDefinition('annotation_directory_loader'), 'load'])
->setArguments([dirname(dirname(__DIR__)).'/src/Controller'])
;
$containerBuilder->setDefinition('controller_routes', $definitionControllerRoutes);

// 7) Routes du dossier /src/Api/ (optionnel)
$definitionApiRoutes = new Definition(RouteCollection::class);
$definitionApiRoutes
->setFactory([$containerBuilder->getDefinition('annotation_directory_loader'), 'load'])
->setArguments([dirname(dirname(__DIR__)).'/src/Api'])
;
$containerBuilder->setDefinition('api_routes', $definitionApiRoutes);

// 8) Fusionner les deux collections dans "route_collection"
$containerBuilder->register('route_collection_factory', RouteCollectionFactory::class);

$containerBuilder->register('route_collection', RouteCollection::class)
->setFactory([new Reference('route_collection_factory'), 'createRouteCollection'])
->setArguments([
    new Reference('controller_routes'),
    new Reference('api_routes'),
])
;

// 9) RequestContext
$containerBuilder
->register('request_context', RequestContext::class)
;

// 10) UrlMatcher
$containerBuilder
->register('url_matcher', UrlMatcher::class)
->setArguments([
    // NB: on utilise Reference pour laisser le container injecter
    new Reference('route_collection'),
    new Reference('request_context'),
])
->setPublic(false) // il peut rester privé si on l'injecte plus bas
;

// 11) ControllerResolver
$containerBuilder
->register('controller_resolver', ContainerControllerResolver::class)
->setArguments([new Reference('service_container')])
->setPublic(true)
;

// 12) ArgumentResolver
$containerBuilder
->register('argument_resolver', ArgumentResolver::class)
->setPublic(false)
;


// 14) FrontController (service public "app.front_controller")
$containerBuilder->register('app.front_controller', FrontController::class)
->setArguments([
    new Reference('url_matcher'),
    new Reference('controller_resolver'),
    new Reference('argument_resolver'),
    new Reference('logger'),
])
// On le rend public, car on va le récupérer en mode "entrypoint"
->setPublic(true)
;

// 15) UrlGenerator
$baseUrl = '/' . trim($_ENV['BASE_URL'], '/'); // Assure qu'il y a un seul `/` au début

$containerBuilder->register('request_context', RequestContext::class)
->setArguments([])
->addMethodCall('setBaseUrl', [$baseUrl]);
$containerBuilder->register('routing.url_generator', UrlGenerator::class)
->setArguments([
    new Reference('route_collection'),
    new Reference('request_context')
])
->setPublic(true);
$containerBuilder->setAlias(UrlGeneratorInterface::class, 'routing.url_generator')
->setPublic(true);
};
