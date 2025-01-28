<?php

namespace App\Factory;

use Symfony\Component\Routing\RouteCollection;

class RouteCollectionFactory
{
    public function createRouteCollection(RouteCollection $controllerRoutes, RouteCollection $apiRoutes): RouteCollection
    {
        $collection = new RouteCollection();
        $collection->addCollection($controllerRoutes);
        $collection->addCollection($apiRoutes);

        return $collection;
    }
}
