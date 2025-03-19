<?php

namespace App\Controller\dit;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Service\autres\MontantPdfService;
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
use App\Traits\CalculeTrait;

class DitDevisSoumisAValidationController extends Controller
{
    use CalculeTrait;
    
    public const AFFECTER_SECTION = 51;

    private DitDevisSoumisAValidation $ditDevisSoumisAValidation;
    private DitDevisSoumisAValidationModel $ditDevisSoumisAValidationModel;
    private MontantPdfService $montantPdfService;
    private GenererPdfDevisSoumisAValidation $generePdfDevis;
    private HistoriqueOperationDEVService $historiqueOperation;
    private DitDevisSoumisAValidationRepository $devisRepository;
    private string $chemin;
    private FileUploaderService $fileUploader;

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
        $this->devisRepository = self::$em->getRepository(DitDevisSoumisAValidation::class);
        $this->chemin = $_ENV['BASE_PATH_FICHIER'].'/dit/dev/';
        $this->fileUploader = new FileUploaderService($this->chemin);
    }

    /**
     * @Route("/insertion-devis/{numDit}/{type}", name="dit_insertion_devis")
     *
     * @return void
     */
    public function insertionDevis(Request $request, $numDit, $type)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $numDevis = $this->numeroDevis($numDit);

        $devisSoumisAValidationInformix = $this->InformationDevisInformix($numDevis);

        $numeroVersionMax = $this->devisRepository->findNumeroVersionMax($numDevis); // recuperation du numero version max
        //ajout des informations vient dans informix dans l'entité devisSoumisAValidation
        $devisSoumisValidataion = $this->devisSoumisValidataion($devisSoumisAValidationInformix, $numeroVersionMax, $numDevis, $numDit, $this->estCeVente($numDevis), $type);

        // Vérification si une version du devis est déjà validée
        if($this->verificationTypeDevis($numDevis, $type, $devisSoumisValidataion)) {
            if ($request->query->get('continueDevis') == 1) {
                $this->sessionService->set('devis_version_valide', 'KO');
            }
        }

        //initialisation du formulaire
        $ditDevisSoumisAValidation = $this->initialistaion($this->ditDevisSoumisAValidation, $numDit, $numDevis);
        $form = self::$validator->createBuilder(DitDevisSoumisAValidationType::class, $ditDevisSoumisAValidation)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->traiterSoumissionDevis($form, $numDevis, $numDit, $type, $devisSoumisValidataion);
        }

        self::$twig->display('dit/DitDevisSoumisAValidation.html.twig', [
            'form' => $form->createView(),
            'numDevis' => $numDevis,
            'numDit' => $numDit,
            'type' => $type
        ]);
    }

    private function verificationTypeDevis(string $numDevis, string $type, array $devisSoumisValidataion)
    {
        $nbSotrieMagasin = $this->ditDevisSoumisAValidationModel->recupNbPieceMagasin($numDevis);
        $devisValide = $this->devisRepository->findDevisVpValide($numDevis);
        $devisStatut = $this->devisRepository->findStatut($numDevis);

        $devisSoumisAvant = $this->donnerDevisSoumisAvant($numDevis, $devisSoumisValidataion);
        $recapAvantApresVte = $this->montantPdfService->recuperationAvantApresVente($devisSoumisAvant['devisSoumisAvantMaxVte'], $devisSoumisAvant['devisSoumisAvantVte']);
        $totalAvAp = $this->montantPdfService->calculeSommeAvantApres($recapAvantApresVte);

        if($type === 'VP') {
            if ( $nbSotrieMagasin[0]['nbr_sortie_magasin'] !== "0" && $totalAvAp['nbLigAv'] === $totalAvAp['nbLigAp'] && $totalAvAp['nbLigAp'] !== 0) {// il n'y a pas de pièce magasin
                $message = " Pas de vérification à faire par le magasin ";
                $this->historiqueOperation->sendNotificationSoumission($message, $numDevis, 'dit_index');
            } else if ( $nbSotrieMagasin[0]['nbr_sortie_magasin'] === "0") {// il n'y a pas de pièce magasin
                $message = " Pas de vérification à faire par le magasin ";
                $this->historiqueOperation->sendNotificationSoumission($message, $numDevis, 'dit_index');
            } else if((int)$devisValide !== 0) {
                $message = " Une version de la devis est déjà validé ";
                $this->historiqueOperation->sendNotificationSoumissionSansRedirection($message, $numDevis, 'dit_index');
                $this->sessionService->set('devis_version_valide', 'OK');
                $this->sessionService->set('message', $message);
                return true;
            } else {
                return false;
            }
        } else {
            if((in_array("Prix à confirmer", $devisStatut) || in_array('Prix refusé magasin', $devisStatut)) && $totalAvAp['nbLigAv'] !== $totalAvAp['nbLigAp']) {
                $message = " Merci de repasser la soumission du devis au magasin pour vérification ";
                $this->historiqueOperation->sendNotificationSoumission($message, $numDevis, 'dit_index');
            }else {
                return false;
            }
        }
    }

      /** ✅ Traite la soumission du devis */
      private function traiterSoumissionDevis($form, string $numDevis, string $numDit, string $type, array $devisSoumisValidataion)
      {
        $originalName = $form->get("pieceJoint01")->getData()->getClientOriginalName();
        $numeroVersion = $devisSoumisValidataion[0]->getNumeroVersion();

        $blockages = $this->ConditionDeBlockage($numDevis, $numDit, $this->devisRepository, $originalName);
        // if ($this->blockageSoumission($blockages, $numDevis)) {
        if (true) {

            /** ENVOIE des DONNEE dans BASE DE DONNEE */
            $this->envoieDonnerDansBd($devisSoumisValidataion, $type);
            $this->editDevisRattacherDit($numDit, $numDevis, $type); //ajout du numero devis dans la table demande_intervention

            /** CREATION , FUSION, ENVOIE DW du PDF */
            $suffix = $this->ditDevisSoumisAValidationModel->constructeurPieceMagasin($numDevis)[0]['retour'];
            //recuperation du fichier ajouter par l'utilisateur
            $file =  $form->get('pieceJoint01')->getData();

            if ($type == 'VP') {
                //generer le nom du fichier
                $nomFichierGenerer = 'verificationprix_' .$numDevis.'-'.$numeroVersion.'#'.$suffix.'.pdf';

                // telecharger le fichier en copiant sur son repertoire
                $this->fileUploader->uploadFileSansName($file, $nomFichierGenerer);

                //envoye des fichier dans le DW
                if($this->estCeVente($numDevis)) { // si vrai c'est une vente
                    $this->generePdfDevis->copyToDWFichierDevisSoumis($nomFichierGenerer);// copier le fichier de devis dans docuware
                } else {
                    $this->generePdfDevis->copyToDWFichierDevisSoumis($nomFichierGenerer);// copier le fichier de devis dans docuware
                }
            } else {
                $nomFichierCtrl = 'devisctrl_' .$numDevis.'-'.$numeroVersion . '#'. $suffix.'.pdf';
                //generer le nom du fichier
                $nomFichierGenerer = 'devisatelier_' .$numDevis.'-'.$numeroVersion.'#'.$suffix.'.pdf';
                
                // telecharger le fichier en copiant sur son repertoire
                $this->fileUploader->uploadFileSansName($file, $nomFichierGenerer);

                //pour création du pdf
                $this->creationPdf($devisSoumisValidataion, $this->generePdfDevis, $nomFichierCtrl);
                
                // envoyer les fichiers dans DW
                if($this->estCeVente($numDevis)) { // si vrai c'est une vente
                    $this->generePdfDevis->copyToDWDevisSoumis($nomFichierCtrl);
                    $this->generePdfDevis->copyToDWFichierDevisSoumis($nomFichierGenerer);// copier le fichier de devis dans docuware
                } else {
                    /**envoie des fichiers dans docuware*/
                    $this->generePdfDevis->copyToDWDevisSoumis($nomFichierCtrl);// copier le fichier de controlle dans docuware
                    $this->generePdfDevis->copyToDWFichierDevisSoumis($nomFichierGenerer);// copier le fichier de devis dans docuware
                }
            }


            $message = 'Le devis a été soumis avec succès';
            $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index', true);
        }
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
            'conditionStatutDit' => $idStatutDit <> self::AFFECTER_SECTION, // le statut DIT est-il différent de AFFECTER SECTION
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
        } 
        // elseif ($blockages['conditionStatutDit']) {
        //     $message = "Erreur lors de la soumission, Impossible de soumettre le devis  . . . le statut de la DIT différent de AFFECTER SECTION";
        //     $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
        // }
         elseif ($blockages['conditionStatutDevis']) {
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

    

    /**
     * Methode qui permet de savoir si la soumission
     * est une Devis vente ou forfait
     *
     * @param string $numDevis
     * @return boolean
     */
    public function estCeVente(string $numDevis): bool
    {
        $recupConstRefPremDev = $this->ditDevisSoumisAValidationModel->recupConstRefPremDev($numDevis);
        $recupNbrItvDev = $this->ditDevisSoumisAValidationModel->recupNbrItvDev($numDevis);

        if($recupConstRefPremDev[0]['contructeur'] === 'ZDI-FORFAIT' && (int)$recupNbrItvDev[0]['itv'] > 0 ) {
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
    private function creationPdf( array $devisSoumisValidataion, GenererPdfDevisSoumisAValidation $generePdfDevis, string $nomFichierCtrl)
    {   
        $numDevis = $devisSoumisValidataion[0]->getNumeroDevis();

        $devisSoumisAvant = $this->donnerDevisSoumisAvant($numDevis);

        $montantPdf = $this->montantPdfService->montantpdf($devisSoumisAvant);

        $quelqueaffichage = $this->quelqueAffichage($numDevis);

        $variationPrixRefPiece = $this->variationPrixRefPiece($numDevis);

        $mailUtilisateur = $this->nomUtilisateur(self::$em)['mailUtilisateur'];

        // dd($montantPdf, $quelqueaffichage);
        if($this->estCeVente($numDevis)) { // vente
            $generePdfDevis->GenererPdfDevisVente($devisSoumisValidataion[0], $montantPdf, $quelqueaffichage, $variationPrixRefPiece, $mailUtilisateur, $nomFichierCtrl);
        } else { // sinom forfait
            $generePdfDevis->GenererPdfDevisForfait($devisSoumisValidataion[0], $montantPdf, $quelqueaffichage, $variationPrixRefPiece, $mailUtilisateur, $nomFichierCtrl);
        }
    }

    private function donnerDevisSoumisAvant(string $numDevis): array 
    {
        return [
            'devisSoumisAvantForfait' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantForfait($numDevis),
            'devisSoumisAvantMaxForfait' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantMaxForfait($numDevis),
            'devisSoumisAvantVte' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvant($numDevis),
            'devisSoumisAvantMaxVte' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantMax($numDevis),
        ];
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

    private function statutSelonType(string $type) {
        if($type == 'VP') {
            $statut = 'Prix à confirmer';
        } else {
            $statut = 'Soumis à validation';
        }
        return $statut;
    }

    private function envoieDonnerDansBd(array $devisSoumisValidataion, string $type)
    {
        $statut = $this->statutSelonType($type);

        // Persist les entités liées
        if (count($devisSoumisValidataion) > 1) {
            foreach ($devisSoumisValidataion as $entity) {
                $entity->setStatut($statut);
                self::$em->persist($entity); // Persister chaque entité individuellement
            }
        } elseif (count($devisSoumisValidataion) === 1) {
            $devisSoumisValidataion[0]->setStatut($statut);
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
    private function devisSoumisValidataion(array $devisSoumisAValidationInformix, ?int $numeroVersionMax, string $numDevis, string $numDit, bool $estCeVenteOuForfait, string $type): array
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
                ->settype($type)
                ->setMontantVente($devisSoumis['montant_vente'])
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
    private function editDevisRattacherDit(string $numDit, string $numDevis, string $type)
    {
        $statut = $this->statutSelonType($type);

        $dit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $dit->setNumeroDevisRattache($numDevis);
        $dit->setStatutDevis($statut);
        self::$em->flush();
    }

    private function nomFichierUploder( string $numDevis, string $numeroVersion, string $suffix)
    {
        //generer le nom de fichier uploder
        $preparNom = [
            'prefix' => 'devis',
            'numeroDoc' => $numDevis,
            'numeroVersion' => $numeroVersion,
            'suffixe' => $suffix
        ];
        $nomFichierGenerer = GenererNonFichierService::generationNomFichier( $preparNom);

        

        return  $nomFichierGenerer;
        
    }

    public function nomFichierCtrl(string $numDevis, string $numeroVersion, string $suffix)
    {
        //generer le nom de fichier generer
        $preparNomFichier = [
            'prefix' => 'devis_ctrl',
            'numeroDoc' => $numDevis,
            'numeroVersion' => $numeroVersion,
            'suffixe' => $suffix
        ];
        $fileName = GenererNonFichierService::generationNomFichier( $preparNomFichier);

        return  $fileName;
    }

    

    
}
