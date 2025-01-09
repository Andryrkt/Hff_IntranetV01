<?php

namespace App\Controller\dit;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Form\FormInterface;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Service\fichier\FileUploaderService;
use App\Entity\dit\DitDevisSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use App\Form\dit\DitDevisSoumisAValidationType;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dit\DitDevisSoumisAValidationModel;
use App\Service\autres\MontantPdfService;
use App\Service\genererPdf\GenererPdfDevisSoumisAValidataion;

class DitDevisSoumisAValidationController extends Controller
{

    private $ditDevisSoumisAValidation;
    private $ditDevisSoumisAValidationModel;
    private $montantPdfService;

    public function __construct()
    {
        // Appeler le constructeur parent
        parent::__construct();

        // Initialisation des propriétés
        $this->ditDevisSoumisAValidation = new DitDevisSoumisAValidation(); // entity
        $this->ditDevisSoumisAValidationModel = new DitDevisSoumisAValidationModel(); // model
        $this->montantPdfService = new MontantPdfService();
    }

    /**
     * @Route("/insertion-devis/{numDit}", name="dit_insertion_devis")
     *
     * @return void
     */
    public function insertionDevis(Request $request, $numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $numDevis = $this->numeroDevis($numDit);
        $ditDevisSoumisAValidation = $this->initialistaion($numDit, $numDevis);

        $form = self::$validator->createBuilder(DitDevisSoumisAValidationType::class, $ditDevisSoumisAValidation)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $numeroVersionMax = self::$em->getRepository(DitDevisSoumisAValidation::class)->findNumeroVersionMax($numDevis);
            $devisSoumisAValidationInformix = $this->ditDevisSoumisAValidationModel->recupDevisSoumisValidation($numDevis);
            if(empty($devisSoumisAValidationInformix )) {
                $message = "Erreur lors de la soumission, veuillez réessayer . . . l'information de la devis n'est pas recupérer"; 
                $this->notification($message, $numDevis, "dit_index" ,false);
            }

            $conditionDitIpsDiffDitSqlServ = $devisSoumisAValidationInformix[0]['numero_dit'] <> $numDit;
            /** 
             * TODO : A RECTIFIER le == par <>
             * */ 
            $conditionServDebiteurvide = $devisSoumisAValidationInformix[0]['serv_debiteur'] == '';

            if($conditionDitIpsDiffDitSqlServ) {
                $message = "Erreur lors de la soumission, veuillez réessayer . . . le numero DIT dans IPS ne correspond pas à la DIT";
                $this->notification($message, $numDevis, "dit_index" ,false);
            } elseif ($conditionServDebiteurvide) {
                $message = "Erreur lors de la soumission, veuillez réessayer . . . le service débiteur n'est pas vide";
                $this->notification($message, $numDevis, "dit_index" ,false);
            } else {
                $devisSoumisValidataion = $this->devisSoumisValidataion($devisSoumisAValidationInformix, $numeroVersionMax, $numDevis, $numDit);

                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                $this->envoieDonnerDansBd($devisSoumisValidataion);
                
                /** CREATION , FUSION, ENVOIE DW du PDF */
                $generePdfDevis = new GenererPdfDevisSoumisAValidataion();
                $this->creationPdf($this->ditDevisSoumisAValidation, $devisSoumisValidataion, $this->ditDevisSoumisAValidationModel, $generePdfDevis);
                $fileName= $this->enregistrementEtFusionFichier($form, $numDevis, $devisSoumisValidataion[0]->getNumeroVersion());
                $generePdfDevis->copyToDWDevisSoumis($fileName);// copier le fichier dans docuware
                // $this->historique($fileName); //remplir la table historique
                $this->historiqueOperationService->enregistrerDEV($fileName, 1, "Succès");
                
                $message = 'Le devis a été soumis avec succès';
                $this->notification($message, $numDevis, "dit_index" ,true);
            }
        }

