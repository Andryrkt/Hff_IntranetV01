<?php

/**
 * Script de démarrage pour s'assurer que l'application est prête
 * Ce script vérifie et régénère les proxies Doctrine si nécessaire
 */

// Vérifier et régénérer les proxies Doctrine
require_once __DIR__ . '/scripts/ensure_proxies.php';

// Le reste du bootstrap de l'application
require_once __DIR__ . '/config/dotenv.php';
