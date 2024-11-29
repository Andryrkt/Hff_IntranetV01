<?php

namespace App\Controller\dit;

ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');

use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\DitTypeDocument;
use App\Entity\admin\dit\DitTypeOperation;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\dit\DitFactureSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\dit\DitFactureSoumisAValidationType;
use App\Entity\dit\DitHistoriqueOperationDocument;
use App\Model\dit\DitFactureSoumisAValidationModel;
use App\Service\genererPdf\GenererPdfFactureAValidation;
use App\Controller\Traits\dit\DitFactureSoumisAValidationtrait;
use App\Entity\dit\DitRiSoumisAValidation;

class DitFactureSoumisAValidationController extends Controller
{
    use DitFactureSoumisAValidationtrait;
    /**
     * @Route("/insertion-facture/{numDit}", name="dit_insertion_facture")
     *
     * @return void
     */
    public function factureSoumisAValidation(Request $request, $numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $ditFactureSoumiAValidationModel = new DitFactureSoumisAValidationModel();
        $numOrBaseDonner = $ditFactureSoumiAValidationModel->recupNumeroOr($numDit);
        if(empty($numOrBaseDonner)){
            $message = "Le DIT n'a pas encore du numéro OR";
            $this->notification($message);
        }
        $ditFactureSoumiAValidation = new DitFactureSoumisAValidation();
        $ditFactureSoumiAValidation->setNumeroDit($numDit);
        $ditFactureSoumiAValidation->setNumeroOR($numOrBaseDonner[0]['numor']);

        $form = self::$validator->createBuilder(DitFactureSoumisAValidationType::class, $ditFactureSoumiAValidation)->getForm();

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        { 
            
            //$demandeIntervention = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
            
            $originalName = $form->get("pieceJoint01")->getData()->getClientOriginalName();

            if(strpos($originalName, 'FACTURE CESSION') !== 0){
                $message = "Le fichier '{$originalName}' soumis a été renommé ou ne correspond pas à la facture de l'OR";
                $this->notification($message);
            }

            $ditFactureSoumiAValidation->setNumeroFact(explode('_',$originalName)[1]);

            $nbFactInformix = $ditFactureSoumiAValidationModel->recupNombreFacture($ditFactureSoumiAValidation->getNumeroOR(), $ditFactureSoumiAValidation->getNumeroFact());
            if(empty($nbFactInformix)){
                $nbFact = 0;
            } else {
                $nbFact = $nbFactInformix[0]['nbfact'];
            }

            $nbFactSqlServer = self::$em->getRepository(DitFactureSoumisAValidation::class)->findNbrFact($ditFactureSoumiAValidation->getNumeroFact());
            if($numOrBaseDonner[0]['numor'] !== $ditFactureSoumiAValidation->getNumeroOR()){
                $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";
                $this->notification($message);
            } elseif ($nbFact === 0) {
                $message = "La facture ne correspond pas à l’OR";
                $this->notification($message);
            } elseif ($nbFactSqlServer > 0) {
                $message = "La facture n° :{$ditFactureSoumiAValidation->getNumeroFact()} a été déjà soumise à validation ";
                $this->notification($message);
            }
            else {
                $dataForm = $form->getData();
                $numeroSoumission = $ditFactureSoumiAValidationModel->recupNumeroSoumission($dataForm->getNumeroOR());
                
                $ditFactureSoumiAValidation
                            ->setNumeroDit($numDit)
                            ->setNumeroOR($dataForm->getNumeroOR())
                            ->setNumeroFact($dataForm->getNumeroFact())
                            ->setHeureSoumission($this->getTime())
                            ->setDateSoumission(new \DateTime($this->getDatesystem()))
                            ->setNumeroSoumission($numeroSoumission)
                        ;

                $factureSoumisAValidation = $this->ditFactureSoumisAValidation($numDit, $dataForm, $ditFactureSoumiAValidationModel, $numeroSoumission, self::$em, $ditFactureSoumiAValidation);
                
                $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact($dataForm->getNumeroOR(),$ditFactureSoumiAValidation->getNumeroFact());
                

                $estRi = false;
                $riSoumis = self::$em->getRepository(DitRiSoumisAValidation::class)->findRiSoumis($ditFactureSoumiAValidation->getNumeroOR(), $numDit);
                
                if(empty($riSoumis)){
                    $estRi = true;                
                } else {
                    for ($i=0; $i < count($infoFacture); $i++) { 
                        if( !in_array($infoFacture[$i]['numeroitv'], $riSoumis)){
                            $estRi = true;
                            break;
                        }
                    }
                }

                if($estRi){
                    $message = "La facture ne correspond pas ou correspond partiellement à un rapport d'intervention.";
                    $this->notification($message);
                } else {                    
                        /** CREATION PDF */
                    $orSoumisValidationModel = self::$em->getRepository(DitOrsSoumisAValidation::class)->findOrSoumisValid($ditFactureSoumiAValidation->getNumeroOR());
                    
                    $orSoumisFact = $ditFactureSoumiAValidationModel->recupOrSoumisValidation($ditFactureSoumiAValidation->getNumeroOR(), $dataForm->getNumeroFact());
                    $orSoumisValidataion = $this->orSoumisValidataion($orSoumisValidationModel, $ditFactureSoumiAValidation);
                    $numDevis = $this->ditModel->recupererNumdevis($ditFactureSoumiAValidation->getNumeroOR());
                    $statut = $this->affectationStatutFac(self::$em, $numDit, $dataForm, $ditFactureSoumiAValidationModel, $ditFactureSoumiAValidation);
                    $montantPdf = $this->montantpdf($orSoumisValidataion, $factureSoumisAValidation, $statut, $orSoumisFact);
            
                    $etatOr = $this->etatOr($dataForm, $ditFactureSoumiAValidationModel);
                    
                    $demandeIntervention = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention'=>$numDit]);
                    $demandeIntervention->setEtatFacturation($etatOr);
                    self::$em->persist($demandeIntervention);
                    self::$em->flush();


                    $genererPdfFacture = new GenererPdfFactureAValidation();
                    $genererPdfFacture->GenererPdfFactureSoumisAValidation($ditFactureSoumiAValidation, $numDevis, $montantPdf, $etatOr, $this->nomUtilisateur(self::$em)['emailUtilisateur']);
                    //envoie des pièce jointe dans une dossier et la fusionner
                    $this->envoiePieceJoint($form, $ditFactureSoumiAValidation, $this->fusionPdf);
                    $genererPdfFacture->copyToDwFactureSoumis($ditFactureSoumiAValidation->getNumeroSoumission(), $ditFactureSoumiAValidation->getNumeroFact());
                
                    /** ENVOIE des DONNEE dans BASE DE DONNEE */
                    // Persist les entités liées
                    foreach ($factureSoumisAValidation as $entity) {
                        self::$em->persist($entity); // Persister chaque entité individuellement
                    }
                    $historique = new DitHistoriqueOperationDocument();
                        $historique->setNumeroDocument($dataForm->getNumeroFact())
                            ->setUtilisateur($this->nomUtilisateur(self::$em)['nomUtilisateur'])
                            ->setIdTypeDocument(self::$em->getRepository(DitTypeDocument::class)->find(2))
                            ->setIdTypeOperation(self::$em->getRepository(DitTypeOperation::class)->find(2))
                            ;
                        self::$em->persist($historique); // Persist l'historique avec les entités liées
                        // Flushe toutes les entités et l'historique
                        
                        self::$em->flush();

                    $this->sessionService->set('notification',['type' => 'success', 'message' => 'Le document de controle a été généré et soumis pour validation']);
                    $this->redirectToRoute("dit_index");
                }
            }
        }


        self::$twig->display('dit/DitFactureSoumisAValidation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}