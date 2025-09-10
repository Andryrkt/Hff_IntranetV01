<?php

// console.php
require_once 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Bundle\MakerBundle\MakerBundle;

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
$loader->load('services.yaml');

// Enregistrer le MakerBundle
$container->registerExtension(new Symfony\Bundle\MakerBundle\DependencyInjection\MakerExtension());
$container->loadFromExtension('maker', []);

$container->compile();

$application = new Application();
$application->setAutoExit(false);

// Ajouter les commandes du MakerBundle
$makerCommands = $container->get('maker_bundle.command_loader')->getCommandNames();
foreach ($makerCommands as $commandName) {
    $application->add($container->get($commandName));
}

$application->run();
