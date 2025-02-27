<?php

namespace App\Controller\Traits\dit;

use App\Entity\admin\utilisateur\User;

trait DitRiSoumisAValidationTrait
{
    private function nomUtilisateur($em)
    {
        $userId = $this->sessionService->get('user_id', []);
        $user = $em->getRepository(User::class)->find($userId);
        return $user->getNomUtilisateur();
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

        $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vri/fichier/';

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
        array_unshift($pdfFiles, $_SERVER['DOCUMENT_ROOT'] . '/Upload/vri/RI_' . $ditri->getNumeroOR() . '-' . $ditri->getNumeroSoumission() . '.pdf');

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vri/RI_' . $ditri->getNumeroOR() . '-' . $ditri->getNumeroSoumission() . '.pdf';

        // Appeler la fonction pour fusionner les fichiers PDF
        if (!empty($pdfFiles)) {
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
    }
}
