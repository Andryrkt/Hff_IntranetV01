<?php

// migrations.php

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

require_once "doctrineBootstrap.php";

$configuration = new PhpFile(__DIR__ . '/migrations-config.php');
$dependencyFactory = DependencyFactory::fromEntityManager($configuration, new ExistingEntityManager($entityManager));

return $dependencyFactory;
