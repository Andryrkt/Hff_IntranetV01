<?php

namespace App\Controller\dit;

ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');

use App\Controller\Controller;
use App\Entity\da\DemandeApproL;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\dit\DitOrsSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use App\Model\dit\DitOrSoumisAValidationModel;
use App\Repository\da\DemandeApproLRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Service\fichier\GenererNonFichierService;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Service\genererPdf\GenererPdfOrSoumisAValidation;
use App\Controller\Traits\dit\DitOrSoumisAValidationTrait;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationORService;

class DitOrsSoumisAValidationController extends Controller
{
    use FormatageTrait;
    use DitOrSoumisAValidationTrait;

    private MagasinListeOrLivrerModel $magasinListOrLivrerModel;
    private HistoriqueOperationService $historiqueOperation;
    private DitOrSoumisAValidationModel $ditOrsoumisAValidationModel;
    private GenererPdfOrSoumisAValidation $genererPdfDit;
    private DitRepository $ditRepository;
    private DitOrsSoumisAValidationRepository $orRepository;
    private DemandeApproLRepository $demandeApproLRepository;

    public function __construct()
    {
        parent::__construct();
        $this->magasinListOrLivrerModel = new MagasinListeOrLivrerModel();
        $this->historiqueOperation      = new HistoriqueOperationORService();
        $this->ditOrsoumisAValidationModel = new DitOrSoumisAValidationModel();
        $this->genererPdfDit = new GenererPdfOrSoumisAValidation();
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->orRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
    }

    /**
     * @Route("/soumission-or/{numDit}", name="dit_insertion_or")
     *
     * @return void
     */
    public function insertionOr(Request $request, $numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();


        $numOrBaseDonner = $this->ditOrsoumisAValidationModel->recupNumeroOr($numDit);

        if (empty($numOrBaseDonner)) {
            $message = "Le DIT n'a pas encore de numéro OR";

            $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
        }

        $numOr = $numOrBaseDonner[0]['numor'];

        $ditInsertionOrSoumis = new DitOrsSoumisAValidation();
        $ditInsertionOrSoumis
            ->setNumeroDit($numDit)
            ->setNumeroOR($numOr)
        ;

        $form = self::$validator->createBuilder(DitOrsSoumisAValidationType::class, $ditInsertionOrSoumis)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** DEBUT CONDITION DE BLOCAGE */
            $originalName = $form->get("pieceJoint01")->getData()->getClientOriginalName();
            $conditionBloquage = $this->conditionsDeBloquegeSoumissionOr($originalName, $numOr, $ditInsertionOrSoumis, $numDit);

            /** FIN CONDITION DE BLOCAGE */
            if ($this->bloquageOrSoumsi($conditionBloquage, $originalName, $ditInsertionOrSoumis)) {
                $numeroVersionMax = self::$em->getRepository(DitOrsSoumisAValidation::class)->findNumeroVersionMax($ditInsertionOrSoumis->getNumeroOR());

                $ditInsertionOrSoumis
                    ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                    ->setHeureSoumission($this->getTime())
                    ->setDateSoumission(new \DateTime($this->getDatesystem()))
                ;

                $orSoumisValidationModel = $this->ditModel->recupOrSoumisValidation($ditInsertionOrSoumis->getNumeroOR());
                //dump($orSoumisValidationModel);
                $orSoumisValidataion = $this->orSoumisValidataion($orSoumisValidationModel, $numeroVersionMax, $ditInsertionOrSoumis, $numDit);
                // dd($orSoumisValidataion);

                /** Modification de la colonne statut_or dans la table demande_intervention */
                $this->modificationStatutOr($numDit);

                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                $this->envoieDonnerDansBd($orSoumisValidataion);

                /** CREATION , FUSION, ENVOIE DW du PDF */
                $suffix = $this->ditOrsoumisAValidationModel->constructeurPieceMagasin($numOr)[0]['retour'];
                $this->creationPdf($ditInsertionOrSoumis, $orSoumisValidataion, $suffix);

                //envoie des pièce jointe dans une dossier et la fusionner
                $this->envoiePieceJoint($form, $ditInsertionOrSoumis, $this->fusionPdf, $suffix);
                $this->genererPdfDit->copyToDw($ditInsertionOrSoumis->getNumeroVersion(), $ditInsertionOrSoumis->getNumeroOR(), $suffix);

                /** modifier la colonne numero_or dans la table demande_intervention */
                $this->modificationDuNumeroOrDansDit($numDit, $ditInsertionOrSoumis);

                $this->historiqueOperation->sendNotificationSoumission('Le document de controle a été généré et soumis pour validation', $ditInsertionOrSoumis->getNumeroOR(), 'dit_index', true);
            } else {
                $message = "Echec lors de la soumission, . . .";
                $this->historiqueOperation->sendNotificationSoumission($message, $numOr, 'dit_index');
                exit;
            }
        }

