<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dit\DitTypeDocument;
use App\Entity\admin\dit\DitTypeOperation;
use App\Entity\dit\DitCdeSoumisAValidation;
use App\Service\fichier\FileUploaderService;
use App\Form\dit\DitCdeSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\dit\DitHistoriqueOperationDocument;
use App\Service\genererPdf\GenererPdfCdeSoumisAValidataion;

class DitCdeSoumisAValidationController extends Controller
{
    /**
     * @Route("/insertion-cde", name="dit_insertion_cde")
     */
    public function cdeSoumisAValidation(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $ditCdeSoumisAValidation = new DitCdeSoumisAValidation();

        
        $form = self::$validator->createBuilder(DitCdeSoumisAValidationType::class)->getForm();
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $fileName = $this->enregistrementFichier($form);
            $this->historique($fileName);


            $this->sessionService->set('notification',['type' => 'success', 'message' => 'La commande a été soumis avec succès']);
            $this->redirectToRoute("dit_index");
        }

        self::$twig->display('dit/DitCdeSoumisAValidation.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function enregistrementFichier($form)
    {
        $file = $form->get('pieceJoint01')->getData();
            $chemin = $_SERVER['DOCUMENT_ROOT'] . '/Upload/cde';
            $fileUploader = new FileUploaderService($chemin);
            if($file) {
                $prefix = 'cde_';
                $fileName = $fileUploader->upload($file, $prefix);
                $this->evoieDw($fileName);
            }

        return $fileName;
    }

    private function evoieDw($fileName)
    {
        $generePdfCde = new GenererPdfCdeSoumisAValidataion();
        $generePdfCde->copyToDWCdeSoumis($fileName);
    }

    private function historique($fileName)
    {
        $historique = new DitHistoriqueOperationDocument();
         //HISOTRIQUE
         $historique
         ->setNumeroDocument($fileName)
         ->setUtilisateur($this->nomUtilisateur(self::$em))
         ->setIdTypeDocument(self::$em->getRepository(DitTypeDocument::class)->find(3))
         ->setIdTypeOperation(self::$em->getRepository(DitTypeOperation::class)->find(2))
         ;
        self::$em->persist($historique);
        self::$em->flush();
    }

    private function nomUtilisateur($em){
        $userId = $this->sessionService->get('user_id', []);
        $user = $em->getRepository(User::class)->find($userId);
        return $user->getNomUtilisateur();
    }
}