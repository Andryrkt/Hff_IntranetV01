<?php

namespace App\Controller\dit\Ors;

ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');

use App\Service\FusionPdf;
use App\Model\dit\DitModel;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Entity\admin\StatutDemande;
use App\Controller\Traits\da\DaTrait;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use Symfony\Component\Form\FormInterface;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaAfficherRepository;
use App\Service\fichier\UploderFileService;
use App\Service\fichier\TraitementDeFichier;
use App\Form\dit\DitOrsSoumisAValidationType;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Model\dit\DitOrSoumisAValidationModel;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use App\Service\dit\ors\OrGeneratorNameService;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Controller\Traits\dit\DitOrSoumisAValidationTrait;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\genererPdf\dit\ors\GenererPdfOrSoumisAValidation;
use App\Service\historiqueOperation\HistoriqueOperationORService;

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
        // $this->ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class);
        // $this->orRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        // $this->demandeApproLRepository = $this->getEntityManager()->getRepository(DemandeApproL::class);
        // $this->demandeApproLRRepository = $this->getEntityManager()->getRepository(DemandeApproLR::class);
        // $this->demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
        // $this->daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
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
        $demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
        /** @var DaAfficherRepository */
        $daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
        $numDas = $demandeApproRepository->getNumDa($numDit);
        if ($numDas) {
            foreach ($numDas as $numDa) {
                $statutDaAfficher = $daAfficherRepository->getLastStatutDaAfficher($numDa);

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

        $this->traitementFormulaire($form,  $request,  $numOr,   $numDit,  $ditInsertionOrSoumis, $daAfficherRepository);

        $this->logUserVisit('dit_insertion_or', [
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur

        $cdtArticleDa = $this->conditionBlocageArticleDa($numOr, $daAfficherRepository);
        return $this->render('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
            'cdtArticleDa' => $cdtArticleDa,
            'lierAUnDa' => $lierAUnDa,
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request, string $numOr, string  $numDit, DitOrsSoumisAValidation $ditInsertionOrSoumis, DaAfficherRepository $daAfficherRepository)
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
                    ->setNumeroDit($numDit);

                $orSoumisValidationModel = $this->ditModel->recupOrSoumisValidation($ditInsertionOrSoumis->getNumeroOR());

                $orSoumisValidataion = $this->orSoumisValidataion($orSoumisValidationModel, $numeroVersionMax, $ditInsertionOrSoumis, $numDit);

                /** Modification de la colonne statut_or dans la table demande_intervention */
                $this->modificationStatutOr($numDit);

                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                $this->envoieDonnerDansBd($orSoumisValidataion);

                /** CREATION , FUSION, ENVOIE DW du PDF */
                $this->traitementDeFichier($form, $ditInsertionOrSoumis, $orSoumisValidataion, $numOr);

                /** modifier la colonne numero_or dans la table demande_intervention */
                $this->modificationDuNumeroOrDansDit($numDit, $ditInsertionOrSoumis);

                /** modification da_valider */
                $this->modificationDaAfficher($numDit, $ditInsertionOrSoumis->getNumeroOR(), $daAfficherRepository);

                $this->historiqueOperation->sendNotificationSoumission('Le document de controle a été généré et soumis pour validation', $ditInsertionOrSoumis->getNumeroOR(), 'dit_index', true);
            } else {
                $message = "Echec lors de la soumission, . . .";
                $this->historiqueOperation->sendNotificationSoumission($message, $numOr, 'dit_index');
                exit;
            }
        }
    }

    private function preparationDesPiecesFaibleAchat(string $numOr): array
    {
        $infoOrs = $this->ditOrsoumisAValidationModel->getInformationOr($numOr);

        $infoPieceFaibleAchat = [];
        if (!empty($infoOrs)) {
            foreach ($infoOrs as $infoOr) {
                $afficher = $this->ditOrsoumisAValidationModel->getPieceFaibleActiviteAchat($infoOr['constructeur'], $infoOr['reference'], $numOr);

                if (isset($afficher[0]) && $afficher[0]['retour'] === 'a afficher') {

                    $infoPieceFaibleAchat[] = [
                        'numero_itv'        => $infoOr['numero_itv'],
                        'libelle_itv'       => $infoOr['libelle_itv'],
                        'constructeur'      => $infoOr['constructeur'],
                        'reference'         => $infoOr['reference'],
                        'designation'       => $infoOr['designation'],
                        'pmp'               => $afficher[0]['pmp'],
                        'date_derniere_cde' => $afficher[0]['date_derniere_cde'],
                    ];
                }
            }
        }
        return $infoPieceFaibleAchat;
    }

    private function traitementDeFichier(FormInterface $form, DitOrsSoumisAValidation $ditInsertionOrSoumis, $orSoumisValidataion, string $numOr): void
    {
        $suffix = $this->ditOrsoumisAValidationModel->constructeurPieceMagasin($numOr)[0]['retour'];

        /** 
         * 1. gestion des pieces jointes et generer le nom du fichier PDF
         * Enregistrement de fichier uploder
         * @var array $nomEtCheminFichiersEnregistrer 
         * @var array $nomFichierEnregistrer 
         * @var string $nomAvecCheminFichier
         * @var string $nomFichier
         */
        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $ditInsertionOrSoumis, $suffix);

        // 2. creation de la page de garde
        $genererPdfOrSoumisAValidation = new GenererPdfOrSoumisAValidation();
        $this->creationPdf($ditInsertionOrSoumis, $orSoumisValidataion, $suffix, $numOr, $nomAvecCheminFichier, $genererPdfOrSoumisAValidation);

        // 3. ajout du page de garde à la premier position
        $traitementDeFichier = new TraitementDeFichier();
        $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, 0);

        // 4. fusion du page de garde et des pieces jointes (conversion avant la fusion)
        $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
        $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);


        // 5. fusion de pdf Demande appro avec le pdf OR fusionner
        // $this->fusionPdfDaAvecORfusionner($numDit, $mainPdf, $daAfficherRepository);

        // 6.  envoyer le pdf fusionner dans DW
        $genererPdfOrSoumisAValidation->copyToDw($nomFichier, $ditInsertionOrSoumis->getNumeroDit());
    }

    private function enregistrementFichier(FormInterface $form, DitOrsSoumisAValidation $ditInsertionOrSoumis, string $suffix): array
    {

        $nameGenerator = new OrGeneratorNameService();
        $numDit = $ditInsertionOrSoumis->getNumeroDit();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/dit/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $path = $cheminBaseUpload . $numDit . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        /**
         * recupère les noms + chemins dans un tableau et les noms dans une autre
         * @var array $nomEtCheminFichiersEnregistrer
         * @var array $nomFichierEnregistrer
         */
        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer] = $uploader->getFichiers($form, [
            'repertoire' => $path,
            'generer_nom_callback' => function (
                UploadedFile $file,
                int $index
            ) use ($nameGenerator, $ditInsertionOrSoumis, $suffix) {
                return $nameGenerator->generateNameFile($file, $ditInsertionOrSoumis->getNumeroOR(), $ditInsertionOrSoumis->getNumeroVersion(), $suffix, $index);
            }
        ]);


        $nomFichier = $nameGenerator->generateNamePrincipal($ditInsertionOrSoumis->getNumeroOR(), $ditInsertionOrSoumis->getNumeroVersion(), $suffix);
        $nomAvecCheminFichier = $path . $nomFichier;

        return [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }

    private function conditionBlocageArticleDa(string $numOr, DaAfficherRepository $daAfficherRepository): bool
    {
        $listeArticlesSavLorString = $this->ditOrsoumisAValidationModel->getListeArticlesSavLorString($numOr);
        $nbrArticlesComparet = $this->ditOrsoumisAValidationModel->getNbrComparaisonArticleDaValiderEtSavLor($listeArticlesSavLorString, $numOr);
        $nombreArticleDansDaAfficheValider = $daAfficherRepository->getNbrDaAfficherValider($numOr);

        return $nbrArticlesComparet !== $nombreArticleDansDaAfficheValider && $nbrArticlesComparet > 0 && $nombreArticleDansDaAfficheValider > 0;
    }


    private function modificationDaAfficher(string $numDit, string $numOr, DaAfficherRepository $daAfficherRepository): void
    {
        $numeroVersionMax = $daAfficherRepository->getNumeroVersionMaxDit($numDit);
        $daAfficherValiders = $daAfficherRepository->findBy(['numeroVersion' => $numeroVersionMax, 'numeroDemandeDit' => $numDit, 'statutDal' => DemandeAppro::STATUT_VALIDE]);
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

    private function fusionPdfDaAvecORfusionner(string $numDit, string $mainPdf, DaAfficherRepository $daAfficherRepository): void
    {
        $numeroVersionMax = $daAfficherRepository->getNumeroVersionMaxDit($numDit);
        $daAfficherValiders = $daAfficherRepository->findBy(['numeroVersion' => $numeroVersionMax, 'numeroDemandeDit' => $numDit, 'statutDal' => DemandeAppro::STATUT_VALIDE]);
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
        $orRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $situationOrSoumis = $orRepository->getblocageStatut($numOr, $numDit);

        $countAgServDeb = $this->ditOrsoumisAValidationModel->countAgServDebit($numOr);

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
            $message = "Echec de la soumission de l'OR, la position de l'OR est parmis 'FC', 'FE', 'CP', 'ST'";
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

    private function creationPdf($ditInsertionOrSoumis, $orSoumisValidataion, string $suffix, string $numOr, string $nomAvecCheminFichier, GenererPdfOrSoumisAValidation $genererPdfOrSoumisAValidation)
    {
        $OrSoumisAvant = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvant($ditInsertionOrSoumis->getNumeroOR());
        // dump($OrSoumisAvant);
        $OrSoumisAvantMax = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvantMax($ditInsertionOrSoumis->getNumeroOR());
        // dump($OrSoumisAvantMax);
        $montantPdf = $this->montantpdf($orSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax);
        // dd($montantPdf);
        $quelqueaffichage = $this->quelqueAffichage($ditInsertionOrSoumis->getNumeroOR());

        // information sur les pièces à faible achat
        $pieceFaibleAchat = $this->preparationDesPiecesFaibleAchat($numOr);

        $genererPdfOrSoumisAValidation->GenererPdf($ditInsertionOrSoumis, $montantPdf, $quelqueaffichage, $this->nomUtilisateur()['mailUtilisateur'], $suffix, $pieceFaibleAchat, $nomAvecCheminFichier);
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
