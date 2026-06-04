<?php

namespace App\Controller\ddp;

use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\da\DemandeAppro;
use App\Entity\dw\DwBcAppro;
use App\Factory\ddp\DemandePaiementFactory;
use App\Form\ddp\DemandePaiementDaType;
use App\Model\ddp\DemandePaiementModel;
use App\Model\dit\DitModel;
use App\Repository\da\DemandeApproRepository;
use App\Repository\dw\DwBcApproRepository;
use App\Service\dataPdf\ordreReparation\Recapitulation;
use App\Service\ddp\DdpaDaService;
use App\Service\ddp\DdpGeneratorNameService;
use App\Service\ddp\DemandePaiementCommandeService;
use App\Service\ddp\DemandePaiementLigneService;
use App\Service\ddp\DemandePaiementService;
use App\Service\ddp\DocDemandePaiementService;
use App\Service\fichier\TraitementDeFichier;
use App\Service\fichier\UploderFileService;
use App\Service\genererPdf\ddp\GeneratePdfDdpDa;
use App\Service\historiqueOperation\HistoriqueOperationDDPService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DemandePaiementDaController extends Controller
{
    use PdfConversionTrait;

    private DemandePaiementModel $demandePaiementModel;
    private DemandePaiementLigneService $demandePaiementLigneService;
    private DemandePaiementService $demandePaiementService;
    private DocDemandePaiementService $docDemandePaiementService;
    private HistoriqueOperationDDPService $historiqueOperation;
    private DemandePaiementFactory $demandePaiementFactory;
    private DemandeApproRepository $demandeApproRepository;
    private DwBcApproRepository $dwBcApproRepository;
    private DitModel $ditModel;
    private Recapitulation $recapitulationOR;

    public function __construct(
        DemandePaiementModel $demandePaiementModel,
        DemandePaiementLigneService $demandePaiementLigneService,
        DemandePaiementService $demandePaiementService,
        DocDemandePaiementService $docDemandePaiementService,
        HistoriqueOperationDDPService $historiqueOperation,
        DemandePaiementFactory $demandePaiementFactory,
        DitModel $ditModel,
        Recapitulation $recapitulationOR

    ) {
        parent::__construct();
        $this->demandePaiementModel = $demandePaiementModel;
        $this->demandePaiementLigneService = $demandePaiementLigneService;
        $this->demandePaiementService = $demandePaiementService;
        $this->docDemandePaiementService = $docDemandePaiementService;
        $this->historiqueOperation = $historiqueOperation;
        $this->demandePaiementFactory = $demandePaiementFactory;
        $this->demandeApproRepository      = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->dwBcApproRepository         = $this->getEntityManager()->getRepository(DwBcAppro::class);
        $this->ditModel                    = $ditModel;
        $this->recapitulationOR            = $recapitulationOR;
    }

    /**
     * @Route("/newDdpa/{typeDdp}/{numCdeDa}/{typeDa}/{numeroVersionBc}/{numOr}", name="demande_paiement_da", defaults={"numCdeDa"=null, "typeDa"=null, "numeroVersionBc"=null, "numOr"=null}, methods={"GET","POST"})
     */
    public function index(
        int $typeDdp,
        ?int $numCdeDa,
        ?int $typeDa,
        ?int $numeroVersionBc,
        ?string $numOr,
        Request $request
    ) {
        // try {
        // initialisation dto
        $numeroVersionBc = $numeroVersionBc ?? 0;

        $dto = $this->demandePaiementFactory
            ->load(
                $typeDdp,
                $numCdeDa,
                $typeDa,
                $numeroVersionBc,
                $numOr,
                $this->getSessionService()
            );
        // } catch (\Throwable $th) {
        //     $message = $th->getMessage();
        //     $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
        //     $nomDeRoute = 'da_list_cde_frn'; // route de redirection après soumission
        //     $nomInputSearch = 'cde_frn_list'; // initialistion de nom de chaque champ ou input
        //     $this->historiqueOperation->sendNotificationSoumission($message, $numCdeDa, $nomDeRoute, false, $criteria, $nomInputSearch);
        // }

        // blocage soumission si montant a regulariser <= 0
        $this->blocageSoumission($dto);
        // creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DemandePaiementDaType::class, $dto, [
            'method' => 'POST',
            'em' => $this->getEntityManager()
        ])->getForm();

        //traitement du formulaire
        $this->traitementDuFormulaire($request, $form);


        return $this->render('ddp/demande_paiement_da_new.html.twig', [
            'dto' => $dto,
            'form' => $form->createView(),
            'baseUrlFichier' => $_ENV['BASE_PATH_FICHIER_COURT'] . 'da/' . $dto->numeroDa . '/',
        ]);
    }

    private function blocageSoumission(DemandePaiementDto $dto)
    {
        if ($dto->estRegule) {
            $message = "La soumission doit être de type régularisation";
            $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
            $nomDeRoute = 'da_list_cde_frn'; // route de redirection après soumission
            $nomInputSearch = 'cde_frn_list'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperation->sendNotificationSoumission($message, $dto->numeroDdp, $nomDeRoute, false, $criteria, $nomInputSearch);
        }
    }

    private function traitementDuFormulaire(Request $request, FormInterface $form)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getdata();

            $nomFichier = $this->traitementDeFichier($dto, $form);
            $this->enregistrementSurBd($dto, $nomFichier);

            // si on crée le demande de paiement avance avec la soumission BC
            if ($dto->ddpaDa) {
                $ddpaDaService = new DdpaDaService($this->getEntityManager());
                $ddpaDaService
                    ->modificationtableDaSoumissionBc($dto)
                    ->copieBcDansDw($dto)
                    ->modificationStatutBcDansDaAfficher($dto);
            }

            /** HISTORISATION */
            $message = "Le document a été généré avec succès";
            $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
            $nomDeRoute = 'da_bon_a_payer'; // route de redirection après soumission
            $nomInputSearch = 'cde_frn_list'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperation->sendNotificationSoumission($message, $dto->numeroDdp, $nomDeRoute, true, $criteria, $nomInputSearch, [], null, true);
        }
    }

    private function pageDeGarde(DemandePaiementDto $dto, string $cheminEtNom): GeneratePdfDdpDa
    {
        // =============
        $numCde              = $dto->numeroCommande;
        $numOr               = $dto->numeroOr;
        $codeSociete         = $dto->codeSociete;

        $infoValidationBC    = $this->dwBcApproRepository->getInfoValidationBC($numCde) ?? [];
        $historiqueLivraison = [];

        $infoMateriel        = $this->ditModel->recupInfoMateriel($numOr, $codeSociete);
        $dataRecapOR         = $this->recapitulationOR->getData($numOr, $codeSociete);
        $demandeAppro        = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $dto->numeroDemandeAppro]);
        $infoFacBl           = [];
        //=============

        $generatePdfDdp = new GeneratePdfDdpDa();
        $generatePdfDdp->generer($infoValidationBC, $infoMateriel, $dataRecapOR, $historiqueLivraison, $demandeAppro, $infoFacBl, $dto, $dto, $cheminEtNom);

        return $generatePdfDdp;
    }

    private function copierFichierDistant(DemandePaiementDto $dto): void
    {
        if ($dto->typeDemande->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $this->docDemandePaiementService->copierFichierDistant($dto);
        }
    }

    private function enregistrementSurBd(DemandePaiementDto $dto, string $nomFichier): void
    {
        // enregistrement dans la table deamnde_paiement
        $ddp = $this->demandePaiementService->createDdp($dto);
        // enregistrement dans la table demande_paiement_ligne
        $this->demandePaiementLigneService->createLignesFromDto($dto);
        // enregistrement dans la table doc_demande_paiement
        $this->docDemandePaiementService->createDocDdp($dto);
        // enregistrement dans la table historique_statut_ddp
        $this->demandePaiementService->createHistoriqueStatut($dto);
        // enregistrement dans la table demande_paiement_commande
        $demandePaiementCommandeService = new DemandePaiementCommandeService($this->getEntityManager());
        $demandePaiementCommandeService->createDdpCommande($dto, $ddp);
    }

    private function traitementDeFichier(DemandePaiementDto $dto, FormInterface $form): string
    {
        $numCdes = [];
        $numeroCommandes = '';

        if (!empty($dto->numeroFournisseur) && $dto->numeroFournisseur !== '-') {
            $numCdes = $this->demandePaiementModel->getCommandeReceptionnee($dto->numeroFournisseur);
            $numCdesString = !empty($numCdes) ? (string) $numCdes[0] : '';
            $numFacString =  $dto->numeroFacture;
            $numeroCommandes = $this->demandePaiementModel->getNumCommande($dto->numeroFournisseur, $numCdesString, $numFacString);
        }

        /** TRAITEMENT FICHIER  AUTRE DOCUMENT ET BC client externe / BC client magasin*/
        if ($dto->pieceJoint04 !== null) {
            $dto->estAutresDoc = true;
            $dto->nomAutreDoc = $dto->pieceJoint04->getClientOriginalName();
        }

        if ($dto->pieceJoint03 !== null || !empty($dto->pieceJoint03)) {
            $nomFichierBCs = [];
            foreach ($dto->pieceJoint03 as $value) {
                $nomFichierBCs[] = $value->getClientOriginalName();
            }
            $dto->estCdeClientExterneDoc = true;
            $dto->nomCdeClientExterneDoc = $nomFichierBCs;
        }

        /** ENREGISTREMENT DU FICHIER */
        $this->copierFichierDistant($dto);
        [$nomEtCheminFichiersEnregistrer, $nomFichiersTelecharger,  $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $dto);
        if ($dto->typeDemande->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $dto->numeroCommande = $numeroCommandes;
        }
        $dto->lesFichiers = $this->docDemandePaiementService->fusionDesFichiersDansUnTableau($dto, $nomFichiersTelecharger);
        // generation de la page de garde DDP
        $this->pageDeGarde($dto, $nomAvecCheminFichier);
        $fichierChoisiAvecChemins = $this->docDemandePaiementService->fichierChoisiAvecChemin($dto);
        $this->docDemandePaiementService->copieFichierChoisi($dto);
        // fusion des PDF (page de garde DDP+ autres documents)
        $this->fusionDesPdf($nomEtCheminFichiersEnregistrer, $fichierChoisiAvecChemins, $nomAvecCheminFichier);

        return $nomFichier;
    }



    private function enregistrementFichier(FormInterface $form, DemandePaiementDto $dto): array
    {
        $nameGenerator = new DdpGeneratorNameService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/ddp/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $path = $cheminBaseUpload . $dto->numeroDdp . '/';
        if (!is_dir($path)) mkdir($path, 0777, true);

        [$nomEtCheminFichiersEnregistrer, $nomFichierTelecharger] = $uploader->getFichiers($form, [
            'repertoire' => $path,
            'conserver_nom_original' => false,
            'generer_nom_callback' => function ($file, $index, $extension, $variables) use ($dto) {
                $fieldName = $variables['field_name'] ?? '';
                $numDdp = $dto->numeroDdp;

                $mapping = [
                    'pieceJoint01' => 'PROFORMA',
                    'pieceJoint02' => 'RIB',
                    'pieceJoint03' => 'BC',
                    'pieceJoint04' => 'AUTRES FICHIERS',
                ];

                $baseName = $mapping[$fieldName] ?? 'Document';

                // Si c'est le champ multiple pieceJoint03 ou s'il y a plusieurs fichiers, on ajoute l'index
                if ($fieldName === 'pieceJoint03') {
                    return sprintf("%s_%s_%02d.%s", $baseName, $numDdp, $index, $extension);
                }

                return sprintf("%s_%s.%s", $baseName, $numDdp, $extension);
            }
        ]);

        $nomFichier = $nameGenerator->generateNamePrincipal($dto->numeroDdp);
        $nomAvecCheminFichier = $path . '/' . $nomFichier;

        return [$nomEtCheminFichiersEnregistrer, $nomFichierTelecharger,  $nomAvecCheminFichier, $nomFichier];
    }

    private function fusionDesPdf(array $nomEtCheminFichiersEnregistrer, array $fichierChoisiAvecChemins, string $nomAvecCheminFichier): void
    {
        $traitementDeFichier = new TraitementDeFichier();
        $nomEtCheminFichiersEnregistrer = array_merge($nomEtCheminFichiersEnregistrer, $fichierChoisiAvecChemins);
        $fichierConvertir = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
        $tousLesFichersAvecChemin = $traitementDeFichier->insertFileAtPosition($fichierConvertir, $nomAvecCheminFichier, 0);
        $traitementDeFichier->fusionFichers($tousLesFichersAvecChemin, $nomAvecCheminFichier);
    }
}
