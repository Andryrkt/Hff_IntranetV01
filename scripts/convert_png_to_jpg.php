<?php
/**
 * Script de conversion PNG -> JPG pour réduire drastiquement le poids
 * Remplace le fond transparent par du blanc.
 */

$assetsDir = 'Views/assets/';
$backupDir = $assetsDir . 'backups/';
$pngFiles = ['Hff.png', 'back.png'];

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

foreach ($pngFiles as $file) {
    $inputPath = $assetsDir . $file;
    $outputPath = $assetsDir . str_replace('.png', '.jpg', $file);

    if (!file_exists($inputPath)) {
        echo "Fichier introuvable : $inputPath\n";
        continue;
    }

    $oldSize = filesize($inputPath);
    echo "Traitement de $file (" . round($oldSize / 1024 / 1024, 2) . " Mo)...\n";

    // Charger le PNG
    $png = @imagecreatefrompng($inputPath);
    if (!$png) {
        echo "   [!] Erreur : Impossible de lire le fichier PNG. Il est peut-être corrompu ou trop lourd pour GD.\n";
        continue;
    }

    $width = imagesx($png);
    $height = imagesy($png);

    // Créer une nouvelle image avec un fond blanc (le JPG ne gère pas la transparence)
    $jpg = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($jpg, 255, 255, 255);
    imagefill($jpg, 0, 0, $white);

    // Copier le PNG sur le fond blanc
    imagecopy($jpg, $png, 0, 0, 0, 0, $width, $height);

    // Sauvegarder en JPG (Qualité 80%)
    if (imagejpeg($jpg, $outputPath, 80)) {
        $newSize = filesize($outputPath);
        echo "   [OK] Converti en JPG : " . round($newSize / 1024, 2) . " Ko\n";
        echo "   [OK] Gain : " . round((1 - ($newSize / $oldSize)) * 100, 2) . "%\n";
        
        // Sauvegarder l'original et supprimer le PNG pour que le site utilise le JPG (si vous mettez à jour vos CSS/HTML)
        // Ou on garde le PNG mais on l'écrase avec une version ultra-compressée
        copy($inputPath, $backupDir . $file);
        echo "   [INFO] Original sauvegardé dans $backupDir\n";
    }

    imagedestroy($png);
    imagedestroy($jpg);
}
