<?php

/**
 * Script pour remplacer automatiquement les références statiques
 * self::$em et self::$twig par les nouvelles méthodes DI
 */

$controllersDir = __DIR__ . '/../src/Controller';
$filesProcessed = 0;
$replacements = 0;

echo "=== Remplacement des références statiques ===\n";

function processFile($filePath)
{
    global $replacements;

    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Remplacer self::$em par $this->getEntityManager()
    $content = preg_replace('/self::\$em->getRepository\(/i', '$this->getEntityManager()->getRepository(', $content);
    $content = preg_replace('/self::\$em->persist\(/i', '$this->getEntityManager()->persist(', $content);
    $content = preg_replace('/self::\$em->flush\(\)/i', '$this->getEntityManager()->flush()', $content);
    $content = preg_replace('/self::\$em->remove\(/i', '$this->getEntityManager()->remove(', $content);
    $content = preg_replace('/self::\$em->find\(/i', '$this->getEntityManager()->find(', $content);
    $content = preg_replace('/self::\$em->findOneBy\(/i', '$this->getEntityManager()->findOneBy(', $content);
    $content = preg_replace('/self::\$em->findBy\(/i', '$this->getEntityManager()->findBy(', $content);

    // Remplacer self::$twig->display par $this->getTwig()->render
    $content = preg_replace('/self::\$twig->display\(/i', '$this->getTwig()->render(', $content);

    // Remplacer les autres références self::$em
    $content = preg_replace('/self::\$em([^a-zA-Z])/i', '$this->getEntityManager()$1', $content);

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $replacements++;
        echo "✅ Modifié: " . basename($filePath) . "\n";
        return true;
    }

    return false;
}

function scanDirectory($dir)
{
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
echo "🔄 Remplacements effectués: $replacements\n";
echo "✅ Remplacement terminé !\n";