        self::$twig->display('dit/DitDevisSoumisAValidation.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    private function creationPdf($ditInsertionDevis, $devisSoumisValidataion, $ditDevisSoumisAValidationModel, $generePdfDevis)
    {
        dd($ditInsertionDevis);
        $OrSoumisAvant = self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvant($ditInsertionDevis->getNumeroDevis());
        // dump($OrSoumisAvant);
        $OrSoumisAvantMax = self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantMax($ditInsertionDevis->getNumeroDevis());
        // dump($OrSoumisAvantMax);
        $montantPdf = $this->montantPdfService->montantpdf($devisSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax);
        dd($ditInsertionDevis->getNumeroDevis());
        $quelqueaffichage = $this->quelqueAffichage($ditDevisSoumisAValidationModel, $ditInsertionDevis->getNumeroDevis());

        $generePdfDevis->GenererPdfDevisSoumisAValidataion($ditInsertionDevis, $montantPdf, $quelqueaffichage, $this->nomUtilisateur(self::$em)['mailUtilisateur']);
    }
    
    private function quelqueAffichage($ditOrsoumisAValidationModel, $numDevis)
    {
        dd($numDevis);
        $numDevis = $this->ditModel->recupererNumdevis($numDevis);
        $nbAchatLocaux = $ditOrsoumisAValidationModel->recupNbAchatLocaux($numDevis);
        if (!empty($nbAchatLocaux) && $nbAchatLocaux[0]['nbr_achat_locaux'] !== "0") {
            $achatLocaux = 'OUI';
        } else {
            $achatLocaux = 'NON';
        }

        return [
            "numDevis" => $numDevis,
            "achatLocaux" => $achatLocaux
        ];
    }

    private function nomUtilisateur($em){
        $userId = $this->sessionService->get('user_id', []);
        $user = $em->getRepository(User::class)->find($userId);
        return [
            'nomUtilisateur' => $user->getNomUtilisateur(),
            'mailUtilisateur' => $user->getMail()
        ];
    }
    
    
    private function notification(string $message, string $numeroDoc, string $redirection , bool $succes = false)
    {
        if($succes){
            $this->sessionService->set('notification',['type' => 'success', 'message' => $message]);
            $this->historiqueOperationService->enregistrerDEV($numeroDoc, 1, 'Succès'); 
        } else {
            $this->sessionService->set('notification',['type' => 'danger', 'message' => $message]);
            $this->historiqueOperationService->enregistrerDEV($numeroDoc, 1, 'Erreur', $message); 
        }

        $this->redirectToRoute($redirection);
        exit();
    }

    private function envoieDonnerDansBd(array $devisSoumisValidataion) 
    {
        // Persist les entités liées
        if (count($devisSoumisValidataion) > 1) {
            foreach ($devisSoumisValidataion as $entity) {
                // Persist l'entité et l'historique
                self::$em->persist($entity); // Persister chaque entité individuellement
            }
        } elseif (count($devisSoumisValidataion) === 1) {
            self::$em->persist($devisSoumisValidataion[0]);
        }


        // Flushe toutes les entités et l'historique
        self::$em->flush();
    }

 

    private function devisSoumisValidataion($devisSoumisAValidationInformix, $numeroVersionMax, $numDevis, $numDit): array
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
        if ($num === null) {
            $num = 0;
        }
        return $num + 1;
    }

    private function numeroDevis(string $numDit): string
    {
        $numeroDevis = $this->ditDevisSoumisAValidationModel->recupNumeroDevis($numDit);
        if(empty($numeroDevis))
        {
            $message = "Echec , ce DIT n'a pas de numéro devis";
            $this->notification($message, $numeroDevis, "dit_index" ,false);
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
    
    private function enregistrementEtFusionFichier(FormInterface $form, string $numDevis, string $numeroVersion)
    {
            $chemin = $_SERVER['DOCUMENT_ROOT'] . '/Upload/dit/dev';
            $fileUploader = new FileUploaderService($chemin);
            $prefix = 'devis_ctrl';
            $fileName = $fileUploader->chargerEtOuFusionneFichier($form, $prefix, $numDevis, true, $numeroVersion);

        return $fileName;
    }
}