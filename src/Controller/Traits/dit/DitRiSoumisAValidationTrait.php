<?php

namespace App\Controller\Traits\dit;

trait DitRiSoumisAValidationTrait
{
    private function nomUtilisateur()
    {
        $userInfo = $this->getSessionService()->get('user_info', []);
        return $userInfo['username'];
    }

    /**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     */
    private function uplodeFile($form, $ditri, $nomFichier, &$pdfFiles)
    {

        /** @var UploadedFile $file*/
        $file = $form->get($nomFichier)->getData();
        $fileName = 'RI_' . $ditri->getNumeroOR() . '_' . $ditri->getNumeroSoumission() . '-01.' . $file->getClientOriginalExtension();

        $fileDossier = $_ENV['BASE_PATH_FICHIER'] . '/vri/fichier/';

        $file->move($fileDossier, $fileName);

        if ($file->getClientOriginalExtension() === 'pdf') {
            $pdfFiles[] = $fileDossier . $fileName;
        }
    }

    private function envoiePieceJoint($form, $ditri, $fusionPdf)
    {

        $pdfFiles = [];

        for ($i = 1; $i < 5; $i++) {
            $nom = "pieceJoint0{$i}";
            if ($form->get($nom)->getData() !== null) {
                $this->uplodeFile($form, $ditri, $nom, $pdfFiles);
            }
        }
        //ajouter le nom du pdf crée par dit en avant du tableau
        array_unshift($pdfFiles, $_ENV['BASE_PATH_FICHIER'] . '/vri/RI_' . $ditri->getNumeroOR() . '-' . $ditri->getNumeroSoumission() . '.pdf');

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $_ENV['BASE_PATH_FICHIER'] . '/vri/RI_' . $ditri->getNumeroOR() . '-' . $ditri->getNumeroSoumission() . '.pdf';

        // Appeler la fonction pour fusionner les fichiers PDF
        if (!empty($pdfFiles)) {
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
    }
}