        $this->logUserVisit('dit_insertion_or', [
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur

        self::$twig->display('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function conditionsDeBloquegeSoumissionOr(string $originalName, string $numOr, $ditInsertionOrSoumis, string $numDit): array
    {
        $numclient = $this->ditOrsoumisAValidationModel->getNumcli($numOr);
        $numcli = empty($numclient) ? '' : $numclient[0];
        $nbrNumcli = $this->ditOrsoumisAValidationModel->numcliExiste($numcli);

        $ditInsertionOrSoumis->setNumeroOR($numOr);

        $numOrNomFIchier = array_key_exists(1, explode('_', $originalName)) ? explode('_', $originalName)[1] : '';

        $demandeIntervention = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);

        $idMateriel = $this->ditOrsoumisAValidationModel->recupNumeroMatricule($numDit, $ditInsertionOrSoumis->getNumeroOR());

        $agServDebiteurBDSql = $demandeIntervention->getAgenceServiceDebiteur();
        $agServInformix = $this->ditModel->recupAgenceServiceDebiteur($ditInsertionOrSoumis->getNumeroOR());

        $datePlanning = $this->verificationDatePlanning($ditInsertionOrSoumis, $this->ditOrsoumisAValidationModel);

        $pos = $this->ditOrsoumisAValidationModel->recupPositonOr($ditInsertionOrSoumis->getNumeroOR());
        $invalidPositions = ['FC', 'FE', 'CP', 'ST'];

        $refClient = $this->ditOrsoumisAValidationModel->recupRefClient($ditInsertionOrSoumis->getNumeroOR());

        // $situationOrSoumis = $this->ditOrsoumisAValidationModel->recupBlockageStatut($numOr);
        $situationOrSoumis = $this->orRepository->getblocageStatut($numOr, $numDit);

        $countAgServDeb = $this->ditOrsoumisAValidationModel->countAgServDebit($numOr);

        $articleDas = $this->ditOrsoumisAValidationModel->validationArticleZstDa($numOr);
        $referenceDas = $this->demandeApproLRepository->getQteRefPu($numDit);
// dd($articleDas, $referenceDas, $this->compareTableaux($articleDas, $referenceDas), !$this->compareTableaux($articleDas, $referenceDas) && !empty($referenceDas) && !empty($articleDas));

        return [
            'nomFichier'            => strpos($originalName, 'Ordre de réparation') !== 0,
            'numeroOrDifferent'     => $numOr !== $ditInsertionOrSoumis->getNumeroOR(),
            'datePlanningExiste'    => $datePlanning,
            'agenceDebiteur'        => !in_array($agServDebiteurBDSql, $agServInformix),
            'invalidePosition'      => in_array($pos[0]['position'], $invalidPositions),
            'idMaterielDifferent'   => $demandeIntervention->getIdMateriel() !== (int)$idMateriel[0]['nummatricule'],
            'sansrefClient'         => empty($refClient),
            'situationOrSoumis'     => $situationOrSoumis === 'bloquer',
            'countAgServDeb'        => (int)$countAgServDeb > 1,
            'numOrFichier'          => $numOrNomFIchier <> $numOr,
            // 'datePlanningInferieureDateDuJour' => $this->datePlanningInferieurDateDuJour($numOr),
            'numcliExiste'          => $nbrNumcli[0] != 'existe_bdd',
            'articleDas'            => !$this->compareTableaux($articleDas, $referenceDas) && !empty($referenceDas) && !empty($articleDas),
        ];
    }

    private function bloquageOrSoumsi(array $conditionBloquage, string $originalName, $ditInsertionOrSoumis): bool
    {
        $okey = false;
        if ($conditionBloquage['nomFichier']) {
            $message = "Le fichier '{$originalName}' soumis a été renommé ou ne correspond pas à un OR";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
            exit;
        }
        if ($conditionBloquage['numeroOrDifferent']) {
            $message = "Echec lors de la soumission, le fichier soumis semble ne pas correspondre à la DIT";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
            exit;
        } elseif ($conditionBloquage['datePlanningExiste']) {
            $message = "Echec de la soumission car il existe une ou plusieurs interventions non planifiées dans l'OR";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
            exit;
        } elseif ($conditionBloquage['agenceDebiteur']) {
            $message = "Echec de la soumission car l'agence / service débiteur de l'OR ne correspond pas à l'agence / service de la DIT";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
            exit;
        } elseif ($conditionBloquage['invalidePosition']) {
            $message = "Echec de la soumission de l'OR";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
            exit;
        } elseif ($conditionBloquage['idMaterielDifferent']) {
            $message = "Echec de la soumission car le materiel de l'OR ne correspond pas au materiel de la DIT";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
            exit;
        } elseif ($conditionBloquage['sansrefClient']) {
            $message = "Echec de la soumission car la référence client est vide.";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
            exit;
        } elseif ($conditionBloquage['situationOrSoumis']) {
            $message = "Echec de la soumission de l'OR . . . un OR est déjà en cours de validation ";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
        } elseif ($conditionBloquage['countAgServDeb']) {
            $message = "Echec de la soumission de l'OR . . . un OR a plusieurs service débiteur ";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
        } elseif ($conditionBloquage['numOrFichier']) {
            $message = "Echec de la soumission de l'OR . . . le numéro OR ne correspond pas ";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
        }
        // elseif ($conditionBloquage['datePlanningInferieureDateDuJour']) {
        //     $message = "Echec de la soumission de l'OR . . . la date de planning est inférieure à la date du jour";
        //     $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
        // } 
        elseif ($conditionBloquage['articleDas']) {
            $message = "Echec de la soumission de l'OR . . . incohérence entre le bon d’achat validé et celui saisi dans l’OR";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
        } elseif ($conditionBloquage['numcliExiste']) {
            $message = "La soumission n'a pas pu être effectuée car le client rattaché à l'OR est introuvable";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
        } else {
            $okey = true;
        }
        return $okey;
    }

    private function creationPdf($ditInsertionOrSoumis, $orSoumisValidataion, string $suffix)
    {
        $OrSoumisAvant = self::$em->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvant($ditInsertionOrSoumis->getNumeroOR());
        // dump($OrSoumisAvant);
        $OrSoumisAvantMax = self::$em->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvantMax($ditInsertionOrSoumis->getNumeroOR());
        // dump($OrSoumisAvantMax);
        $montantPdf = $this->montantpdf($orSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax);
        // dd($montantPdf);
        $quelqueaffichage = $this->quelqueAffichage($ditInsertionOrSoumis->getNumeroOR());

        $this->genererPdfDit->GenererPdfOrSoumisAValidation($ditInsertionOrSoumis, $montantPdf, $quelqueaffichage, $this->nomUtilisateur(self::$em)['mailUtilisateur'], $suffix);
    }

    private function modificationStatutOr($numDit)
    {
        $demandeIntervention = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $demandeIntervention->setStatutOr('Soumis à validation');
        self::$em->persist($demandeIntervention);
        self::$em->flush();
    }

    private function envoieDonnerDansBd($orSoumisValidataion)
    {
        // Persist les entités liées
        if (count($orSoumisValidataion) > 1) {
            foreach ($orSoumisValidataion as $entity) {
                // Persist l'entité et l'historique
                self::$em->persist($entity); // Persister chaque entité individuellement
            }
        } elseif (count($orSoumisValidataion) === 1) {
            self::$em->persist($orSoumisValidataion[0]);
        }


        // Flushe toutes les entités et l'historique
        self::$em->flush();
    }

    private function modificationDuNumeroOrDansDit($numDit, $ditInsertionOrSoumis)
    {
        $dit = self::$em->getrepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $dit->setNumeroOR($ditInsertionOrSoumis->getNumeroOR());
        // $dit->setStatutOr('Soumis à validation');
        self::$em->flush();
    }

    private function quelqueAffichage($numOr)
    {
        $numDevis = $this->ditModel->recupererNumdevis($numOr);
        $nbSotrieMagasin = $this->ditOrsoumisAValidationModel->recupNbPieceMagasin($numOr);

        $nbAchatLocaux = $this->ditOrsoumisAValidationModel->recupNbAchatLocaux($numOr);
        if (!empty($nbSotrieMagasin) && $nbSotrieMagasin[0]['nbr_sortie_magasin'] !== "0") {
            $sortieMagasin = 'OUI';
        } else {
            $sortieMagasin = 'NON';
        }
        if (!empty($nbAchatLocaux) && $nbAchatLocaux[0]['nbr_achat_locaux'] !== "0") {
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

    function compareTableaux($a, $b)
    {
        if (count($a) != count($b)) {
            return false;
        }

        foreach ($a as $item) {
            $found = false;
            foreach ($b as $key => $value) {
                if ($item == $value) {
                    $found = true;
                    unset($b[$key]);
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }

        return true;
    }
}
