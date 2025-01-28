<?php
// index.php

require_once __DIR__.'/vendor/autoload.php';

//charger dotev
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// On récupère notre container
$container = require __DIR__.'/config/services.php';

use Symfony\Component\HttpFoundation\Request;

/** @var \Doctrine\ORM\EntityManagerInterface $em */
$em = $container->get('entity_manager');

// Crée la requête depuis les variables globals (GET, POST, SERVER, etc.)
$request = Request::createFromGlobals();

// Récupère l'instance du FrontController (service public)
$frontController = $container->get('app.front_controller');

// Gère la requête et retourne un objet Response
$response = $frontController->handleRequest($request);

// Enfin, on envoie la réponse au client
$response->send();
