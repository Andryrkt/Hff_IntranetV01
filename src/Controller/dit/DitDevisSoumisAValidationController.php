<?php

namespace App\Controller\dit;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\FormInterface;
use App\Service\fichier\FileUploaderService;
use App\Entity\dit\DitDevisSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use App\Form\dit\DitDevisSoumisAValidationType;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dit\DitDevisSoumisAValidationModel;
use App\Repository\dit\DitDevisSoumisAValidationRepository;
use App\Service\autres\MontantPdfService;
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
            $devisRepository = self::$em->getRepository(DitDevisSoumisAValidation::class);
            $blockages = $this->ConditionDeBlockage($numDevis, $numDit, $devisRepository);
            if ($this->blockageSoumission($blockages, $numDevis)) {
                
                $devisSoumisAValidationInformix = $this->InformationDevisInformix($numDevis, $this->estCeVenteOuForfait($numDevis));
                
                $numeroVersionMax = $devisRepository->findNumeroVersionMax($numDevis); // recuperation du numero version max
                //ajout des informations vient dans informix dans l'entité devisSoumisAValidation
                $devisSoumisValidataion = $this->devisSoumisValidataion($devisSoumisAValidationInformix, $numeroVersionMax, $numDevis, $numDit, $this->estCeVenteOuForfait($numDevis));

                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                $this->envoieDonnerDansBd($devisSoumisValidataion);

                /** CREATION , FUSION, ENVOIE DW du PDF */
                $this->creationPdf($devisSoumisValidataion, $this->generePdfDevis);
                $fileName= $this->enregistrementEtFusionFichier($form, $numDevis, $devisSoumisValidataion[0]->getNumeroVersion());
                $this->generePdfDevis->copyToDWDevisSoumis($fileName);// copier le fichier dans docuware
                // $this->historique($fileName); //remplir la table historique

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
    public function ConditionDeBlockage(string $numDevis, string $numDit, DitDevisSoumisAValidationRepository $devisRepository): array
    {   $TrouverDansDit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        if ($TrouverDansDit === null) {
            $message = "Erreur avant la soumission, Impossible de soumettre le devis . . . l'information de la statut du n° DIT $numDit n'est pas récupérer";
            $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
        } else {
            $idStatutDit = $TrouverDansDit->getIdStatutDemande()->getId();
            $statutDevis = $devisRepository->findDernierStatutDevis($numDevis);
            $numDitIps = $this->ditDevisSoumisAValidationModel->recupNumDitIps($numDevis)[0]['num_dit'];
            $servDebiteur = $this->ditDevisSoumisAValidationModel->recupServDebiteur($numDevis)[0]['serv_debiteur'];
        }

        
        return  [
            'conditionDitIpsDiffDitSqlServ' => $numDitIps <> $numDit, // n° dit ips <> n° dit intranet
            'conditionServDebiteurvide' => $servDebiteur <> '', // le service debiteur n'est pas vide
            'conditionStatutDit' => $idStatutDit <> 51, // le statut DIT est-il différent de AFFECTER SECTION
            'conditionStatutDevis' => $statutDevis === 'Soumis à validation' // le statut de la dernière version de devis est-il Soumis à validation 
        ];
    }

    /**
     * Methode qui récupère les données du devis dans la base de donnée informix
     *
     * @param string $numDevis
     * @return array
     */
    public function InformationDevisInformix(string $numDevis, bool $estCeForfaitVente)
    {
        $devisSoumisAValidationInformix = $this->ditDevisSoumisAValidationModel->recupDevisSoumisValidation($numDevis, $estCeForfaitVente);
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

    /**
     * METHODE pour les condition de blockage de soumision devis
     *
     * @param array $blockages
     * @param string $numDevis
     * @return boolean
     */
    public function blockageSoumission(array $blockages, string $numDevis): bool
    {
        if ($blockages['conditionDitIpsDiffDitSqlServ']) {
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
        } else {
            return true;
        }
    } 

    private function variationPrixRefPiece(string $numDevis): array
    {
        $infoPieceClients = $this->ditDevisSoumisAValidationModel->recupInfoPieceClient($numDevis);

        $infoPieces = array_map([$this->ditDevisSoumisAValidationModel, 'recupInfoPourChaquePiece'], $infoPieceClients);
        $infoPrix = [];
        if(!empty($infoPiece)){
            foreach ($infoPieces as $infoPiece) {
                $infoPrix[] = [
                    'lineType' => isset($infoPiece[0]) ? ($infoPiece[0]['type_ligne'] ?? '-') : '-',
                    'cst' => isset($infoPiece[0]) ? ($infoPiece[0]['cst'] ?? '-') : '-',
                    'refPiece' => isset($infoPiece[0]) ? ($infoPiece[0]['refpiece'] ?? '-') : '-',
                    'pu1' => isset($infoPiece[0]) ? ($infoPiece[0]['prixvente'] ?? '-') : '0.00',
                    'datePu1' => isset($infoPiece[0]) ? ($infoPiece[0]['dateligne'] ?? '-') : '-',
                    'pu2' => isset($infoPiece[1]) ? ($infoPiece[1]['prixvente'] ?? '-') : '0.00',
                    'datePu2' => isset($infoPiece[1]) ? ($infoPiece[1]['dateligne'] ?? '-') : '-',
                    'pu3' => isset($infoPiece[2]) ? ($infoPiece[2]['prixvente'] ?? '-') : '0.00',
                    'datePu3' => isset($infoPiece[2]) ? ($infoPiece[2]['dateligne'] ?? '-') : '-',
                ];
            }
        } else {
            $infoPrix[] = [
                'lineType' => '-',
                'cst' => '-',
                'refPiece' => '-',
                'pu1' => '0.00',
                'datePu1' => '-',
                'pu2' => '0.00',
                'datePu2' => '-',
                'pu3' => '0.00',
                'datePu3' => '-',
            ];
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

        $devisSoumisAvant = [
            'devisSoumisAvantVte' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvant($numDevis, 'VTE'),
            'devisSoumisAvantMaxVte' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantMax($numDevis, 'VTE'),
            'devisSoumisAvantCes' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvant($numDevis, 'CES'),
            'devisSoumisAvantMaxCes' => self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantMax($numDevis, 'CES'),
            'vteData' => $this->filtreLesDonnerVte($devisSoumisValidataion),
            'cesData' => $this->filtreLesDonnerCes($devisSoumisValidataion)
        ];

        // dump($OrSoumisAvantMax);
        $montantPdf = $this->montantPdfService->montantpdf($devisSoumisAvant);
        // dd($montantPdf);
        $quelqueaffichage = $this->quelqueAffichage($numDevis);

        $variationPrixRefPiece = $this->variationPrixRefPiece($numDevis);

        if($this->estCeVenteOuForfait($numDevis)) { // vente
            $generePdfDevis->GenererPdfDevisVente($devisSoumisValidataion[0], $montantPdf, $quelqueaffichage, $variationPrixRefPiece, $this->nomUtilisateur(self::$em)['mailUtilisateur']);
        } else { // sinom forfait
            $generePdfDevis->GenererPdfDevisForfait($devisSoumisValidataion[0], $montantPdf, $quelqueaffichage, $variationPrixRefPiece, $this->nomUtilisateur(self::$em)['mailUtilisateur']);
        }
    }
    
    private function filtreLesDonnerVte(array $devisSoumisValidataion): array
    {
        return array_filter($devisSoumisValidataion, function ($item) {
            return $item->getNatureOperation() === 'VTE';
        });
    }

    private function filtreLesDonnerCes(array $devisSoumisValidataion): array
    {
        return array_filter($devisSoumisValidataion, function ($item) {
            return $item->getNatureOperation() === 'CES';
        });
    }


    private function quelqueAffichage($numDevis)
    {
        return [
            "numDevis" => $numDevis,
            "sortieMagasin" => $this->estCeSortieMagasin($numDevis),
            "achatLocaux" => $this->estCeAchatLocaux($numDevis)
        ];
    }

    private function estCeSortieMagasin($numDevis): string
    {
        $nbSotrieMagasin = $this->ditDevisSoumisAValidationModel->recupNbPieceMagasin($numDevis);
        if (!empty($nbSotrieMagasin) && $nbSotrieMagasin[0]['nbr_sortie_magasin'] !== "0") {
            $sortieMagasin = 'OUI';
        } else {
            $sortieMagasin = 'NON';
        }

        return $sortieMagasin;
    }

    private function estCeAchatLocaux($numDevis): string
    {
        $nbAchatLocaux = $this->ditDevisSoumisAValidationModel->recupNbAchatLocaux($numDevis);
        if (!empty($nbAchatLocaux) && $nbAchatLocaux[0]['nbr_achat_locaux'] !== "0") {
            $achatLocaux = 'OUI';
        } else {
            $achatLocaux = 'NON';
        }

        return $achatLocaux;
    }

    private function nomUtilisateur($em)
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
                self::$em->persist($entity); // Persister chaque entité individuellement
            }
        } elseif (count($devisSoumisValidataion) === 1) {
            self::$em->persist($devisSoumisValidataion[0]);
        }


        // Flushe toutes les entités et l'historique
        self::$em->flush();
    }



    private function devisSoumisValidataion($devisSoumisAValidationInformix, $numeroVersionMax, $numDevis, $numDit, $estCeVenteOuForfait): array
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
                ->setStatut('Soumis à validation')
                ->setNatureOperation($devisSoumis['nature_opreration'])
                ->setMontantForfait($devisSoumis['montant_forfait'])
                ->setNomClient($infoDit->getNomClient())
                ->setNumeroClient($infoDit->getNumeroClient())
                ->setObjetDit($infoDit->getObjetDemande())
                ->setDevisVenteOuForfait($venteOuForfait)
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

    private function enregistrementEtFusionFichier(FormInterface $form, string $numDevis, string $numeroVersion)
    {
        $chemin = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/dev/';
        $fileUploader = new FileUploaderService($chemin);
        $prefix = 'devis_ctrl';
        $fileName = $fileUploader->chargerEtOuFusionneFichier($form, $prefix, $numDevis, false, $numeroVersion);

        return $fileName;
    }
}
