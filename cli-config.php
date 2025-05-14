<?php
// cli-config.php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/vendor/autoload.php';

// Récupère l'EntityManager depuis ton bootstrap
$entityManager = require_once __DIR__ . '/doctrineBootstrap.php'; // ou bootstrap.php

return ConsoleRunner::createHelperSet($entityManager);




