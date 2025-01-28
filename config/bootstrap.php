<?php
// bootstrap.php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

// On charge la configuration YAML
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load('routes.yaml');

// On compile le container (résolution des dépendances)
$containerBuilder->compile();

return $containerBuilder;
