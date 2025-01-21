<?php

namespace App\Controller\dit;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Form\FormInterface;
use App\Service\fichier\FileUploaderService;
use App\Entity\dit\DitDevisSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use App\Form\dit\DitDevisSoumisAValidationType;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dit\DitDevisSoumisAValidationModel;
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

            $numeroVersionMax = self::$em->getRepository(DitDevisSoumisAValidation::class)->findNumeroVersionMax($numDevis);
            $devisSoumisAValidationInformix = $this->ditDevisSoumisAValidationModel->recupDevisSoumisValidation($numDevis);
            if (empty($devisSoumisAValidationInformix)) {
                $message = "Erreur lors de la soumission, veuillez réessayer . . . l'information de la devis n'est pas recupérer";

                $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
            }

            $conditionDitIpsDiffDitSqlServ = $devisSoumisAValidationInformix[0]['numero_dit'] <> $numDit;

            $conditionServDebiteurvide = $devisSoumisAValidationInformix[0]['serv_debiteur'] <> '';

            if ($conditionDitIpsDiffDitSqlServ) {
                $message = "Erreur lors de la soumission, veuillez réessayer . . . le numero DIT dans IPS ne correspond pas à la DIT";
                $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
            } elseif ($conditionServDebiteurvide) {
                $message = "Erreur lors de la soumission, veuillez réessayer . . . le service débiteur n'est pas vide";
                $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index');
            } else {

                $devisSoumisValidataion = $this->devisSoumisValidataion($devisSoumisAValidationInformix, $numeroVersionMax, $numDevis, $numDit);

                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                $this->envoieDonnerDansBd($devisSoumisValidataion);

                /** CREATION , FUSION, ENVOIE DW du PDF */
                $this->creationPdf($devisSoumisValidataion, $this->ditDevisSoumisAValidationModel, $this->generePdfDevis);
                $fileName = $this->enregistrementEtFusionFichier($form, $numDevis, $devisSoumisValidataion[0]->getNumeroVersion());
                $this->generePdfDevis->copyToDWDevisSoumis($fileName); // copier le fichier dans docuware
                // $this->historique($fileName); //remplir la table historique

                $message = 'Le devis a été soumis avec succès';
                $this->historiqueOperation->sendNotificationCreation($message, $numDevis, 'dit_index', true);
            }
        }

        self::$twig->display('dit/DitDevisSoumisAValidation.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    private function creationPdf($devisSoumisValidataion, $ditDevisSoumisAValidationModel, GenererPdfDevisSoumisAValidation $generePdfDevis)
    {
        $numDevis = $devisSoumisValidataion[0]->getNumeroDevis();

        $OrSoumisAvant = self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvant($numDevis);
        // dump($OrSoumisAvant);
        $OrSoumisAvantMax = self::$em->getRepository(DitDevisSoumisAValidation::class)->findDevisSoumiAvantMax($numDevis);
        // dump($OrSoumisAvantMax);
        $montantPdf = $this->montantPdfService->montantpdf($devisSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax);
        // dd($montantPdf);
        $quelqueaffichage = $this->quelqueAffichage($ditDevisSoumisAValidationModel, $numDevis);

        $generePdfDevis->GenererPdfDevisForfait($devisSoumisValidataion[0], $montantPdf, $quelqueaffichage, $this->nomUtilisateur(self::$em)['mailUtilisateur']);
    }

    private function quelqueAffichage($ditDevisSoumisAValidationModel, $numDevis)
    {
        return [
            "numDevis" => $numDevis,
            "sortieMagasin" => $this->estCeSortieMagasin($ditDevisSoumisAValidationModel, $numDevis),
            "achatLocaux" => $this->estCeAchatLocaux($ditDevisSoumisAValidationModel, $numDevis)
        ];
    }

    private function estCeSortieMagasin($ditDevisSoumisAValidationModel, $numDevis): bool
    {
        $nbSotrieMagasin = $ditDevisSoumisAValidationModel->recupNbPieceMagasin($numDevis);
        if (!empty($nbSotrieMagasin) && $nbSotrieMagasin[0]['nbr_sortie_magasin'] !== "0") {
            $sortieMagasin = 'OUI';
        } else {
            $sortieMagasin = 'NON';
        }

        return $sortieMagasin;
    }

    private function estCeAchatLocaux($ditDevisSoumisAValidationModel, $numDevis): bool
    {
        $nbAchatLocaux = $ditDevisSoumisAValidationModel->recupNbAchatLocaux($numDevis);
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
        if (empty($numeroDevis)) {
            $message = "Echec , ce DIT n'a pas de numéro devis";
            $this->historiqueOperation->sendNotificationCreation($message, $numeroDevis, 'dit_index');
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
