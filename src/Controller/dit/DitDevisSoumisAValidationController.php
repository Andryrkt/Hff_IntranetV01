<?php

namespace App\Controller\dit;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dit\DitTypeDocument;
use App\Entity\admin\dit\DitTypeOperation;
use App\Service\fichier\FileUploaderService;
use App\Entity\dit\DitDevisSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use App\Form\dit\DitDevisSoumisAValidationType;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dit\DitDevisSoumisAValidationModel;
use App\Entity\dit\DitHistoriqueOperationDocument;
use App\Service\genererPdf\GenererPdfDevisSoumisAValidataion;

class DitDevisSoumisAValidationController extends Controller
{

    private $ditDevisSoumisAValidation;
    private $ditDevisSoumisAValidationModel;
    
    public function __construct()
    {
        // Appeler le constructeur parent
        parent::__construct();

        // Initialisation des propriétés
        $this->ditDevisSoumisAValidation = new DitDevisSoumisAValidation();
        $this->ditDevisSoumisAValidationModel = new DitDevisSoumisAValidationModel();
    }

    /**
     * @Route("/insertion-devis/{numDit}", name="dit_insertion_devis")
     *
     * @return void
     */
    public function insertionDevis(Request $request, $numDit)
    {
        $numDevis = $this->numeroDevis($numDit);
        $ditDevisSoumisAValidation = $this->initialistaion($numDit, $numDevis);

        $form = self::$validator->createBuilder(DitDevisSoumisAValidationType::class, $ditDevisSoumisAValidation)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        { 
            $numeroVersionMax = self::$em->getRepository(DitDevisSoumisAValidation::class)->findNumeroVersionMax($numDevis);
            $devisSoumisAValidationInformix = $this->ditDevisSoumisAValidationModel->recupDevisSoumisValidation($numDevis);
            if(empty($devisSoumisAValidationInformix )) {
                $message = "Echec lors de la soumission, l'information de la devis n'est pas recupérer";
                $this->notification($message);
            }

            $conditionDitIpsDiffDitSqlServ = $devisSoumisAValidationInformix[0]['numero_dit'] <> $numDit;
            $conditionServDebiteurvide = $devisSoumisAValidationInformix[0]['serv_debiteur'] <> '';

            if($conditionDitIpsDiffDitSqlServ) {
                $message = "Echec lors de la soumission, le numero DIT dans IPS ne correspond pas à la DIT";
                $this->notification($message);
            } elseif ($conditionServDebiteurvide) {
                $message = "Echec lors de la soumission, le service débiteur n'est pas vide";
                $this->notification($message);
            } else {
                $devisSoumisValidataion = $this->devisSoumisValidataion($devisSoumisAValidationInformix, $numeroVersionMax, $numDevis, $numDit);
                
                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                $this->envoieDonnerDansBd($devisSoumisValidataion);
                
                $fileName = $this->enregistrementFichier($form);
                $this->evoieDansDw($fileName); // copier le fichier dans docuware
                // $this->historique($fileName); //remplir la table historique
                $this->historiqueOperationService->enregistrerDEV($fileName, 1, "Succès");
                
                $this->sessionService->set('notification',['type' => 'success', 'message' => 'Le devis a été soumis avec succès']);
                $this->redirectToRoute("dit_index");
            }
        }

        self::$twig->display('dit/DitDevisSoumisAValidation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function notification($message)
    {
        $this->sessionService->set('notification',['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dit_index");
        exit();
    }

    private function envoieDonnerDansBd($devisSoumisValidataion) 
    {
        // Persist les entités liées
        if(count($devisSoumisValidataion) > 1){
            foreach ($devisSoumisValidataion as $entity) {
               // Persist l'entité et l'historique
               self::$em->persist($entity); // Persister chaque entité individuellement
            }
        } elseif(count($devisSoumisValidataion) === 1) {
            self::$em->persist($devisSoumisValidataion[0]);
        }
        
        
        // Flushe toutes les entités et l'historique
        self::$em->flush();
    }

    private function evoieDansDw($fileName)
    {
        $generePdfCde = new GenererPdfDevisSoumisAValidataion();
        $generePdfCde->copyToDWDevisSoumis($fileName);
    }

    private function devisSoumisValidataion($devisSoumisAValidationInformix, $numeroVersionMax, $numDevis, $numDit)
    {
        $devisSoumisValidataion = []; // Tableau pour stocker les objets

        foreach ($devisSoumisAValidationInformix as $devisSoumis) {
            // Instancier une nouvelle entité pour chaque entrée du tableau
            $ditInsertionDevis = new DitDevisSoumisAValidation(); 
            
            $ditInsertionDevis
                        ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                        ->setDateHeureSoumission(new \DateTime())
                        ->setNumeroDevis($numDevis)
                        ->setNumeroDit($numDit)
                        ->setNumeroItv($devisSoumis['numero_itv'])
                        ->setNombreLigneItv($devisSoumis['nombre_ligne'])
                        ->setMontantItv($devisSoumis['montant_itv'])
                        ->setMontantPiece($devisSoumis['montant_piece'])
                        ->setMontantMo($devisSoumis['montant_mo'])
                        ->setMontantAchatLocaux($devisSoumis['montant_achats_locaux'])
                        ->setMontantFraisDivers($devisSoumis['montant_divers'])
                        ->setMontantLubrifiants($devisSoumis['montant_lubrifiants'])
                        ->setLibellelItv($devisSoumis['libelle_itv'])
                        ->setStatut('Soumis à validation')
                        ;
            
            $devisSoumisValidataion[] = $ditInsertionDevis; // Ajouter l'objet dans le tableau
        }

        return $devisSoumisValidataion;
    }

    private function autoIncrement($num)
    {
        if($num === null){
            $num = 0;
        }
        return $num + 1;
    }

    private function numeroDevis(string $numDit): string
    {
        $numeroDevis = $this->ditDevisSoumisAValidationModel->recupNumeroDevis($numDit);
        if(empty($numeroDevis))
        {
            $message = "Echec , le numero de devis n'existe pas";
            $this->notification($message);
        } else {
            return $numeroDevis[0]['numdevis'];
        }
    }

    private function initialistaion(string $numDit, string $numDevis): DitDevisSoumisAValidation
    {
        $this->ditDevisSoumisAValidation
            ->setNumeroDit($numDit)
            ->setNumeroDevis($numDevis)
            ->setDateHeureSoumission(new DateTime());
        return $this->ditDevisSoumisAValidation;
    }
    private function enregistrementFichier($form)
    {
        $file = $form->get('pieceJoint01')->getData();
            $chemin = $_SERVER['DOCUMENT_ROOT'] . '/Upload/dev';
            $fileUploader = new FileUploaderService($chemin);
            if($file) {
                $prefix = 'dev_';
                $fileName = $fileUploader->upload($file, $prefix);
            }

        return $fileName;
    }

}