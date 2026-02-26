<?php

namespace App\Controller\da\ListeCdeFrn;

use DateTime;
use Exception;
use App\Model\da\DaModel;
use App\Model\dit\DitModel;
use App\Entity\dw\DwBcAppro;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\admin\Application;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DaSoumissionFacBl;
use App\Form\da\DaSoumissionFacBlType;
use App\Service\autres\VersionService;
use App\Service\genererPdf\GeneratePdf;
use App\Model\da\DaSoumissionFacBlModel;
use App\Service\autres\AutoIncDecService;
use Symfony\Component\Form\FormInterface;
use App\Repository\dw\DwBcApproRepository;
use App\Repository\da\DaAfficherRepository;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Service\genererPdf\bap\GenererPdfBonAPayer;
use App\Factory\da\CdeFrnDto\DaSoumissionFacBlFactory;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlMapper;
use App\Service\dataPdf\ordreReparation\Recapitulation;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionFacBlController extends Controller
{
    use PdfConversionTrait;

    const STATUT_SOUMISSION = 'Soumis à validation';

    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private HistoriqueOperationService $historiqueOperation;
    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private GeneratePdf $generatePdf;
    private DemandeApproRepository $demandeApproRepository;
    private DwBcApproRepository $dwBcApproRepository;
    private DaAfficherRepository $daAfficherRepository;
    private DaSoumissionFacBlModel $daSoumissionFacBlModel;
    private DaModel $daModel;
    private DaSoumissionFacBlFactory $daSoumissionFacBlFactory;
    private DaSoumissionFacBlMapper $daSoumissionfacBlMapper;

    public function __construct()
    {
        parent::__construct();

        $this->generatePdf                 = new GeneratePdf();
        $this->traitementDeFichier         = new TraitementDeFichier();
        $this->cheminDeBase                = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->historiqueOperation         = new HistoriqueOperationDaBcService($this->getEntityManager());
        $this->daSoumissionFacBlRepository = $this->getEntityManager()->getRepository(DaSoumissionFacBl::class);
        $this->demandeApproRepository      = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->dwBcApproRepository         = $this->getEntityManager()->getRepository(DwBcAppro::class);
        $this->daAfficherRepository        = $this->getEntityManager()->getRepository(DaAfficher::class);
        $this->daSoumissionFacBlModel        = new DaSoumissionFacBlModel();
        $this->daModel                     = new DaModel();
        $this->daSoumissionFacBlFactory = new DaSoumissionFacBlFactory($this->getEntityManager());
        $this->daSoumissionfacBlMapper = new DaSoumissionFacBlMapper();
    }

    /**
     * @Route("/soumission-facbl/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl", defaults={"numOr"=0})
     */
    public function index(string $numCde, string $numDa, string $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dto = $this->daSoumissionFacBlFactory->initialisation($numCde, $numDa, $numOr, $this->getUserName());

        $form = $this->getFormFactory()->createBuilder(DaSoumissionFacBlType::class, $dto, [
            'method'  => 'POST'
        ])->getForm();

        $this->traitementFormulaire($request, $form);

        return $this->render('da/soumissionFacBl.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * permet de faire le rtraitement du formulaire
     *
     * @param Request $request
     * @param FormInterface $form
     * @param array $infosLivraison
     * 
     * @return void
     */
    private function traitementFormulaire(Request $request, FormInterface $form): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSoumissionFacBlDto $dto */
            $dto = $form->getData();

            $numCde  = $dto->numeroCde;
            $numDa   = $dto->numeroDemandeAppro;
            $numLiv = $dto->numLiv;

            if ($this->verifierConditionDeBlocage($dto)) {
                // Traitement du fichier
                [$nomAvecCheminPdfFusionner, $nomPdfFusionner] = $this->traitementDeFichier($form, $dto);

                // enrichissement Dto
                $dto  = $this->daSoumissionFacBlFactory->EnrichissementDtoApresSoumission($dto, $nomPdfFusionner);
                /** ENREGISTREMENT DANS LA BASE DE DONNEE */
                $daSoumissionFacBl = $this->daSoumissionfacBlMapper->map($dto);
                $this->getEntityManager()->persist($daSoumissionFacBl);
                $this->getEntityManager()->flush();

                /** COPIER DANS DW */
                $this->generatePdf->copyToDWFacBlDa($nomPdfFusionner, $numDa);

                /** MODIFICATION DA AFFICHER */
                $this->modificationDaAfficher($numDa, $numCde, $numLiv);

                /** HISTORISATION */
                $message = 'Le document est soumis pour validation';
                $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
                $nomDeRoute = 'da_list_cde_frn'; // route de redirection après soumission
                $nomInputSearch = 'cde_frn_list'; // initialistion de nom de chaque champ ou input
                $this->historiqueOperation->sendNotificationSoumission($message, $numCde, $nomDeRoute, true, $criteria, $nomInputSearch);
            }
        }
    }

    private function traitementDeFichier($form, $dto): array
    {
        $numCde  = $dto->numeroCde;
        $numDa   = $dto->numeroDemandeAppro;
        $numOr   = $dto->numeroOR;
        $numLiv  = $dto->numLiv;
        $infoLiv = $dto->infoLiv[$numLiv];
        $nomOriginalFichier = $dto->pieceJoint1->getClientOriginalName();

        /** ENREGISTREMENT DE FICHIER */
        $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

        /** AJOUT DES CHEMINS DANS LE TABLEAU */
        $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');

        /** CREATION DE LA PAGE DE GARDE */
        $pageDeGarde = $this->genererPageDeGarde($infoLiv, $dto);

        /** AJOUT DE LA PAGE DE GARDE A LA PREMIERE POSITION */
        $nomFichierAvecChemins = $this->traitementDeFichier->insertFileAtPosition($nomFichierAvecChemins, $pageDeGarde, 0);

        /** CONVERTIR LES PDF */
        $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);

        /** GENERATION DU NOM DU FICHIER */
        $numeroVersionMax          = $dto->numeroVersion;
        $nomPdfFusionner           =  "FACBL$numCde#$numDa-{$numOr}_{$numeroVersionMax}~{$nomOriginalFichier}";
        $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;

        /** FUSION DES PDF */
        $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

        return [$nomAvecCheminPdfFusionner, $nomPdfFusionner];
    }

    /**
     * Modification du colonne est_facture_bl_soumis dans la table da_afficher
     *
     * @param string $numDa
     * @param int $numeroVersionMax
     */
    private function modificationDaAfficher(string $numDa, string $numCde, $numLiv): void
    {
        $daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
        $numeroVersionMax = $daAfficherRepository->getNumeroVersionMax($numDa);
        $typeDa = $daAfficherRepository->getTypeDaSelonNumDa($numDa);
        $daAffichers = [];

        if (in_array((int)$typeDa, [DemandeAppro::TYPE_DA_AVEC_DIT, DemandeAppro::TYPE_DA_REAPPRO_MENSUEL, DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL])) {
            $refDesiSavLors = $this->daSoumissionFacBlModel->getRefDesiSavLor($numLiv);
            foreach ($refDesiSavLors as  $refDesiSavLor) {
                $daAffichers[] = $this->getEntityManager()->getRepository(DaAfficher::class)
                    ->findOneBy(
                        [
                            'numeroDemandeAppro' => $numDa,
                            'numeroVersion' => $numeroVersionMax,
                            'numeroCde' => $numCde,
                            'artRefp' => $refDesiSavLor['reference'],
                            'artDesi' => $refDesiSavLor['designation']
                        ]
                    );
            }
        } else {
            $refDesiFrnCdls = $this->daSoumissionFacBlModel->getRefDesiFrnCdl($numLiv);
            foreach ($refDesiFrnCdls as  $refDesiFrnCdl) {
                $daAffichers[] = $this->getEntityManager()->getRepository(DaAfficher::class)
                    ->findOneBy(
                        [
                            'numeroDemandeAppro' => $numDa,
                            'numeroVersion' => $numeroVersionMax,
                            'numeroCde' => $numCde,
                            'artRefp' => $refDesiFrnCdl['reference'],
                            'artDesi' => $refDesiFrnCdl['designation']
                        ]
                    );
            }
        }


        foreach ($daAffichers as  $daAfficher) {
            if (!$daAfficher instanceof DaAfficher) {
                throw new Exception('Erreur: L\'objet DaAfficher est invalide.');
            }
            $daAfficher->setEstFactureBlSoumis(true);
            $this->getEntityManager()->persist($daAfficher);
        }
        $this->getEntityManager()->flush();
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
                            $nomDeFichier = sprintf('FACBL_%s-%04d.%s', $numCde, $compteur, $extension);

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


    private function verifierConditionDeBlocage(DaSoumissionFacBlDto $dto): bool
    {
        $numCde = $dto->numeroCde;
        $numLiv = $dto->numLiv;
        $mttFac = $dto->montantBlFacture;
        $infoLivraison = $dto->infoLiv[$numLiv];

        $nomOriginalFichier = $dto->pieceJoint1->getClientOriginalName();

        $mttFacFormate = (float)str_replace(',', '.', str_replace(' ', '', $mttFac));

        $message = '';
        $okey = true;

        // Blocage si la livraison n'est pas clôturée
        if (!empty($infoLivraison) && isset($infoLivraison['date_clot']) && $infoLivraison['date_clot'] === null) {
            $message = "La livraison n° '$numLiv' associée à la commande n° '$numCde' n'est pas encore clôturée. Merci de clôturer la livraison avant de soumettre le document dans DocuWare.";
            $okey = false;
        }
        // Blocage si le nom de fichier contient des caractères spéciaux
        elseif (preg_match('/[#\-_~]/', $nomOriginalFichier)) {
            $message = "Le nom de fichier ('{$nomOriginalFichier}') n'est pas valide. Il ne doit pas contenir les caractères suivants : #, -, _ ou ~. Merci de renommer votre fichier avant de le soumettre dans DocuWare.";
            $okey = false;
        }
        // Blocage si montant ne correspond pas au montant de la livraison dans IPS
        elseif ($mttFacFormate !== (float) $infoLivraison['montant_fac_bl']) {
            $message = "Le montant de la facture <b>{$mttFac}</b> ne correspond pas au montant de la livraison dans IPS. Merci de vérifier le montant de la facture avant de le soumettre dans DocuWare.";
            $okey = false;
        }

        if (!$okey) $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');

        return $okey;
    }

    private function genererPageDeGarde(array $infoLivraison, DaSoumissionFacBlDto $dto): string
    {
        $ditModel         = new DitModel();
        $generatePdfBap   = new GenererPdfBonAPayer();
        $recapitulationOR = new Recapitulation();

        $numCde           = $dto->numeroCde;
        $numOr            = $dto->numeroOR;


        $infoValidationBC = $this->dwBcApproRepository->getInfoValidationBC($numCde) ?? [];
        $infoMateriel     = $ditModel->recupInfoMateriel($numOr);
        $dataRecapOR      = $recapitulationOR->getData($numOr);
        $demandeAppro     = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $dto->numeroDemandeAppro]);
        $infoFacBl        = [
            "refBlFac"   => $infoLivraison["ref_fac_bl"],
            "dateBlFac"  => $dto->dateBlFac,
            "numLivIPS"  => $infoLivraison["num_liv"],
            "dateLivIPS" => $infoLivraison["date_clot"],
        ];

        return $generatePdfBap->genererPageDeGarde($infoValidationBC, $infoMateriel, $dataRecapOR, $demandeAppro, $dto, $infoFacBl);
    }
}
