<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Controller\Traits\dit\DitOrSoumisAValidationTrait;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dit\DitTypeDocument;
use App\Entity\admin\dit\DitTypeOperation;
use App\Entity\dit\DitHistoriqueOperationDocument;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\dit\DitOrsSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Service\genererPdf\GenererPdfOrSoumisAValidation;

class DitOrsSoumisAValidationController extends Controller
{
    use FormatageTrait;
    use DitOrSoumisAValidationTrait;

    private $magasinListOrLivrerModel;

    public function __construct()
    {
        parent::__construct();
        $this->magasinListOrLivrerModel = new MagasinListeOrLivrerModel();
    }
    
    /**
     * @Route("/insertion-or/{numDit}", name="dit_insertion_or")
     *
     * @return void
     */
    public function insertionOr(Request $request, $numDit)
    {
        $ditInsertionOrSoumis = new DitOrsSoumisAValidation();
        $ditInsertionOrSoumis->setNumeroDit($numDit);

        

        $form = self::$validator->createBuilder(DitOrsSoumisAValidationType::class, $ditInsertionOrSoumis)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {   
            $numOrBaseDonner = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit])->getNumeroOr();
            $datePlanning = $this->verificationDatePlanning($ditInsertionOrSoumis);

            if($numOrBaseDonner !== $ditInsertionOrSoumis->getNumeroOR()){
                $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";
                $this->notification($message);
            } elseif($datePlanning === '') {
                $message = "Le numéro Or doit avoir une date planning";
                $this->notification($message);
            } else {
                $numeroVersionMax = self::$em->getRepository(DitOrsSoumisAValidation::class)->findNumeroVersionMax($ditInsertionOrSoumis->getNumeroOR());
                $orSoumisValidationModel = $this->ditModel->recupOrSoumisValidation($ditInsertionOrSoumis->getNumeroOR());
                
            
                $ditInsertionOrSoumis
                                    ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                                    ->setHeureSoumission($this->getTime())
                                    ->setDateSoumission(new \DateTime($this->getDatesystem()))
                                    ;
                $orSoumisValidataion = $this->orSoumisValidataion($orSoumisValidationModel, $numeroVersionMax, $ditInsertionOrSoumis);
                
                /** ENVOIE des DONNEE dans BASE DE DONNEE */
               // Persist les entités liées

                
                foreach ($orSoumisValidataion as $entity) {
                    // Persist l'entité et l'historique
                    self::$em->persist($entity); // Persister chaque entité individuellement
                }
                $historique = new DitHistoriqueOperationDocument();
                $historique->setNumeroDocument($ditInsertionOrSoumis->getNumeroOR())
                    ->setUtilisateur($this->nomUtilisateur(self::$em))
                    ->setIdTypeDocument(self::$em->getRepository(DitTypeDocument::class)->find(1))
                    ->setIdTypeOperation(self::$em->getRepository(DitTypeOperation::class)->find(2))
                    ;
                self::$em->persist($historique); // Persist l'historique avec les entités liées
                // Flushe toutes les entités et l'historique
                self::$em->flush();


                /** CREATION , FUSION, ENVOIE DW du PDF */
                $OrSoumisAvant = self::$em->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvant($ditInsertionOrSoumis->getNumeroOR());
                $OrSoumisAvantMax = self::$em->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvantMax($ditInsertionOrSoumis->getNumeroOR());
                $numDevis = $this->ditModel->recupererNumdevis($ditInsertionOrSoumis->getNumeroOR());
                $montantPdf = $this->montantpdf($orSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax);
                $genererPdfDit = new GenererPdfOrSoumisAValidation();
                $genererPdfDit->GenererPdfOrSoumisAValidation($ditInsertionOrSoumis, $montantPdf, $numDevis);
                //envoie des pièce jointe dans une dossier et la fusionner
                $this->envoiePieceJoint($form, $ditInsertionOrSoumis, $this->fusionPdf);
                $genererPdfDit->copyToDw($ditInsertionOrSoumis->getNumeroVersion(), $ditInsertionOrSoumis->getNumeroOR());



                $this->sessionService->set('notification',['type' => 'success', 'message' => 'Le document de controle a été généré et soumis pour validation']);
                $this->redirectToRoute("dit_index");
            }
        }


        self::$twig->display('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
