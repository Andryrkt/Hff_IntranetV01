<?php
/**
 * Script d'optimisation d'image lourde
 * Redimensionne à 1920px max et compresse à 80%
 */

$inputPath = 'Views/assets/PIC03.jpg';
$outputPath = 'Views/assets/PIC03_optimized.jpg';

if (!file_exists($inputPath)) {
    die("Erreur : Le fichier source n'existe pas ($inputPath).\n");
}

$originalSize = filesize($inputPath);
echo "Taille originale : " . round($originalSize / 1024 / 1024, 2) . " Mo\n";

// Charger l'image
$image = imagecreatefromjpeg($inputPath);
if (!$image) {
    die("Erreur : Impossible de charger l'image JPEG.\n");
}

$width = imagesx($image);
$height = imagesy($image);

// Redimensionnement si nécessaire (max 1920px)
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
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagedestroy($image);
    $image = $newImage;
    echo "Redimensionné de {$width}x{$height} vers {$newWidth}x{$newHeight}\n";
}

// Sauvegarder avec une compression de 80%
imagejpeg($image, $outputPath, 80);
imagedestroy($image);

$newSize = filesize($outputPath);
echo "Nouvelle taille : " . round($newSize / 1024, 2) . " Ko\n";
echo "Gain : " . round((1 - ($newSize / $originalSize)) * 100, 2) . "%\n";
echo "L'image optimisée a été créée : $outputPath\n";
