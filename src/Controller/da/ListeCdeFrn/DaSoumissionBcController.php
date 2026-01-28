<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Constants\ddp\StatutConstants;
use Exception;
use App\Model\da\DaModel;
use App\Entity\da\DaValider;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\admin\Application;
use App\Entity\da\DaSoumissionBc;
use App\Entity\ddp\DemandePaiement;
use App\Entity\admin\ddp\TypeDemande;
use App\Model\da\DaSoumissionBcModel;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Model\ddp\DemandePaiementModel;
use App\Service\genererPdf\GeneratePdf;
use App\Service\autres\AutoIncDecService;
use App\Repository\da\DaAfficherRepository;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaSoumissionBcRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\da\soumissionBC\DaSoumissionBcType;
use App\Constants\ddp\TypeDemandePaiementConstants;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionBcController extends Controller
{
    use PdfConversionTrait;

    private  DaSoumissionBc $daSoumissionBc;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private HistoriqueOperationService $historiqueOperation;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    private GeneratePdf $generatePdf;
    private DemandeApproRepository $demandeApproRepository;
    private DaAfficherRepository $daAfficherRepository;
    private DaSoumissionBcModel $daSoumissionBcModel;

    public function __construct()
    {
        parent::__construct();

        $this->daSoumissionBc = new DaSoumissionBc();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->historiqueOperation      = new HistoriqueOperationDaBcService($this->getEntityManager());
        $this->daSoumissionBcRepository = $this->getEntityManager()->getRepository(DaSoumissionBc::class);
        $this->generatePdf = new GeneratePdf();
        $this->demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
        $this->daSoumissionBcModel = new DaSoumissionBcModel();
    }

    /**
     * @Route("/soumission-bc/{numCde}/{numDa}/{numOr}", name="da_soumission_bc", defaults={"numOr"=0})
     */
    public function index(string $numCde, string $numDa, string $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->daSoumissionBc->setNumeroCde($numCde);

        $form = $this->getFormFactory()->createBuilder(DaSoumissionBcType::class, $this->daSoumissionBc, [
            'method' => 'POST',
        ])->getForm();

        $this->traitementFormulaire($request, $numCde, $form, $numDa, $numOr);

        return $this->render('da/soumissionBc.html.twig', [
            'form' => $form->createView(),
            'numCde' => $numCde,
        ]);
    }

    /**
     * permet de faire le rtraitement du formulaire
     *
     * @param Request $request
     * @param string $numCde
     * @param [type] $form
     * @return void
     */
    private function traitementFormulaire(Request $request, string $numCde, $form, string $numDa, string $numOr): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $soumissionBc = $form->getData();
            if ($this->verifierConditionDeBlocage($soumissionBc, $numCde, $numDa)) {
                /** ENREGISTREMENT DE FICHIER */
                $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

                //numeroversion max
                $numeroVersionMax = $this->autoIncrement($this->daSoumissionBcRepository->getNumeroVersionMax($numCde));
                /** FUSION DES PDF */
                $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');
                $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);
                $nomPdfFusionner =  'BCAppro!' . $numCde . '#' . $numDa . '-' . $numOr . '_' . $numeroVersionMax . '.pdf';
                $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;
                $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

                /** AJOUT DES INFO NECESSAIRE */
                $soumissionBc = $this->ajoutInfoNecesaireSoumissionBc($numCde, $numDa, $soumissionBc, $nomPdfFusionner, $numeroVersionMax, $numOr);

                /** ENREGISTREMENT DANS LA BASE DE DONNEE */
                $this->getEntityManager()->persist($soumissionBc);
                $this->getEntityManager()->flush();

                /** COPIER DANS DW */
                $this->generatePdf->copyToDWBcDa($nomPdfFusionner, $numDa);

                /** modification du table da_afficher */
                $this->modificationDaAfficher($numDa, $numCde);

                /** ENREGISTREMENT DANS LA TABLE DEMANDE DE PAIEMENT */
                $this->EnregistrementDansLaTableDemandepaiement($numCde);

                /** HISTORISATION */
                $message = "Le document est soumis pour validation";
                $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
                $nomDeRoute = 'da_list_cde_frn'; // route de redirection après soumission
                $nomInputSearch = 'cde_frn_list'; // initialistion de nom de chaque champ ou input
                $this->historiqueOperation->sendNotificationSoumission($message, $numCde, $nomDeRoute, true, $criteria, $nomInputSearch);
            }
        }
    }

    private function getNumeroDdp(): string
    {
        //recupereation de l'application DDP pour generer le numero de ddp
        $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DDP']);
        if (!$application) {
            throw new \Exception("L'application 'DDP' n'a pas été trouvée dans la configuration.");
        }
        //generation du numero de ddp
        $numeroDdp = AutoIncDecService::autoGenerateNumero('DDP', $application->getDerniereId(), true);
        //mise a jour de la derniere id de l'application DDP
        AutoIncDecService::mettreAJourDerniereIdApplication($application, $this->getEntityManager(), $numeroDdp);

        return $numeroDdp;
    }

    private function getTypePayementAvantLivraison(): TypeDemande
    {
        // recupération du type de demande "DDP avant livraison"
        $ddpAvantLivraison = $this->getEntityManager()->getRepository(TypeDemande::class)->find(TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE);
        if (!$ddpAvantLivraison) {
            throw new \Exception("Le type de demande 'DDP avant livraison' (ID 1) n'a pas été trouvé.");
        }

        return $ddpAvantLivraison;
    }

    private function EnregistrementDansLaTableDemandepaiement(int $numCde)
    {
        $numFrn = $this->daAfficherRepository->getNumFrnDa($numCde)['numeroFournisseur'] ?? '';

        $ddpModel = new DemandePaiementModel();
        $infoCde = $ddpModel->recupInfoComamnde($numCde, $numFrn);

        if (!empty($infoCde)) {
            $demandePaiement = new DemandePaiement();

            $demandePaiement->setNumeroDdp($this->getNumeroDdp())
                ->setTypeDemandeId($this->getTypePayementAvantLivraison())
                ->setNumeroFournisseur($infoCde[0]['num_fournisseur'] ?? '')
                ->setRibFournisseur($infoCde[0]['rib'] ?? '')
                ->setBeneficiaire($infoCde[0]['nom_fournisseur'] ?? '')
                ->setMotif(null)
                ->setAgenceDebiter($infoCde[0]['code_agence'] ?? '')
                ->setServiceDebiter($infoCde[0]['code_service'] ?? '')
                ->setStatut(StatutConstants::STATUT_EN_ATTENTE_VALIDATION_BC)
                ->setAdresseMailDemandeur($this->getUserMail())
                ->setDemandeur($this->getUserName())
                ->setModePaiement($infoCde[0]['mode_paiement'] ?? '')
                ->setMontantAPayers($infoCde[0]['montant_total_cde'] ?? 0)
                ->setContact(Null)
                ->setNumeroCommande([$infoCde[0]['numero_cde']] ?? [])
                ->setNumeroFacture([])
                ->setStatutDossierRegul(Null)
                ->setNumeroVersion(1)
                ->setDevise($infoCde[0]['devise'] ?? '')
                ->setEstAutreDoc(false)
                ->setNomAutreDoc(Null)
                ->setEstCdeClientExterneDoc(false)
                ->setNomCdeClientExterneDoc(Null)
                ->setNumeroDossierDouane(Null)
                ->setAppro(true)
            ;

            $this->getEntityManager()->persist($demandePaiement);
            $this->getEntityManager()->flush();
        }
    }

    private function modificationDaAfficher(string $numDa, string $numCde): void
    {
        $numeroVersionMaxCde = $this->daAfficherRepository->getNumeroVersionMax($numDa);
        $daValiders = $this->daAfficherRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMaxCde, 'numeroCde' => $numCde]);
        if (!empty($daValiders)) {
            foreach ($daValiders as $key => $daValider) {
                $daValider
                    ->setStatutCde(DaSoumissionBc::STATUT_SOUMISSION);
                $this->getEntityManager()->persist($daValider);
            }

            $this->getEntityManager()->flush();
        }
    }

    private function ajoutInfoNecesaireSoumissionBc(string $numCde, string $numDa, DaSoumissionBc $soumissionBc, string $nomPdfFusionner, int $numeroVersionMax, string $numOr): DaSoumissionBc
    {
        $numDit = $this->demandeApproRepository->getNumDitDa($numDa);
        // $numOr = $this->ditRepository->getNumOr($numDit);

        $montantBc = $this->getMontantBc($numCde);

        $soumissionBc->setNumeroCde($numCde)
            ->setUtilisateur($this->getUserName())
            ->setPieceJoint1($nomPdfFusionner)
            ->setStatut(DaSoumissionBc::STATUT_SOUMISSION)
            ->setNumeroVersion($numeroVersionMax)
            ->setNumeroDemandeAppro($numDa)
            ->setNumeroDemandeDit($numDit)
            ->setNumeroOR($numOr)
            ->setMontantBc($montantBc)
        ;
        return $soumissionBc;
    }

    private function getMontantBc(string $numCde): float
    {
        $daModel = new DaModel();
        return $daModel->getMontantBcDaDirect($numCde);
    }

    private function conditionDeBlocage(DaSoumissionBc $soumissionBc, string $numCde, string $numDa): array
    {
        $nomdeFichier = $soumissionBc->getPieceJoint1()->getClientOriginalName();
        $nomdeFichier = str_replace('BON_DE_COMMANDE', 'BON DE COMMANDE', $nomdeFichier);
        $statut = $this->daSoumissionBcRepository->getStatut($numCde);
        $montantBc = $this->daSoumissionBcRepository->getMontantBc($numCde);

        //recuperation du numDa dans Informix
        $numDaInformix = $this->daSoumissionBcModel->getNumDa($numCde);

        return [
            'nomDeFichier' => explode('_', $nomdeFichier)[0] <> 'BON DE COMMANDE' || explode('_', $nomdeFichier)[1] <> $numCde,
            'statut' => $statut === DaSoumissionBc::STATUT_SOUMISSION || $statut === DaSoumissionBc::STATUT_A_VALIDER_DA,
            'numDaEgale' => $numDaInformix[0] !== $numDa,
            'montantBcEgale' => $montantBc == $this->getMontantBc($numCde)
        ];
    }

    private function verifierConditionDeBlocage(DaSoumissionBc $soumissionBc, string $numCde, string $numDa): bool
    {
        $conditions = $this->conditionDeBlocage($soumissionBc, $numCde, $numDa);
        $nomdeFichier = $soumissionBc->getPieceJoint1()->getClientOriginalName();
        $okey = false;

        if ($conditions['nomDeFichier']) {
            $message = "Le fichier '{$nomdeFichier}' soumis a été renommé ou ne correspond pas à un BC";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        } elseif ($conditions['statut']) {
            $message = "Echec lors de la soumission, un BC est déjà en cours de validation ";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        } elseif ($conditions['numDaEgale']) {
            $message = "Le numéro de DA '$numDa' ne correspond pas pour le BC '$numCde'";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        } elseif ($conditions['montantBcEgale']) {
            $message = "Soumission d'un même BC";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        } else {
            $okey = true; // Aucune condition de blocage n'est remplie
        }

        return $okey;
    }

    /**
     * Enregistrement des fichiers téléchagrer dans le dossier de destination
     *
     * @param [type] $form
     * @return array
     */
    private function enregistrementFichier($form, $numCde, $numDa): array
    {
        $fieldPattern = '/^pieceJoint(\d{1})$/';
        $nomDesFichiers = [];
        $compteur = 1; // Pour l’indexation automatique

        foreach ($form->all() as $fieldName => $field) {
            if (preg_match($fieldPattern, $fieldName, $matches)) {
                /** @var UploadedFile|UploadedFile[]|null $file */
                $file = $field->getData();

                if ($file !== null) {
                    $fichiers = is_array($file) ? $file : [$file];

                    foreach ($fichiers as $singleFile) {
                        if ($singleFile !== null) {
                            // Ensure $singleFile is an instance of Symfony's UploadedFile
                            if (!$singleFile instanceof UploadedFile) {
                                throw new \InvalidArgumentException('Expected instance of Symfony\Component\HttpFoundation\File\UploadedFile.');
                            }

                            $extension = $singleFile->guessExtension() ?? $singleFile->getClientOriginalExtension();
                            $nomDeFichier = sprintf('BC_%s-%04d.%s', $numCde, $compteur, $extension);

                            $this->traitementDeFichier->upload(
                                $singleFile,
                                $this->cheminDeBase . '/' . $numDa,
                                $nomDeFichier
                            );

                            $nomDesFichiers[] = $nomDeFichier;
                            $compteur++;
                        }
                    }
                }
            }
        }

        return $nomDesFichiers;
    }

    /**
     * Ajout de prefix pour chaque element du tableau files
     *
     * @param array $files
     * @param string $prefix
     * @return array
     */
    private function addPrefixToElementArray(array $files, string $prefix): array
    {
        return array_map(function ($file) use ($prefix) {
            return $prefix . $file;
        }, $files);
    }

    private function autoIncrement(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return (int)$num + 1;
    }
}
