<?php

/**
 * Script pour convertir les appels display() en retour de Response
 */

$controllersDir = __DIR__ . '/../src/Controller';
$filesProcessed = 0;
$conversions = 0;

echo "=== Conversion display() vers Response ===\n";

function processFile($filePath) {
    global $conversions;
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Pattern pour capturer les appels display() avec leurs paramètres
    $pattern = '/\$this->getTwig\(\)->render\(([^)]+)\);/';
    
    if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        // Traiter les matches en ordre inverse pour éviter les problèmes d'offset
        for ($i = count($matches[0]) - 1; $i >= 0; $i--) {
            $match = $matches[0][$i];
            $params = $matches[1][$i][0];
            $offset = $match[1];
            
            // Remplacer par un retour de Response
            $replacement = "return new \\Symfony\\Component\\HttpFoundation\\Response(\$this->getTwig()->render($params));";
            $content = substr_replace($content, $replacement, $offset, strlen($match[0]));
        }
        
        // Ajouter l'import Response si nécessaire
        if (strpos($content, 'use Symfony\\Component\\HttpFoundation\\Response;') === false) {
            $content = str_replace(
                '<?php',
                "<?php\n\nuse Symfony\\Component\\HttpFoundation\\Response;",
                $content
            );
        }
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $conversions++;
            echo "✅ Converti: " . basename($filePath) . "\n";
            return true;
        }
    }
    
    return false;
}

function scanDirectory($dir) {
    global $filesProcessed;
    
    $files = glob($dir . '/*');
    
    foreach ($files as $file) {
        if (is_dir($file)) {
            scanDirectory($file);
        } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $filesProcessed++;
            processFile($file);
        }
    }
}

// Scanner récursivement tous les contrôleurs
scanDirectory($controllersDir);

echo "\n=== Résumé ===\n";
echo "📁 Fichiers traités: $filesProcessed\n";
echo "🔄 Conversions effectuées: $conversions\n";
echo "✅ Conversion terminée !\n";
