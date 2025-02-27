<?php

namespace App\Controller\dit;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Service\autres\MontantPdfService;
use Symfony\Component\Form\FormInterface;
use App\Service\fichier\FileUploaderService;
use App\Entity\dit\DitDevisSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use App\Form\dit\DitDevisSoumisAValidationType;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dit\DitDevisSoumisAValidationModel;
use App\Service\fichier\GenererNonFichierService;
use App\Repository\dit\DitDevisSoumisAValidationRepository;
use App\Service\genererPdf\GenererPdfDevisSoumisAValidation;
use App\Service\historiqueOperation\HistoriqueOperationDEVService;

class DitDevisSoumisAValidationController extends Controller
{
    private $ditDevisSoumisAValidation;
    private $ditDevisSoumisAValidationModel;
    private $montantPdfService;
    private $generePdfDevis;
    private $historiqueOperation;

    public function __construct()
    {
        // Appeler le constructeur parent
        parent::__construct();

        // Initialisation des propriétés
        $this->ditDevisSoumisAValidation = new DitDevisSoumisAValidation();
        $this->ditDevisSoumisAValidationModel = new DitDevisSoumisAValidationModel(); // model
        $this->montantPdfService = new MontantPdfService();
        $this->generePdfDevis = new GenererPdfDevisSoumisAValidation();
        $this->historiqueOperation = new HistoriqueOperationDEVService;
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
        $ditDevisSoumisAValidation = $this->initialistaion($this->ditDevisSoumisAValidation, $numDit, $numDevis);
        
       
        $form = self::$validator->createBuilder(DitDevisSoumisAValidationType::class, $ditDevisSoumisAValidation)->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $originalName = $form->get("pieceJoint01")->getData()->getClientOriginalName();

            $devisRepository = self::$em->getRepository(DitDevisSoumisAValidation::class);
            $blockages = $this->ConditionDeBlockage($numDevis, $numDit, $devisRepository, $originalName);
            if ($this->blockageSoumission($blockages, $numDevis)) {
                
                $devisSoumisAValidationInformix = $this->InformationDevisInformix($numDevis);

                $numeroVersionMax = $devisRepository->findNumeroVersionMax($numDevis); // recuperation du numero version max
                //ajout des informations vient dans informix dans l'entité devisSoumisAValidation
                $devisSoumisValidataion = $this->devisSoumisValidataion($devisSoumisAValidationInformix, $numeroVersionMax, $numDevis, $numDit, $this->estCeVenteOuForfait($numDevis));
                
                
                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                $this->envoieDonnerDansBd($devisSoumisValidataion);
                $this->editDevisRattacherDit($numDit, $numDevis); //ajout du numero devis dans la table demande_intervention

                /** CREATION , FUSION, ENVOIE DW du PDF */
                $this->creationPdf($devisSoumisValidataion, $this->generePdfDevis);
                $fileNames = $this->enregistrementEtFusionFichier($form, $numDevis, $devisSoumisValidataion[0]->getNumeroVersion());
                $this->generePdfDevis->copyToDWDevisSoumis($fileNames['fileName']);// copier le fichier de controlle dans docuware
                $this->generePdfDevis->copyToDWFichierDevisSoumis($fileNames['nomFichierUploder']);// copier le fichier de devis dans docuware
                

                $message = 'Le devis a été soumis avec succès';
                $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index', true);
            }
        }

