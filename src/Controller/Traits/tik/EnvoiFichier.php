<?php

namespace App\Controller\Traits\tik;

use App\Entity\admin\tik\TkiCommentaires;
use App\Service\fichier\FileUploaderService;

trait EnvoiFichier
{
    /** 
     * Fonction pour le traitement de fichier
     */
    private function traitementEtEnvoiDeFichier($form, TkiCommentaires $commentaire)
    {
        //TRAITEMENT FICHIER
        $fileNames = [];
        // Récupérez les fichiers uploadés depuis le formulaire
        $files        = $form->get('fileNames')->getData();
        $chemin       = $_SERVER['DOCUMENT_ROOT'] . '/Upload/tik/fichiers';
        $fileUploader = new FileUploaderService($chemin);
        if ($files) {
            foreach ($files as $file) {
                // Définissez le préfixe pour chaque fichier, par exemple "DS_" pour "Demande de Support"
                $prefix   = $commentaire->getNumeroTicket() . '_commentaire_';
                $fileName = $fileUploader->upload($file, $prefix);
                // Obtenir la taille du fichier dans l'emplacement final
                $filePath = $chemin . '/' . $fileName;
                $fileSize = round(filesize($filePath) / 1024, 2); // Taille en Ko avec 2 décimales
                if (file_exists($filePath)) {
                    $fileSize = round(filesize($filePath) / 1024, 2);
                } else {
                    $fileSize = 0; // ou autre valeur par défaut ou message d'erreur
                }

                $fileNames[] = [
                    'name' => $fileName,
                    'size' => $fileSize
                ];
            }
        }

        // Enregistrez les noms des fichiers dans votre entité
        $commentaire->setFileNames($fileNames);
    }
}
