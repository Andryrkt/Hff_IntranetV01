<?php

/**
 * Configuration des sessions pour Hffintranet
 * Ce fichier définit le répertoire de session personnalisé
 */

// Définir le répertoire de session personnalisé
$sessionPath = __DIR__ . '/../var/sessions';

// Créer le répertoire s'il n'existe pas
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}

// Configurer le répertoire de session
ini_set('session.save_path', $sessionPath);

// Autres paramètres de session
ini_set('session.gc_maxlifetime', 3600); // 1 heure
ini_set('session.cookie_lifetime', 3600);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Vérifier que le répertoire est accessible en écriture
if (!is_writable($sessionPath)) {
    error_log("ERREUR: Le répertoire de session n'est pas accessible en écriture: " . $sessionPath);
    // Fallback vers le répertoire temporaire système
    ini_set('session.save_path', sys_get_temp_dir());
}
