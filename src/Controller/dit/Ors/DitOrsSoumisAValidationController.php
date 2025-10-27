<?php

namespace App\Controller\dit\Ors;

ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');

use App\Entity\da\DaValider;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Controller\Traits\da\DaTrait;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Repository\da\DaValiderRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaAfficherRepository;
use App\Form\dit\DitOrsSoumisAValidationType;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Model\dit\DitOrSoumisAValidationModel;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Service\genererPdf\GenererPdfOrSoumisAValidation;
use App\Model\dit\DitModel;
use App\Controller\Traits\dit\DitOrSoumisAValidationTrait;
use App\Entity\admin\StatutDemande;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationORService;
use App\Service\FusionPdf;
use Symfony\Component\Form\FormInterface;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitOrsSoumisAValidationController extends Controller
{
    use FormatageTrait;
    use DitOrSoumisAValidationTrait;
    use DaTrait;

    private MagasinListeOrLivrerModel $magasinListOrLivrerModel;
    private HistoriqueOperationService $historiqueOperation;
    private DitOrSoumisAValidationModel $ditOrsoumisAValidationModel;
    private GenererPdfOrSoumisAValidation $genererPdfDit;
    private DitRepository $ditRepository;
    private DitOrsSoumisAValidationRepository $orRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DaAfficherRepository $daAfficherRepository;
    private $ditModel;
    private $fusionPdf;


    public function __construct()
    {
        parent::__construct();
        $this->magasinListOrLivrerModel = new MagasinListeOrLivrerModel();
        $this->historiqueOperation      = new HistoriqueOperationORService($this->getEntityManager());
        $this->ditOrsoumisAValidationModel = new DitOrSoumisAValidationModel();
        $this->genererPdfDit = new GenererPdfOrSoumisAValidation();
        $this->ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class);
        $this->orRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $this->demandeApproLRepository = $this->getEntityManager()->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $this->getEntityManager()->getRepository(DemandeApproLR::class);
        $this->demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
        $this->ditModel = new DitModel();
        $this->fusionPdf = new FusionPdf();
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

        // verification si l'OR est lié à un DA
        $lierAUnDa = false;
        $numDas = $this->demandeApproRepository->getNumDa($numDit);
        if ($numDas) {
            foreach ($numDas as $numDa) {
                $statutDaAfficher = $this->daAfficherRepository->getLastStatutDaAfficher($numDa);

                if (
                    !empty($statutDaAfficher) &&
                    !in_array(
                        $statutDaAfficher[0],
                        [
                            DemandeAppro::STATUT_VALIDE,
                            DemandeAppro::STATUT_TERMINER,
                            DemandeAppro::STATUT_EN_COURS_CREATION
                        ]
                    )
                ) {
                    $lierAUnDa = true;
                    break; // on arrête si on en trouve un qui correspond
                }
            }
        }

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

        $form = $this->getFormFactory()->createBuilder(DitOrsSoumisAValidationType::class, $ditInsertionOrSoumis)->getForm();

        $this->traitementFormulaire($form,  $request,  $numOr,   $numDit,  $ditInsertionOrSoumis);

        $this->logUserVisit('dit_insertion_or', [
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur

        $cdtArticleDa = $this->conditionBlocageArticleDa($numOr);
        return $this->render('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
            'cdtArticleDa' => $cdtArticleDa,
            'lierAUnDa' => $lierAUnDa,
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request, string $numOr, string  $numDit, DitOrsSoumisAValidation $ditInsertionOrSoumis)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** DEBUT CONDITION DE BLOCAGE */
            $originalName = $form->get("pieceJoint01")->getData()->getClientOriginalName();
            $observation = $form->get("observation")->getData();
            $conditionBloquage = $this->conditionsDeBloquegeSoumissionOr($originalName, $numOr, $ditInsertionOrSoumis, $numDit);

            /** FIN CONDITION DE BLOCAGE */
            if ($this->bloquageOrSoumsi($conditionBloquage, $originalName, $ditInsertionOrSoumis)) {

                $numeroVersionMax = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class)->findNumeroVersionMax($ditInsertionOrSoumis->getNumeroOR());

                $ditInsertionOrSoumis
                    ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                    ->setHeureSoumission($this->getTime())
                    ->setDateSoumission(new \DateTime($this->getDatesystem()))
                    ->setObservation($observation)
                ;

                $orSoumisValidationModel = $this->ditModel->recupOrSoumisValidation($ditInsertionOrSoumis->getNumeroOR());

                $orSoumisValidataion = $this->orSoumisValidataion($orSoumisValidationModel, $numeroVersionMax, $ditInsertionOrSoumis, $numDit);


                /** Modification de la colonne statut_or dans la table demande_intervention */
                $this->modificationStatutOr($numDit);

                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                $this->envoieDonnerDansBd($orSoumisValidataion);

                /** CREATION , FUSION, ENVOIE DW du PDF */
                $suffix = $this->ditOrsoumisAValidationModel->constructeurPieceMagasin($numOr)[0]['retour'];
                $this->creationPdf($ditInsertionOrSoumis, $orSoumisValidataion, $suffix);

                // creation du nom du pdf pricipal avec chemin
                $mainPdf = sprintf(
                    '%s/vor/oRValidation_%s-%s#%s.pdf',
                    $_ENV['BASE_PATH_FICHIER'],
                    $ditInsertionOrSoumis->getNumeroOR(),
                    $ditInsertionOrSoumis->getNumeroVersion(),
                    $suffix
                );

                //envoie des pièce jointe dans une dossier et la fusionner
                $this->envoiePieceJoint($form, $ditInsertionOrSoumis, $this->fusionPdf, $suffix, $mainPdf);

                //fusion de pdf Demande appro avec le pdf OR fusionner
                // $this->fusionPdfDaAvecORfusionner($numDit, $mainPdf);

                // envoyer le pdf fusionner dans DW
                $this->genererPdfDit->copyToDw($ditInsertionOrSoumis->getNumeroVersion(), $ditInsertionOrSoumis->getNumeroOR(), $suffix);

                /** modifier la colonne numero_or dans la table demande_intervention */
                $this->modificationDuNumeroOrDansDit($numDit, $ditInsertionOrSoumis);

                /** modification da_valider */
                $this->modificationDaAfficher($numDit, $ditInsertionOrSoumis->getNumeroOR());

                $this->historiqueOperation->sendNotificationSoumission('Le document de controle a été généré et soumis pour validation', $ditInsertionOrSoumis->getNumeroOR(), 'dit_index', true);
            } else {
                $message = "Echec lors de la soumission, . . .";
                $this->historiqueOperation->sendNotificationSoumission($message, $numOr, 'dit_index');
                exit;
            }
        }
    }

    private function conditionBlocageArticleDa(string $numOr): bool
    {
        $listeArticlesSavLorString = $this->ditOrsoumisAValidationModel->getListeArticlesSavLorString($numOr);
        $nbrArticlesComparet = $this->ditOrsoumisAValidationModel->getNbrComparaisonArticleDaValiderEtSavLor($listeArticlesSavLorString, $numOr);
        $nombreArticleDansDaAfficheValider = $this->daAfficherRepository->getNbrDaAfficherValider($numOr);

        return $nbrArticlesComparet !== $nombreArticleDansDaAfficheValider && $nbrArticlesComparet > 0 && $nombreArticleDansDaAfficheValider > 0;
    }


    private function modificationDaAfficher(string $numDit, string $numOr): void
    {
        $numeroVersionMax = $this->daAfficherRepository->getNumeroVersionMaxDit($numDit);
        $daAfficherValiders = $this->daAfficherRepository->findBy(['numeroVersion' => $numeroVersionMax, 'numeroDemandeDit' => $numDit, 'statutDal' => DemandeAppro::STATUT_VALIDE]);
        if (!empty($daAfficherValiders)) {

            /** @var DaAfficher $daValider */
            foreach ($daAfficherValiders as $daValider) {
                // recuperation du numéro de ligne
                $numeroLigne = $this->ditOrsoumisAValidationModel->getNumeroLigne($daValider->getArtRefp(), $daValider->getArtDesi(), $numOr);
                //modification des informations necessaire
                $daValider
                    ->setNumeroOr($numOr)
                    ->setOrResoumettre(false)
                    ->setNumeroLigneIps($numeroLigne[0]['numero_ligne'])
                ;
                $this->getEntityManager()->persist($daValider);
            }
            $this->getEntityManager()->flush();
        }
    }

    private function fusionPdfDaAvecORfusionner(string $numDit, string $mainPdf): void
    {
        $numeroVersionMax = $this->daAfficherRepository->getNumeroVersionMaxDit($numDit);
        $daAfficherValiders = $this->daAfficherRepository->findBy(['numeroVersion' => $numeroVersionMax, 'numeroDemandeDit' => $numDit, 'statutDal' => DemandeAppro::STATUT_VALIDE]);
        if (!empty($daAfficherValiders)) {
            //recupération du nom et chemin du PDF DA
            $cheminNomFichierDa = sprintf(
                '%s/da/%s/%s.pdf',
                $_ENV['BASE_PATH_FICHIER'],
                $daAfficherValiders[0]->getNumeroDemandeAppro(),
                $daAfficherValiders[0]->getNumeroDemandeAppro()
            );
            //ajout des chemin et nom de fichier à fusionnner dans un tableau 
            $pdfFiles = [$mainPdf, $cheminNomFichierDa];
            //conversion des fichiers
            $this->ConvertirLesPdf($pdfFiles);
            //fusion des fichiers
            $this->fusionPdf->mergePdfs($pdfFiles, $mainPdf);
        }
    }

    private function conditionsDeBloquegeSoumissionOr(string $originalName, string $numOr, $ditInsertionOrSoumis, string $numDit): array
    {
        $numclient = $this->ditOrsoumisAValidationModel->getNumcli($numOr);
        $numcli = empty($numclient) ? '' : $numclient[0];
        $nbrNumcli = $this->ditOrsoumisAValidationModel->numcliExiste($numcli);

        $ditInsertionOrSoumis->setNumeroOR($numOr);

        $numOrNomFIchier = array_key_exists(1, explode('_', $originalName)) ? explode('_', $originalName)[1] : '';

        $demandeIntervention = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);

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

        // $numDa = $this->demandeApproRepository->getNumDa($numDit);
        // if ($numDa) {
        //     $articleDas = $this->ditOrsoumisAValidationModel->validationArticleZstDa($numOr);
        //     $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
        //     $daValiders = $this->getLignesRectifieesDA($numDa, $numeroVersionMax);
        //     $referenceDas = array_map(function ($item) {
        //         return [
        //             "quantite" => $item->getQteDem(),
        //             "reference" => $item->getArtRefp(),
        //             "montant" => $item->getPrixUnitaire(),
        //             "designation" => $item->getArtDesi()
        //         ];
        //     }, $daValiders);
        //     // dd($articleDas, $referenceDas, $this->compareTableaux($articleDas, $referenceDas), !$this->compareTableaux($articleDas, $referenceDas) && !empty($referenceDas) && !empty($articleDas));
        // }

        // dd($nbrArticlesComparet, $nombreArticleDansDaValider);

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
            // 'articleDas'            => !$this->compareTableaux($articleDas, $referenceDas) && !empty($referenceDas) && !empty($articleDas),
            'premierSoumissionDatePlanningInferieurDateDuJour' => $this->premierSoumissionDatePlanningInferieurDateDuJour($numOr),
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
        // elseif ($conditionBloquage['articleDas']) {
        //     $message = "Echec de la soumission de l'OR . . . incohérence entre le bon d’achat validé et celui saisi dans l’OR";
        //     $okey = false;
        //     $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
        // } 
        elseif ($conditionBloquage['numcliExiste']) {
            $message = "La soumission n'a pas pu être effectuée car le client rattaché à l'OR est introuvable";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
        } elseif ($conditionBloquage['premierSoumissionDatePlanningInferieurDateDuJour']) {
            $message = " Impossible de soumettre l’OR, la date de planning est déjà dépassée";
            $okey = false;
            $this->historiqueOperation->sendNotificationSoumission($message, $ditInsertionOrSoumis->getNumeroOR(), 'dit_index');
        } else {
            $okey = true;
        }
        return $okey;
    }

    private function creationPdf($ditInsertionOrSoumis, $orSoumisValidataion, string $suffix)
    {
        $OrSoumisAvant = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvant($ditInsertionOrSoumis->getNumeroOR());
        // dump($OrSoumisAvant);
        $OrSoumisAvantMax = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvantMax($ditInsertionOrSoumis->getNumeroOR());
        // dump($OrSoumisAvantMax);
        $montantPdf = $this->montantpdf($orSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax);
        // dd($montantPdf);
        $quelqueaffichage = $this->quelqueAffichage($ditInsertionOrSoumis->getNumeroOR());

        $this->genererPdfDit->GenererPdfOrSoumisAValidation($ditInsertionOrSoumis, $montantPdf, $quelqueaffichage, $this->nomUtilisateur($this->getEntityManager())['mailUtilisateur'], $suffix);
    }

    private function modificationStatutOr($numDit)
    {
        $demandeIntervention = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $demandeIntervention->setStatutOr('Soumis à validation');
        $this->getEntityManager()->persist($demandeIntervention);
        $this->getEntityManager()->flush();
    }

    private function envoieDonnerDansBd($orSoumisValidataion)
    {
        // Persist les entités liées
        if (count($orSoumisValidataion) > 1) {
            foreach ($orSoumisValidataion as $entity) {
                // Persist l'entité et l'historique
                $this->getEntityManager()->persist($entity); // Persister chaque entité individuellement
            }
        } elseif (count($orSoumisValidataion) === 1) {
            $this->getEntityManager()->persist($orSoumisValidataion[0]);
        }


        // Flushe toutes les entités et l'historique
        $this->getEntityManager()->flush();
    }

    private function modificationDuNumeroOrDansDit($numDit, $ditInsertionOrSoumis)
    {
        $dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $dit->setNumeroOR($ditInsertionOrSoumis->getNumeroOR());
        // recuperation du statut DIT CLOTUREE VALIDER
        $statutCloturerValider = $this->getEntityManager()->getRepository(StatutDemande::class)->find(DemandeIntervention::STATUT_CLOTUREE_VALIDER);
        $dit->setIdStatutDemande($statutCloturerValider);

        $this->getEntityManager()->flush();
    }

    private function quelqueAffichage($numOr)
    {
        $numDevis = $this->ditModel->recupererNumdevis($numOr);
        $nbSotrieMagasin = $this->ditOrsoumisAValidationModel->recupNbPieceMagasin($numOr);
        $nbAchatLocaux = $this->ditOrsoumisAValidationModel->recupNbAchatLocaux($numOr);
        $nbPol = $this->ditOrsoumisAValidationModel->recupNbPol($numOr);


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

        if (!empty($nbPol) && $nbPol[0]['nbr_pol'] !== "0") {
            $pol = 'OUI';
        } else {
            $pol = 'NON';
        }

        return [
            "numDevis" => $numDevis,
            "sortieMagasin" => $sortieMagasin,
            "achatLocaux" => $achatLocaux,
            "pol" => $pol,
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
