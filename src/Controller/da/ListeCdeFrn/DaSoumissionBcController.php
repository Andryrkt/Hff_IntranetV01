<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Constants\da\StatutBcConstant;
use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\Da\ListeCdeFrn\DaSoumissionBcDto;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeAppro;
use App\Factory\da\CdeFrnDto\DaSoumissionBcFactory;
use App\Form\da\soumissionBC\DaSoumissionBcType;
use App\Mapper\Da\ListCdeFrn\Bc\DaSoumissionBcMapper;
use App\Model\da\DaSoumissionBcModel;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DaSoumissionBcRepository;
use App\Repository\da\DemandeApproRepository;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\GeneratePdf;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;
use App\Service\historiqueOperation\HistoriqueOperationService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/soumission-bc/{numCde}/{numDa}/{numOr}/{typeDa}", name="da_soumission_bc", defaults={"numOr"=0, "typeDa" = null})
     */
    public function index(string $numCde, string $numDa, string $numOr, ?string $typeDa, Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        $dto = (new DaSoumissionBcFactory($this->getEntityManager()))->init((int)$numCde, (string) $numDa, (int)$numOr, (int)$typeDa, $codeSociete);

        $form = $this->getFormFactory()->createBuilder(DaSoumissionBcType::class, $dto, [
            'method' => 'POST',
        ])->getForm();

        $this->traitementFormulaire($request, $form);

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
    private function traitementFormulaire(Request $request, FormInterface $form): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSoumissionBcDto $dto */
            $dto = $form->getData();

            $numCde = $dto->numeroCde;
            $numDa = $dto->numeroDemandeAppro;
            $typeDa = $dto->typeDa;
            $codeSociete = $dto->codeSociete;

            $bcStatut = $this->daSoumissionBcRepository->getStatut($numCde, $codeSociete);
            $condition_1 =  $dto->demandePaiementAvance && !$bcStatut;
            $condition_2 = $dto->demandePaiementAvance && $bcStatut === StatutBcConstant::STATUT_REFUSE;
            if ($condition_1 || $condition_2) {
                if ($this->verifierConditionDeBlocage($dto, $numCde, $numDa, $codeSociete)) {
                    [$numeroVersionMax, $nomPdfFusionner] = $this->traitemnetBc($form, $dto, false);

                    $this->getSessionService()->set('demande_paiement_a_l_avance', ['ddpa' => $dto->demandePaiementAvance, 'nom_pdf' => $nomPdfFusionner]);
                    // redirection vers la page de creation de demande de paiement
                    $this->redirectToRoute('demande_paiement_da', [
                        'typeDdp' => 1,
                        'numCdeDa' => $numCde,
                        'typeDa' => $typeDa,
                        'numeroVersionBc' => $numeroVersionMax,
                    ]);
                }
            } else {

                if ($this->verifierConditionDeBlocage($dto, $numCde, $numDa, $codeSociete)) {

                    $this->traitemnetBc($form, $dto, true);

                    /** HISTORISATION */
                    $message = "Le document est soumis pour validation";
                    $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
                    $nomDeRoute = 'da_list_cde_frn'; // route de redirection après soumission
                    $nomInputSearch = 'cde_frn_list'; // initialistion de nom de chaque champ ou input
                    $this->historiqueOperation->sendNotificationSoumission($message, $numCde, $nomDeRoute, true, $criteria, $nomInputSearch);
                }
            }
        }
    }


    private function traitemnetBc(FormInterface $form, DaSoumissionBcDto $dto, bool $copier): array
    {
        $numCde = $dto->numeroCde;
        $numDa = $dto->numeroDemandeAppro;
        $numOr = $dto->numeroOr;
        $codeSociete = $dto->codeSociete;

        /** ENREGISTREMENT DE FICHIER */
        $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

        //numeroversion max
        $numeroVersionMax = $this->autoIncrement($this->daSoumissionBcRepository->getNumeroVersionMax($numCde, $codeSociete));
        /** FUSION DES PDF */
        $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');
        $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);
        $nomPdfFusionner =  'BCAppro!' . $numCde . '#' . $numDa . '-' . $numOr . '_' . $numeroVersionMax . '.pdf';
        $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;
        $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

        /** AJOUT DES INFO NECESSAIRE */
        $dto = (new DaSoumissionBcFactory($this->getEntityManager()))->apresSoumission($dto, $this->getUserName(), $nomPdfFusionner);
        $daSoumissionBc = DaSoumissionBcMapper::map($dto);

        /** ENREGISTREMENT DANS LA BASE DE DONNEE */
        $this->getEntityManager()->persist($daSoumissionBc);
        $this->getEntityManager()->flush();

        if ($copier) {
            /** COPIER DANS DW */
            $this->generatePdf->copyToDWBcDa($nomPdfFusionner, $numDa);

            /** modification du table da_afficher */
            $this->modificationDaAfficher($dto);
        }

        return [$numeroVersionMax, $nomPdfFusionner];
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

    private function modificationDaAfficher(DaSoumissionBcDto $dto): void
    {
        $numCde = $dto->numeroCde;
        $numDa = $dto->numeroDemandeAppro;
        $codeSociete = $dto->codeSociete;

        $numeroVersionMaxCde = $this->daAfficherRepository->getNumeroVersionMax($numDa, $codeSociete);
        $daValiders = $this->daAfficherRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMaxCde, 'numeroCde' => $numCde]);
        if (!empty($daValiders)) {
            foreach ($daValiders as $key => $daValider) {
                $daValider
                    ->setStatutCde(StatutBcConstant::STATUT_SOUMISSION);
                $this->getEntityManager()->persist($daValider);
            }

            $this->getEntityManager()->flush();
        }
    }


    private function conditionDeBlocage(DaSoumissionBcDto $dto): array
    {
        $numCde = $dto->numeroCde;
        $numDa = $dto->numeroDemandeAppro;
        $codeSociete = $dto->codeSociete;
        $montantBc = $dto->montantBc;
        $montantBcIps = $dto->montantBcIps;


        // Ensure pieceJoint1 is an UploadedFile before attempting to get its original name
        $nomdeFichier = $dto->pieceJoint1 instanceof UploadedFile ? $dto->pieceJoint1->getClientOriginalName() : '';
        $nomdeFichier = str_replace('BON_DE_COMMANDE', 'BON DE COMMANDE', $nomdeFichier);
        $statut = $this->daSoumissionBcRepository->getStatut($numCde, $codeSociete);

        //recuperation du numDa dans Informix
        $numDaInformix = $this->daSoumissionBcModel->getNumDa($numCde, $codeSociete);

        return [
            'nomDeFichier' => explode('_', $nomdeFichier)[0] <> 'BON DE COMMANDE' || explode('_', $nomdeFichier)[1] <> $numCde,
            'statut' => $statut === StatutBcConstant::STATUT_SOUMISSION || $statut === StatutBcConstant::STATUT_A_VALIDER_DA,
            'numDaEgale' => $numDaInformix[0] !== $numDa,
            'montantBcEgale' => $montantBc == $montantBcIps
        ];
    }

    private function verifierConditionDeBlocage(DaSoumissionBcDto $dto): bool
    {
        $conditions = $this->conditionDeBlocage($dto);
        $numCde = $dto->numeroCde;
        $numDa = $dto->numeroDemandeAppro;
        $pieceJoint1 = $dto->pieceJoint1;

        // Ensure pieceJoint1 is an UploadedFile before attempting to get its original name
        $nomdeFichier = $pieceJoint1 instanceof UploadedFile ? $pieceJoint1->getClientOriginalName() : '';
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
            $message = "Le numéro de DA '{$numDa}' ne correspond pas pour le BC '$numCde'";
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
