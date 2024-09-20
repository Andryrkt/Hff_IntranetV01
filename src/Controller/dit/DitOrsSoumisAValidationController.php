<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\dit\DitOrsSoumisAValidationType;
use App\Service\genererPdf\GenererPdfOrSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitOrsSoumisAValidationController extends Controller
{
    /**
     * @Route("/insertion-or/{numDit}", name="dit_insertion_or")
     *
     * @return void
     */
    public function insertionOr(Request $request, $numDit)
    {
        $ditInsertionOr = new DitOrsSoumisAValidation();
        $ditInsertionOr->setNumeroDit($numDit);

        $form = self::$validator->createBuilder(DitOrsSoumisAValidationType::class, $ditInsertionOr)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {   
            $numOrBaseDonner = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit])->getNumeroOr();
            $numeroVersionMax = self::$em->getRepository(DitOrsSoumisAValidation::class)->findNumeroVersionMax();
            
                $ditInsertionOr->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                ->setHeureSoumission($this->getTime())
            ->setDateSoumission(new \DateTime($this->getDatesystem()));
            
           

            // if($numOrBaseDonner !== $ditInsertionOr->getNumeroOR()){
            //     $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";
            //     $this->notification($message);
            // } else {
                $genererPdfDit = new GenererPdfOrSoumisAValidation();
                $genererPdfDit->GenererPdfOrSoumisAValidation($ditInsertionOr);
                $genererPdfDit->copyToDw($ditInsertionOr->getNumeroVersion());
                //envoie des pièce jointe dans une dossier et la fusionner
                $this->envoiePieceJoint($form, $ditInsertionOr, $this->fusionPdf);

                self::$em->persist($ditInsertionOr);
            self::$em->flush();

                $this->sessionService->set('notification',['type' => 'success', 'message' => 'l\'Or est bien validé']);
                $this->redirectToRoute("dit_index");
            //}
        }


        self::$twig->display('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     */
    private function uplodeFile($form, $ditInsertionOr, $nomFichier, &$pdfFiles)
    {
        
        /** @var UploadedFile $file*/
        $file = $form->get($nomFichier)->getData();
        $fileName = 'oRValidation_' .$ditInsertionOr->getNumeroVersion(). '_01.' . $file->getClientOriginalExtension();
       
        $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/fichier/';
      
        $file->move($fileDossier, $fileName);

        if ($file->getClientOriginalExtension() === 'pdf') {
            $pdfFiles[] = $fileDossier.$fileName;
        }


    }

    private function envoiePieceJoint($form, $ditInsertionOr, $fusionPdf)
    {

        $pdfFiles = [];

        for ($i=1; $i < 2; $i++) { 
        $nom = "pieceJoint0{$i}";
        if($form->get($nom)->getData() !== null){
                $this->uplodeFile($form, $ditInsertionOr, $nom, $pdfFiles);
            }
        }
        //ajouter le nom du pdf crée par dit en avant du tableau
        array_unshift($pdfFiles, $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/oRValidation_' . $ditInsertionOr->getNumeroVersion(). '.pdf');

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/oRValidation_' . $ditInsertionOr->getNumeroVersion(). '.pdf';

        // Appeler la fonction pour fusionner les fichiers PDF
        if (!empty($pdfFiles)) {
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
    }

    private function notification($message)
    {
        $this->sessionService->set('notification',['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dit_index");
    }

    private function autoIncrement($num)
    {
        if($num === null){
            $num = 0;
        }
        return $num + 1;
    }
}
