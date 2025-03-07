<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Service\fichier\FileUploaderService;
use App\Form\dit\DitCdeSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\genererPdf\GenererPdfCdeSoumisAValidataion;

class DitCdeSoumisAValidationController extends Controller
{
    /**
     * @Route("/soumission-cde", name="dit_insertion_cde")
     */
    public function cdeSoumisAValidation(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(DitCdeSoumisAValidationType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileName = $this->enregistrementFichier($form);

            $this->historiqueOperationService->enregistrerCDE($fileName, 1, 'Succès'); // Historisation de l'opération

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'La commande a été soumis avec succès']);
            $this->redirectToRoute("dit_index");
        }

        $this->logUserVisit('dit_insertion_cde'); // historisation du page visité par l'utilisateur

        self::$twig->display('dit/DitCdeSoumisAValidation.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function enregistrementFichier($form)
    {
        $file = $form->get('pieceJoint01')->getData();
        $chemin = $_ENV['BASE_PATH_FICHIER'].'/cde';
        $fileUploader = new FileUploaderService($chemin);
        if ($file) {
            $prefix = 'cde_';
            $fileName = $fileUploader->upload($file, $prefix);
            $this->envoieDw($fileName);
        }

        return $fileName;
    }

    private function envoieDw($fileName)
    {
        $generePdfCde = new GenererPdfCdeSoumisAValidataion();
        $generePdfCde->copyToDWCdeSoumis($fileName);
    }
}
