<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as MigrationsConsoleRunner;

require_once 'vendor/autoload.php';

// Charger le DependencyFactory
$dependencyFactory = require 'config/migrations.php';

// Utilisation des commandes Doctrine ORM et Migrations
$entityManager = $dependencyFactory->getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);

