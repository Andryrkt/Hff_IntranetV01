<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dit\DitTypeDocument;
use App\Entity\admin\dit\DitTypeOperation;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\dit\DitOrsSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use App\Model\dit\DitOrSoumisAValidationModel;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Entity\dit\DitHistoriqueOperationDocument;
use App\Service\genererPdf\GenererPdfOrSoumisAValidation;
use App\Controller\Traits\dit\DitOrSoumisAValidationTrait;
use App\Model\dit\DitModel;

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
        $ditOrsoumisAValidationModel = new DitOrSoumisAValidationModel();
        $numOrBaseDonner = $ditOrsoumisAValidationModel->recupNumeroOr($numDit);
        if(empty($numOrBaseDonner)){
            $message = "Le DIT n'a pas encore du numéro OR";
            $this->notification($message);
        }
        $ditInsertionOrSoumis = new DitOrsSoumisAValidation();
        $ditInsertionOrSoumis->setNumeroDit($numDit);
        $ditInsertionOrSoumis->setNumeroOR($numOrBaseDonner[0]['numor']);

        $form = self::$validator->createBuilder(DitOrsSoumisAValidationType::class, $ditInsertionOrSoumis)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {  
            $originalName = $form->get("pieceJoint01")->getData()->getClientOriginalName();
            
            if(strpos($originalName, 'Ordre de réparation') !== 0){
            
                $message = "Le fichier '{$originalName}' soumis a été renommé ou ne correspond pas à un OR";
                $this->notification($message);
            }

            $ditInsertionOrSoumis->setNumeroOR(explode('_',$originalName)[1]);

            $demandeIntervention = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
            
            $idMateriel = $ditOrsoumisAValidationModel->recupNumeroMatricule($numDit, $ditInsertionOrSoumis->getNumeroOR());

            $agServDebiteurBDSql = $demandeIntervention->getAgenceServiceDebiteur();
            $datePlanning = $this->verificationDatePlanning($ditInsertionOrSoumis, $ditOrsoumisAValidationModel);
            
            $agServInformix = $this->ditModel->recupAgenceServiceDebiteur($ditInsertionOrSoumis->getNumeroOR());

            $pos = $ditOrsoumisAValidationModel->recupPositonOr($ditInsertionOrSoumis->getNumeroOR()); // Exemple de valeur, vous pouvez la changer selon vos besoins

            $invalidPositions = ['FC', 'FE', 'CP', 'ST'];


            
            if($numOrBaseDonner[0]['numor'] !== $ditInsertionOrSoumis->getNumeroOR()){
                $message = "Echec lors de la soumission, le fichier soumis semble ne pas correspondre à la DIT";
                $this->notification($message);
            } elseif($datePlanning) {
                $message = "Echec de la soumission car il existe une ou plusieurs interventions non planifiées dans l'OR";
                $this->notification($message);
            } elseif(!in_array($agServDebiteurBDSql, $agServInformix)) {
                $message = "Echec de la soumission car l'agence / service débiteur de l'OR ne correspond pas à l'agence / service de la DIT";
                $this->notification($message);
            } elseif (in_array($pos, $invalidPositions)) {
                $message = "Echec de la soumission de l'OR";
                $this->notification($message);
            } elseif ($demandeIntervention->getIdMateriel() !== (int)$idMateriel[0]['nummatricule']) {
                $message = "Echec de la soumission car le materiel de l'OR ne correspond pas au materiel de la DIT";
                $this->notification($message);
            }
            else {
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
               if(count($orSoumisValidataion) > 1){
                   foreach ($orSoumisValidataion as $entity) {
                       // Persist l'entité et l'historique
                       self::$em->persist($entity); // Persister chaque entité individuellement
                    }
                } elseif(count($orSoumisValidataion) === 1) {
                    self::$em->persist($orSoumisValidataion[0]);
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
                $montantPdf = $this->montantpdf($orSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax);
                $quelqueaffichage = $this->quelqueAffichage($ditOrsoumisAValidationModel, $ditInsertionOrSoumis->getNumeroOR());
                $genererPdfDit = new GenererPdfOrSoumisAValidation();
                $genererPdfDit->GenererPdfOrSoumisAValidation($ditInsertionOrSoumis, $montantPdf, $quelqueaffichage);
                //envoie des pièce jointe dans une dossier et la fusionner
                $this->envoiePieceJoint($form, $ditInsertionOrSoumis, $this->fusionPdf);
                $genererPdfDit->copyToDw($ditInsertionOrSoumis->getNumeroVersion(), $ditInsertionOrSoumis->getNumeroOR());

                //modifier la colonne numero_or dans la table demande_intervention
                $dit = self::$em->getrepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
                $dit->setNumeroOR($ditInsertionOrSoumis->getNumeroOR());
                $dit->setStatutOr('Soumis à validation');
                self::$em->flush();

                //redirection
                $this->sessionService->set('notification',['type' => 'success', 'message' => 'Le document de controle a été généré et soumis pour validation']);
                $this->redirectToRoute("dit_index");
            }
        
        }


        self::$twig->display('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function quelqueAffichage($ditOrsoumisAValidationModel, $numOr)
    {
        $numDevis = $this->ditModel->recupererNumdevis($numOr);
        $nbSotrieMagasin = $ditOrsoumisAValidationModel->recupNbPieceMagasin($numOr);
        $nbAchatLocaux = $ditOrsoumisAValidationModel->recupNbAchatLocaux($numOr);
        if(!empty($nbSotrieMagasin)){
            $sortieMagasin = 'OUI';
        } else {
            $sortieMagasin = 'NON';
        }
        if(!empty($nbAchatLocaux)){
            $achatLocaux = 'OUI';
        } else {
            $achatLocaux = 'NON';
        }

        return [
            "numDevis" => $numDevis,
            "sortieMagasin" => $sortieMagasin,
            "achatLocaux" => $achatLocaux
        ];
    }

}
