<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\DitTypeDocument;
use App\Entity\admin\dit\DitTypeOperation;
use App\Entity\dit\DitRiSoumisAValidation;
use App\Form\dit\DitRiSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use App\Model\dit\DitRiSoumisAValidationModel;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\dit\DitHistoriqueOperationDocument;
use App\Controller\Traits\dit\DitRiSoumisAValidationTrait;
use App\Service\genererPdf\GenererPdfRiSoumisAValidataion;

class DitRiSoumisAValidationController extends Controller
{
    use DitRiSoumisAValidationTrait;

    /**
     * @Route("/insertion-ri/{numDit}", name="dit_insertion_ri")
     *
     * @return void
     */
    public function riSoumisAValidation(Request $request, $numDit)
    {
        $ditRiSoumiAValidation = new DitRiSoumisAValidation();
        $ditRiSoumiAValidation->setNumeroDit($numDit);

        $form = self::$validator->createBuilder(DitRiSoumisAValidationType::class, $ditRiSoumiAValidation)->getForm();


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        { 
            $ditRiSoumisAValidationModel = new DitRiSoumisAValidationModel();
            $demandeIntervention = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
            $numOrBaseDonner = $demandeIntervention->getNumeroOr();

            if($numOrBaseDonner !== $ditRiSoumiAValidation->getNumeroOR()){
                $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";
                $this->notification($message);
            } else {

                $dataForm = $form->getData();
                $numeroSoumission = $ditRiSoumisAValidationModel->recupNumeroSoumission($dataForm->getNumeroOR());
                $ditRiSoumiAValidation
                ->setNumeroDit($numDit)
                ->setNumeroOR($dataForm->getNumeroOR())
                ->setHeureSoumission($this->getTime())
                ->setDateSoumission(new \DateTime($this->getDatesystem()))
                ->setNumeroSoumission($numeroSoumission)
                ;

                /** ENVOIE des DONNEE dans BASE DE DONNEE */
               // Persist les entités liées
                
                self::$em->persist($ditRiSoumiAValidation); // Persister chaque entité individuellement
            
                $historique = new DitHistoriqueOperationDocument();
                $historique
                    ->setNumeroDocument('RI_'.$dataForm->getNumeroOR().'-'.$ditRiSoumiAValidation->getNumeroSoumission())
                    ->setUtilisateur($this->nomUtilisateur(self::$em))
                    ->setIdTypeDocument(self::$em->getRepository(DitTypeDocument::class)->find(3))
                    ->setIdTypeOperation(self::$em->getRepository(DitTypeOperation::class)->find(2))
                    ;
                self::$em->persist($historique); // Persist l'historique avec les entités liées
                // Flushe toutes les entités et l'historique
                
                self::$em->flush();

                /** @var UploadedFile $file*/
                $file = $form->get("pieceJoint01")->getData();
                $fileName = 'RI_'.$ditRiSoumiAValidation->getNumeroOR().'_'.$ditRiSoumiAValidation->getNumeroSoumission(). '.' . $file->getClientOriginalExtension();
                $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vri/';
            
                $file->move($fileDossier, $fileName);

                $genererPdfRi = new GenererPdfRiSoumisAValidataion();
                $genererPdfRi->copyToDwRiSoumis($ditRiSoumiAValidation->getNumeroSoumission(), $ditRiSoumiAValidation->getNumeroOR());
            
                $this->sessionService->set('notification',['type' => 'success', 'message' => 'Le document de controle a été généré et soumis pour validation']);
                $this->redirectToRoute("dit_index");
            }


        } 

        self::$twig->display('dit/DitRiSoumisAValidation.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}