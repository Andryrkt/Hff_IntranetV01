<?php

namespace App\Loader;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;

class customAnnotationClassLoader extends AnnotationClassLoader
{
    protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, $annot)
    {
        $route->addDefaults([
            '_controller' => $class->name . '::' . $method->name
        ]);
    }
}