        self::$twig->display('dit/DitDevisSoumisAValidation.html.twig', [
            'form' => $form->createView(),
            'numDevis' => $numDevis,
            'numDit' => $numDit
        ]);
    }

    /**
     * Mehode qui crée les conditions de blockage de soumission de devis
     *
     * @param string $numDevis
     * @param string $numDit
     * @param DitDevisSoumisAValidationRepository $devisRepository
     * @return array
     */
    public function ConditionDeBlockage(string $numDevis, string $numDit, DitDevisSoumisAValidationRepository $devisRepository, $originalName): array
    {   
        $TrouverDansDit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        
        if ($TrouverDansDit === null) {
            $message = "Erreur avant la soumission, Impossible de soumettre le devis . . . l'information de la statut du n° DIT $numDit n'est pas récupérer";
            $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
        } else {
            $numClientIps = $this->ditDevisSoumisAValidationModel->recupNumeroClient($numDevis)[0]['numero_client'];
            $numClientIntranet = $TrouverDansDit->getNumeroClient();
            $numDevisNomFichier = !preg_match("/$numDevis/", $originalName);
            $idStatutDit = $TrouverDansDit->getIdStatutDemande()->getId();
            $statutDevis = $devisRepository->findDernierStatutDevis($numDevis);
            $numDitIps = $this->ditDevisSoumisAValidationModel->recupNumDitIps($numDevis)[0]['num_dit'];
            $servDebiteur = $this->ditDevisSoumisAValidationModel->recupServDebiteur($numDevis)[0]['serv_debiteur'];
            $nomFichierCommence = !preg_match('/^devis/i', $originalName);
        }

        return  [
            'numClient' => $numClientIps <> $numClientIntranet, // est -ce le n° client dans IPS est different du n° client dans intranet
            'numDevisNomFichier' => $numDevisNomFichier, // le n° devis contient sur le nom de fichier?
            'conditionDitIpsDiffDitSqlServ' => $numDitIps <> $numDit, // n° dit ips <> n° dit intranet
            'conditionServDebiteurvide' => $servDebiteur <> '', // le service debiteur n'est pas vide
            'conditionStatutDit' => $idStatutDit <> 51, // le statut DIT est-il différent de AFFECTER SECTION
            'conditionStatutDevis' => $statutDevis === 'Soumis à validation', // le statut de la dernière version de devis est-il Soumis à validation 
            'conditionNomCommence' => $nomFichierCommence // le nom de fichier telechager commence-t-il par "DEVIS"
        ];
    }

    /**
     * METHODE pour les condition de blockage de soumision devis
     *
     * @param array $blockages
     * @param string $numDevis
     * @return boolean
     */
    public function blockageSoumission(array $blockages, string $numDevis): bool
    {
        if ($blockages['numDevisNomFichier']) {
            $message = " Erreur lors de la soumission, Impossible de soumettre le devis . . . Veuillez vérifier le fichier uploadé car il ne correspond pas au numéro au devis N° { $numDevis} ";
            $this->historiqueOperation->sendNotificationSoumission($message, $numDevis, 'dit_index');
        } elseif ($blockages['numClient']) {
            $message = " Erreur lors de la soumission, Impossible de soumettre le devis . . . Veuillez vérifier le client car le client sur la DIT est différent de celui du devis ";
            $this->historiqueOperation->sendNotificationSoumission($message, $numDevis, 'dit_index');
        } elseif ($blockages['conditionDitIpsDiffDitSqlServ']) {
            $message = "Erreur lors de la soumission, Impossible de soumettre le devis . . . le numero DIT dans IPS ne correspond pas à la DIT";
            $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
        } elseif ($blockages['conditionServDebiteurvide']) {
            $message = "Erreur lors de la soumission, Impossible de soumettre le devis . . . le service débiteur n'est pas vide";
            $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
        } elseif ($blockages['conditionStatutDit']) {
            $message = "Erreur lors de la soumission, Impossible de soumettre le devis  . . . le statut de la DIT différent de AFFECTER SECTION";
            $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
        } elseif ($blockages['conditionStatutDevis']) {
            $message = "Erreur lors de la soumission, Impossible de soumettre le devis  . . . un devis est déjà en cours de validation";
            $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
        } elseif ($blockages['conditionNomCommence']) {
            $message = "Erreur lors de la soumission, Impossible de soumettre le devis  . . . Le fichier soumis a été renommé ou ne correspond pas à un devis";
            $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
        } 
        else {
            return true;
        }
    } 

    /**
     * Methode qui récupère les données du devis dans la base de donnée informix
     *
     * @param string $numDevis
     * @return array
     */
    public function InformationDevisInformix(string $numDevis)
    {
        $devisSoumisAValidationInformix = $this->ditDevisSoumisAValidationModel->recupDevisSoumisValidation($numDevis);
        if (empty($devisSoumisAValidationInformix)) {
            $message = "Erreur lors de la soumission, Impossible de soumettre le devis . . . l'information de la devis n'est pas recupérer";
            $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
        } else {
            return $devisSoumisAValidationInformix;
        }
    }

    // /**
    //  * Methode qui récupère les données du devis dans la base de donnée informix pour seulement devis forfait
    //  *
    //  * @param string $numDevis
    //  * @return array
    //  */
    // public function InformationDevisInformixForfait(string $numDevis)
    // {
    //     $devisSoumisAValidationInformixForfait = $this->ditDevisSoumisAValidationModel->recupDevisSoumisValidationForfait($numDevis);
    //     return empty($devisSoumisAValidationInformixForfait) ? [] : $devisSoumisAValidationInformixForfait;
    // }

    /**
     * Methode qui permet de savoir si la soumission
     * est une Devis vente ou forfait
     *
     * @param string $numDevis
     * @return boolean
     */
    public function estCeVenteOuForfait(string $numDevis): bool
    {
        $nbrItvTypeVte = $this->ditDevisSoumisAValidationModel->recupNbrItvTypeVte($numDevis);
        $nbrItvTypeCes = $this->ditDevisSoumisAValidationModel->recupNbrItvTypeCes($numDevis);

        if((int)$nbrItvTypeVte[0]['nb_vte'] > 0 && (int)$nbrItvTypeCes[0]['nb_ces'] > 0 ) {
            return false; //Devis forfait
        } else {
            return true; //Devis vente
        }
    }
    
    

    private function variationPrixRefPiece(string $numDevis): array
    {
        $infoPieceClients = $this->ditDevisSoumisAValidationModel->recupInfoPieceClient($numDevis);

        $infoPieces = array_map([$this->ditDevisSoumisAValidationModel, 'recupInfoPourChaquePiece'], $infoPieceClients);
        // $infoPieces = [];
        // foreach ($infoPieceClients as $value) {
        //     $infoPieces[] = $this->ditDevisSoumisAValidationModel->recupInfoPourChaquePiece($value);
        // }

        $infoPrix = [];
        if(!empty($infoPieces)){
            foreach ($infoPieces as $infoPiece) {
                if(!empty($infoPiece)) {
                    $infoPrix[] = [
                        'lineType' => isset($infoPiece[0]) ? ($infoPiece[0]['type_ligne'] ?? '-') : '-',
                        'cst' => isset($infoPiece[0]) ? ($infoPiece[0]['cst'] ?? '-') : '-',
                        'refPieces' => isset($infoPiece[0]) ? ($infoPiece[0]['refpiece'] ?? '-') : '-',
                        'pu1' => isset($infoPiece[0]) ? ($infoPiece[0]['prixvente'] ?? '-') : '-',
                        'datePu1' => isset($infoPiece[0]) ? ($infoPiece[0]['dateligne'] ?? '-') : '-',
                        'pu2' => isset($infoPiece[1]) ? ($infoPiece[1]['prixvente'] ?? '-') : '-',
                        'datePu2' => isset($infoPiece[1]) ? ($infoPiece[1]['dateligne'] ?? '-') : '-',
                        'pu3' => isset($infoPiece[2]) ? ($infoPiece[2]['prixvente'] ?? '-') : '-',
                        'datePu3' => isset($infoPiece[2]) ? ($infoPiece[2]['dateligne'] ?? '-') : '-',
                    ];
                }
            }
        } 

        return $infoPrix;
    }

    /**
     * Methode pour la création du pdf
     *
     * @param array $devisSoumisValidataion
     * @param GenererPdfDevisSoumisAValidation $generePdfDevis
     * @return void
     */
    private function creationPdf( array $devisSoumisValidataion, GenererPdfDevisSoumisAValidation $generePdfDevis)
    {   
        $numDevis = $devisSoumisValidataion[0]->getNumeroDevis();

        $devisSoumisAvant = $this->donnerDevisSoumisAvant($numDevis, $devisSoumisValidataion);

        $montantPdf = $this->montantPdfService->montantpdf($devisSoumisAvant);

        $quelqueaffichage = $this->quelqueAffichage($numDevis);

        $variationPrixRefPiece = $this->variationPrixRefPiece($numDevis);

        $mailUtilisateur = $this->nomUtilisateur(self::$em)['mailUtilisateur'];

        // dd($montantPdf, $quelqueaffichage);
        if($this->estCeVenteOuForfait($numDevis)) { // vente
            $generePdfDevis->GenererPdfDevisVente($devisSoumisValidataion[0], $montantPdf, $quelqueaffichage, $variationPrixRefPiece, $mailUtilisateur);
        } else { // sinom forfait
            $generePdfDevis->GenererPdfDevisForfait($devisSoumisValidataion[0], $montantPdf, $quelqueaffichage, $variationPrixRefPiece, $mailUtilisateur);
        }
    }

    private function donnerDevisSoumisAvant(string $numDevis, array $devisSoumisValidataion): array 
    {
        //dd($this->rectifierVenteAvantAvecForfait($numDevis), $this->rectifierVenteAvantMaxAvecForfait($numDevis));
        return [
            'devisSoumisAvantForfait' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantForfait($numDevis),
            'devisSoumisAvantMaxForfait' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantMaxForfait($numDevis),
            'devisSoumisAvantVte' => $this->rectifierVenteAvantAvecForfait($numDevis),
            'devisSoumisAvantMaxVte' => $this->rectifierVenteAvantMaxAvecForfait($numDevis),
            'devisSoumisAvantCes' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvant($numDevis, 'CES'),
            'devisSoumisAvantMaxCes' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantMax($numDevis, 'CES'),
            'vteData' => $this->filtreLesDonnerVte($devisSoumisValidataion),
            'cesData' => $this->filtreLesDonnerCes($devisSoumisValidataion),
        ];
    }

    private function rectifierVenteAvantAvecForfait(string $numDevis) {
        $devisSoumisAvantVtes = self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvant($numDevis, 'VTE');
    
        if (!empty($devisSoumisAvantVtes)) {
            foreach ($devisSoumisAvantVtes as $value) { // Utilisez $key pour garder une référence à l'index
                if ($value->getMontantForfait() !== null) {
                    $value->setMontantVente($value->getMontantITV() - $value->getMontantForfait());
                }
            }

            // foreach ($devisSoumisAvantVtes as $key => $value) { // Utilisez $key pour garder une référence à l'index
            //     if ($value->getMontantITV() === 0.00 && $value->getMontantForfait() !== null) {
            //         unset($devisSoumisAvantVtes[$key]); // Supprimez l'élément du tableau
            //     }
            // }

            // // Réindexer le tableau après suppression
            // $devisSoumisAvantVtes = array_values($devisSoumisAvantVtes);
        }
        return $devisSoumisAvantVtes;
    }
    

    private function rectifierVenteAvantMaxAvecForfait(string $numDevis) {
        $devisSoumisAvantMaxVtes = self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantMax($numDevis, 'VTE');

            // if($devisSoumisAvantMaxVtes !== null){
            //     foreach ($devisSoumisAvantMaxVtes as $value) {
            //         if($value->getMontantForfait() !== null) {
            //             $value->setMontantItv($value->getMontantITV() - $value->getMontantForfait());
            //         }
            //     }
            // }
        return $devisSoumisAvantMaxVtes;
    }

    /**
     * Methode pour filtrer les données de VTE (recupère seulement les donnée de vente)
     *
     * @param array $devisSoumisValidataion
     * @return array
     */
    private function filtreLesDonnerVte(array $devisSoumisValidataion): array
    {
        // Filtrer les éléments avec la nature d'opération égale à 'VTE'
        $resultatFiltre = array_filter($devisSoumisValidataion, function ($item) {
            return $item->getNatureOperation() === 'VTE';
        });

        // Vérifier si tous les montants des éléments filtrés sont à zéro
        $tousMontantsZero = array_reduce($resultatFiltre, function ($acc, $item) {
            $sommeMontants = 
                $item->getMontantPiece() + 
                $item->getMontantMo() + 
                $item->getMontantAchatLocaux() + 
                $item->getMontantFraisDivers() + 
                $item->getMontantLubrifiants()+
                $item->getMontantVente();

            return $acc && ($sommeMontants == 0);
        }, true);

        // Si tous les montants sont à zéro, retourner un tableau vide
        return $tousMontantsZero ? [] : $resultatFiltre;
    }


    /**
     * Methode pour filtrer les données de CES (recupère seulement les donnée de CES)
     *
     * @param array $devisSoumisValidataion
     * @return array
     */
    private function filtreLesDonnerCes(array $devisSoumisValidataion): array
    {
        return array_filter($devisSoumisValidataion, function ($item) {
            return $item->getNatureOperation() === 'CES';
        });
    }


    private function quelqueAffichage(string $numDevis): array
    {
        return [
            "numDevis" => $numDevis,
            "sortieMagasin" => $this->estCeSortieMagasin($numDevis),
            "achatLocaux" => $this->estCeAchatLocaux($numDevis)
        ];
    }

    private function estCeSortieMagasin(string $numDevis): string
    {
        $nbSotrieMagasin = $this->ditDevisSoumisAValidationModel->recupNbPieceMagasin($numDevis);
        if (!empty($nbSotrieMagasin) && $nbSotrieMagasin[0]['nbr_sortie_magasin'] !== "0") {
            $sortieMagasin = 'OUI';
        } else {
            $sortieMagasin = 'NON';
        }

        return $sortieMagasin;
    }

    private function estCeAchatLocaux(string $numDevis): string
    {
        $nbAchatLocaux = $this->ditDevisSoumisAValidationModel->recupNbAchatLocaux($numDevis);
        if (!empty($nbAchatLocaux) && $nbAchatLocaux[0]['nbr_achat_locaux'] !== "0") {
            $achatLocaux = 'OUI';
        } else {
            $achatLocaux = 'NON';
        }

        return $achatLocaux;
    }

    private function nomUtilisateur($em): array 
    {
        $userId = $this->sessionService->get('user_id', []);
        $user = $em->getRepository(User::class)->find($userId);
        return [
            'nomUtilisateur' => $user->getNomUtilisateur(),
            'mailUtilisateur' => $user->getMail()
        ];
    }

    private function envoieDonnerDansBd(array $devisSoumisValidataion)
    {
        // Persist les entités liées
        if (count($devisSoumisValidataion) > 1) {
            foreach ($devisSoumisValidataion as $entity) {
                $entity->setStatut('Soumis à validation');
                self::$em->persist($entity); // Persister chaque entité individuellement
            }
        } elseif (count($devisSoumisValidataion) === 1) {
            self::$em->persist($devisSoumisValidataion[0]);
        }


        // Flushe toutes les entités et l'historique
        self::$em->flush();
    }



    /**
     * Methode qui permet de transformer les données de l'informix en entité 
     *
     * @param array $devisSoumisAValidationInformix
     * @param integer|null $numeroVersionMax
     * @param string $numDevis
     * @param string $numDit
     * @param boolean $estCeVenteOuForfait
     * @return array tableau d'objet devisSoumisAValidation
     */
    private function devisSoumisValidataion(array $devisSoumisAValidationInformix, ?int $numeroVersionMax, string $numDevis, string $numDit, bool $estCeVenteOuForfait): array
    {
        $devisSoumisValidataion = []; // Tableau pour stocker les objets
        $infoDit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        if($estCeVenteOuForfait) {
            $venteOuForfait = 'DEVIS VENTE';
        } else {
            $venteOuForfait = 'DEVIS FORFAIT';
        }

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
                ->setNatureOperation($devisSoumis['nature_operation'])
                ->setMontantForfait($devisSoumis['montant_forfait'])
                ->setNomClient($infoDit->getNomClient())
                ->setNumeroClient($infoDit->getNumeroClient())
                ->setObjetDit($infoDit->getObjetDemande())
                ->setDevisVenteOuForfait($venteOuForfait)
                ->setDevise($devisSoumis['devise'])
            ;

            $devisSoumisValidataion[] = $ditInsertionDevis; // Ajouter l'objet dans le tableau
        }

        return $devisSoumisValidataion;
    }


    // private function devisSoumisValidataionForfait($devisSoumisAValidationInformixForfait, $numeroVersionMax, $numDevis, $numDit): array
    // {
    //     $devisSoumisValidataionForfait = []; // Tableau pour stocker les objets
    //     $infoDit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);

    //     foreach ($devisSoumisAValidationInformixForfait as $devisSoumis) {
    //         // Instancier une nouvelle entité pour chaque entrée du tableau
    //         $ditInsertionDevis = new DitDevisSoumisAValidation();

    //         $ditInsertionDevis
    //             ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
    //             ->setDateHeureSoumission(new \DateTime())
    //             ->setNumeroDevis($numDevis)
    //             ->setNumeroDit($numDit)
    //             ->setNumeroItv($devisSoumis['numero_itv'])
    //             ->setNombreLigneItv($devisSoumis['nombre_ligne'])
    //             ->setMontantItv($devisSoumis['montant_itv'])
    //             ->setMontantPiece($devisSoumis['montant_piece'])
    //             ->setMontantMo($devisSoumis['montant_mo'])
    //             ->setMontantAchatLocaux($devisSoumis['montant_achats_locaux'])
    //             ->setMontantFraisDivers($devisSoumis['montant_divers'])
    //             ->setMontantLubrifiants($devisSoumis['montant_lubrifiants'])
    //             ->setLibellelItv($devisSoumis['libelle_itv'])
    //             ->setStatut('Soumis à validation')
    //             ->setNatureOperation($devisSoumis['nature_operation'])
    //             ->setMontantForfait($devisSoumis['montant_forfait'])
    //             ->setNomClient($infoDit->getNomClient())
    //             ->setNumeroClient($infoDit->getNumeroClient())
    //             ->setObjetDit($infoDit->getObjetDemande())
    //         ;

    //         $devisSoumisValidataionForfait[] = $ditInsertionDevis; // Ajouter l'objet dans le tableau
    //     }

    //     return $devisSoumisValidataionForfait;
    // }

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
        // dump($numeroDevis);
        // dd(empty($numeroDevis));
        if (empty($numeroDevis)) {
            $message = "Echec , ce DIT n'a pas de numéro devis";
            $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_index');
        } else {
            return $numeroDevis[0]['numdevis'];
        }
    }

    private function initialistaion(DitDevisSoumisAValidation $ditDevisSoumisAValidation, string $numDit, string $numDevis)
    {
        return $ditDevisSoumisAValidation
            ->setNumeroDit($numDit)
            ->setNumeroDevis($numDevis)
            ->setDateHeureSoumission(new DateTime());
    }

    /**
     * Methode pour ajouter le numero devis dans la table demande_intervention
     *
     * @param string $numDit
     * @param string $numDevis
     * @return void
     */
    private function editDevisRattacherDit(string $numDit, string $numDevis)
    {
        $dit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $dit->setNumeroDevisRattache($numDevis);
        $dit->setStatutDevis('Soumis à validation');
        self::$em->flush();
    }

    /**
     * Methode pour la création et la fusion du fichier
     *
     * @param FormInterface $form
     * @param string $numDevis
     * @param string $numeroVersion
     * @return void
     */
    private function enregistrementEtFusionFichier(FormInterface $form, string $numDevis, string $numeroVersion)
    {
        $chemin = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/dev/';
        $fileUploader = new FileUploaderService($chemin);
        $options = [
            'prefix' => 'devis_ctrl',
            'prefixFichier' => 'devis',
            'numeroDoc' => $numDevis,
            'mergeFiles' => false,
            'numeroVersion' => $numeroVersion,
            'isIndex' => false
        ];
        $fileName = $fileUploader->chargerEtOuFusionneFichier($form, $options);

        $preparNom = [
            'prefix' => 'devis',
            'numeroDoc' => $numDevis,
            'numeroVersion' => $numeroVersion
        ];
        $nomFichierUploder = GenererNonFichierService::genererNonFichier( $preparNom);

        return [
            'fileName' => $fileName,
            'nomFichierUploder' => $nomFichierUploder
        ];
    }
}
