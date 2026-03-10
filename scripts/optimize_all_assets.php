<?php
/**
 * Script d'optimisation massive des assets (JPG et PNG)
 * Redimensionne à 1920px max et compresse les fichiers > 500 Ko
 */

$assetsDir = 'Views/assets/';
$backupDir = $assetsDir . 'backups/';
$threshold = 500 * 1024; // 500 Ko

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$files = scandir($assetsDir);
$optimizedCount = 0;
$totalGain = 0;

echo "Analyse des assets dans $assetsDir...\n";
echo str_repeat("-", 50) . "\n";

foreach ($files as $file) {
    $filePath = $assetsDir . $file;
    if (is_dir($filePath)) continue;

    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) continue;

    $size = filesize($filePath);
    if ($size < $threshold) continue;

    echo "Optimisation de $file (" . round($size / 1024 / 1024, 2) . " Mo)...\n";

    // 1. Sauvegarde de l'original
    copy($filePath, $backupDir . $file);

    // 2. Chargement de l'image
    $image = null;
    if ($extension === 'png') {
        $image = @imagecreatefrompng($filePath);
    } else {
        $image = @imagecreatefromjpeg($filePath);
    }

    if (!$image) {
        echo "   [!] Erreur : Impossible de traiter ce fichier.\n";
        continue;
    }

    // 3. Redimensionnement (Max 1920px)
    $width = imagesx($image);
    $height = imagesy($image);
    $maxDim = 1920;

    if ($width > $maxDim || $height > $maxDim) {
        $ratio = $width / $height;
        if ($ratio > 1) {
            $newWidth = $maxDim;
            $newHeight = (int)($maxDim / $ratio);
        } else {
            $newHeight = $maxDim;
            $newWidth = (int)($maxDim * $ratio);
        }
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Gérer la transparence pour les PNG
        if ($extension === 'png') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $newImage;
    }

    // 4. Sauvegarde (Compression)
    if ($extension === 'png') {
        // PNG compression level 0-9 (9 est le max)
        imagepng($image, $filePath, 8); 
    } else {
        imagejpeg($image, $filePath, 80);
    }
    
    imagedestroy($image);

    $newSize = filesize($filePath);
    $gain = $size - $newSize;
    $totalGain += $gain;
    $optimizedCount++;

    echo "   [OK] Nouvelle taille : " . round($newSize / 1024, 2) . " Ko (-" . round(($gain / $size) * 100, 2) . "%)\n";
}

echo str_repeat("-", 50) . "\n";
echo "Total : $optimizedCount images optimisées.\n";
echo "Espace disque gagné : " . round($totalGain / 1024 / 1024, 2) . " Mo.\n";
echo "Les originaux sont dans $backupDir\n";
