<?php

/**
 * Script pour remplacer automatiquement toutes les références à self::$validator
 * par $this->getFormFactory() dans tous les contrôleurs
 */

echo "=== Remplacement global de self::\$validator par \$this->getFormFactory() ===\n";

$controllerDir = __DIR__ . '/../src/Controller';
$totalFiles = 0;
$totalReplacements = 0;

function processDirectory($dir)
{
    global $totalFiles, $totalReplacements;

    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $path = $dir . '/' . $file;

        if (is_dir($path)) {
            processDirectory($path);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $totalFiles++;
            $content = file_get_contents($path);
            $originalContent = $content;

            // Remplacer self::$validator par $this->getFormFactory()
            $content = preg_replace('/self::\$validator->createBuilder/', '$this->getFormFactory()->createBuilder', $content);

            // Compter les remplacements
            $replacements = substr_count($originalContent, 'self::$validator->createBuilder') -
                substr_count($content, 'self::$validator->createBuilder');

            if ($replacements > 0) {
                $totalReplacements += $replacements;
                file_put_contents($path, $content);
                echo "✅ {$path}: {$replacements} remplacement(s)\n";
            }
        }
    }
}

// Traiter tous les contrôleurs
processDirectory($controllerDir);

echo "\n=== Résumé ===\n";
echo "📁 Fichiers traités: {$totalFiles}\n";
echo "🔄 Remplacements effectués: {$totalReplacements}\n";
echo "✅ Remplacement terminé !\n";
