<?php

/**
 * Script de nettoyage du cache
 */

echo "=== NETTOYAGE DU CACHE ===\n\n";

// Dossiers de cache à nettoyer
$cacheDirs = [
    'var/cache/container',
    'var/cache/routes',
    'var/cache/proxies'
];

foreach ($cacheDirs as $dir) {
    if (file_exists($dir)) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✓ Cache nettoyé: $dir\n";
    } else {
        echo "✓ Dossier n'existe pas: $dir\n";
    }
}

echo "\n=== RÉSULTAT ===\n";
echo "✅ Cache nettoyé avec succès !\n";
echo "✅ Exécutez 'php scripts/warmup_cache.php' pour régénérer le cache.\n";
