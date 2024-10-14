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
        $ditRiSoumisAValidationModel = new DitRiSoumisAValidationModel();
        $numOrBaseDonner = $ditRiSoumisAValidationModel->recupNumeroOr($numDit);
        if(empty($numOrBaseDonner)){
            $message = "Le DIT n'a pas encore du numéro OR";
            $this->notification($message);
        }
        $ditRiSoumiAValidation = new DitRiSoumisAValidation();
        $ditRiSoumiAValidation->setNumeroDit($numDit);
        $ditRiSoumiAValidation->setNumeroOR($numOrBaseDonner[0]['numor']);
        
        $itvDejaSoumis = $ditRiSoumisAValidationModel->findItvDejaSoumis($numDit);
        $itvAfficher = $ditRiSoumisAValidationModel->recupInterventionOr($ditRiSoumiAValidation->getNumeroOR(), $itvDejaSoumis);

        $form = self::$validator->createBuilder(DitRiSoumisAValidationType::class, $ditRiSoumiAValidation, [
            'itvAfficher' => $itvAfficher
        ])->getForm();


        $form->handleRequest($request);

        if($form->isSubmitted())
        { 
            $dataForm = $form->getData();
            $itvCoches = [];

            // Récupérer les valeurs des cases cochées
            for ($i = 0; $i < count($itvAfficher); $i++) {
                $checkboxFieldName = 'checkbox_' . $i;
                if ($form->has($checkboxFieldName) && $form->get($checkboxFieldName)->getData()) {
                    $itvCoches[] = (int)$itvAfficher[$i]['numeroitv'];
                }
            }
            $toutNumeroItv = $ditRiSoumisAValidationModel->recupNumeroItv($numOrBaseDonner[0]['numor']);
            
            $existe= false;
            $estSoumis = false;
            foreach ($itvCoches as $value) {
                if(in_array($value, $itvDejaSoumis)){
                    $estSoumis =true;
                    break;
                }
                if(!in_array($value, $toutNumeroItv)){
                    $existe = true;
                }
            }

            if($numOrBaseDonner[0]['numor'] !== $ditRiSoumiAValidation->getNumeroOR()){
                $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";
                $this->notification($message);
            } elseif($estSoumis) {
                $message = "Erreur lors de la soumission, car certaines interventions ont déjà fait l'objet d'une soumission dans DocuWare.";
                $this->notification($message);
            } elseif ($existe) {
                $message = "Erreur lors de la soumission, car certaines interventions n'ont pas encore été validées dans DocuWare.";
                $this->notification($message);
            } else {
                
                $numeroSoumission = $ditRiSoumisAValidationModel->recupNumeroSoumission($dataForm->getNumeroOR());
                $ditRiSoumiAValidation
                ->setNumeroDit($numDit)
                ->setNumeroOR($dataForm->getNumeroOR())
                ->setHeureSoumission($this->getTime())
                ->setDateSoumission(new \DateTime($this->getDatesystem()))
                ->setNumeroSoumission($numeroSoumission)
                ;


                $riSoumis = [];

                foreach ($itvCoches as $value) {
                    $riSoumisAValidation = new DitRiSoumisAValidation();
                    $riSoumisAValidation
                        ->setNumeroDit($numDit)
                        ->setNumeroOR($dataForm->getNumeroOR())
                        ->setHeureSoumission($this->getTime())
                        ->setDateSoumission(new \DateTime($this->getDatesystem()))
                        ->setNumeroSoumission($numeroSoumission)
                        ->setNumeroItv((int)$value)
                    ;
                    $riSoumis[] = $riSoumisAValidation;
                }

                
                /** ENVOIE des DONNEE dans BASE DE DONNEE */
               // Persist les entités liées
                foreach ($riSoumis as $value) {
                    self::$em->persist($value); // Persister chaque entité individuellement
                }

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
                $fileName = 'RI_'.$ditRiSoumiAValidation->getNumeroOR().'-'.$ditRiSoumiAValidation->getNumeroSoumission(). '.' . $file->getClientOriginalExtension();
                $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vri/';
            
                $file->move($fileDossier, $fileName);

                $genererPdfRi = new GenererPdfRiSoumisAValidataion();
                $genererPdfRi->copyToDwRiSoumis($ditRiSoumiAValidation->getNumeroSoumission(), $ditRiSoumiAValidation->getNumeroOR());
            
                $this->sessionService->set('notification',['type' => 'success', 'message' => 'Le rapport d\'intervention a été soumis avec succès']);
                $this->redirectToRoute("dit_index");
            }


        } 


        self::$twig->display('dit/DitRiSoumisAValidation.html.twig', [
            'form' => $form->createView(),
            'itvAfficher' => $itvAfficher
        ]);
    }
}