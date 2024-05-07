<?php

// return [
//     [
//         'method' => 'GET',
//         'path' => '/Hffintranet/',
//         'controller' => 'App\\Controller\\BadmController:formBadm'
//     ],
//     [
//         'method' => 'POST',
//         'path' => '/list',
//         'controller' => 'App\\Controller\\BadmController:formCompleBadm'
//     ],
//     [
//         'method' => ['GET', 'POST'],
//         'path' => '/add',
//         'controller' => 'Tutorial\\Fastroute\\Controller\\LinksController:add'
//     ],
// ];


// use Symfony\Component\Routing\Route;
// use Symfony\Component\Routing\RouteCollection;

// $routes = new RouteCollection();

// $routes->add('hello', new Route('/hello/{name}', ['name' => 'World']));

// return $routes;


use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $configurator) {
    $configurator->add('hello', '/hello/{name}');
};
