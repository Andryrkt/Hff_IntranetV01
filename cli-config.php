<?php
// cli-config.php

use Doctrine\ORM\Tools\Console\ConsoleRunner as ORMConsoleRunner;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as MigrationsConsoleRunner;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Symfony\Component\Console\Application;

require_once "doctrineBootstrap.php";

// Configurez l'EntityManager
$helperSet = ORMConsoleRunner::createHelperSet($entityManager);

// Configuration pour les migrations
$config = new PhpFile(__DIR__ . '/config/migrations-config.php');
$dependencyFactory = DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));

// Créez l'application console
$cli = new Application('Doctrine Migrations');

// Ajoutez les commandes de Doctrine ORM
ORMConsoleRunner::addCommands($cli);

// Ajoutez les commandes de Doctrine Migrations
MigrationsConsoleRunner::addCommands($cli, $dependencyFactory);

// Définissez le HelperSet
$cli->setHelperSet($helperSet);

// Exécutez l'application
$cli->run();



