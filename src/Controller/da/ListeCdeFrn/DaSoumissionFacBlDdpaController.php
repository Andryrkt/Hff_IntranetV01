<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDdpaDto;
use App\Entity\da\DaAfficher;
use App\Entity\ddp\DemandePaiement;
use App\Factory\da\CdeFrnDto\DaSoumissionFacBlDdpaFactory;
use App\Form\da\daCdeFrn\DaSoumissionFacBlDdpaType;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlDdpaMapper;
use App\Mapper\ddp\DemandePaiementMapper;
use App\Repository\da\DaAfficherRepository;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\GeneratePdf;
use App\Service\genererPdf\GeneratePdfDdp;
use App\Service\historiqueOperation\HistoriqueOperationDaFacBlService;
use App\Service\historiqueOperation\HistoriqueOperationService;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionFacBlDdpaController extends Controller
{
    use PdfConversionTrait;

    private DaSoumissionFacBlDdpaFactory $daSoumissionFacBlDdpaFactory;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private string $cheminDeBaseDdp;
    private HistoriqueOperationService $historiqueOperation;
    private DaAfficherRepository $daAfficherRepository;
    private DaSoumissionFacBlDdpaMapper $daSoumissionfacBlDdpaMapper;
    private GeneratePdfDdp $generatePdfDdp;

    public function __construct()
    {
        $this->daSoumissionFacBlDdpaFactory = new DaSoumissionFacBlDdpaFactory($this->getEntityManager());
        $this->traitementDeFichier         = new TraitementDeFichier();
        $this->cheminDeBase                = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->cheminDeBaseDdp             = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $this->historiqueOperation = new HistoriqueOperationDaFacBlService($this->getEntityManager());
        $this->daAfficherRepository        = $this->getEntityManager()->getRepository(DaAfficher::class);
        $this->daSoumissionfacBlDdpaMapper = new DaSoumissionFacBlDdpaMapper();
        $this->generatePdfDdp                 = new GeneratePdfDdp();
    }

    /**
     * @Route("/soumission-facbl-ddpa/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl_ddpa", defaults={"numOr"=0})
     */
    public function index(int $numCde, ?string $numDa, ?int $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //initialisation 
        $dto = $this->daSoumissionFacBlDdpaFactory->initialisation($numCde, $numDa, $numOr, $this->getUser());

        // creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DaSoumissionFacBlDdpaType::class, $dto, [
            'method'  => 'POST'
        ])->getForm();

        //traitement du formulaire
        $this->TraitementFormualire($request, $form);


        return $this->render('da/soumissionFacBlDdpa.html.twig', [
            'form' => $form->createView(),
            'dto' => $dto
        ]);
    }

    private function TraitementFormualire(Request $request, FormInterface $form)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSoumissionFacBlDdpaDto $dto */
            $dto = $form->getData();

            $numCde  = $dto->numeroCde;
            $numDa   = $dto->numeroDemandeAppro;

            if ($this->verifierConditionDeBlocage($dto)) {
                // Traitement du fichier
                [$nomAvecCheminPdfFusionner, $nomPdfFusionner] = $this->traitementDeFichier($form, $dto);

                /** GENERATION DE PDF pour le demnade de paiement */
                $nomPageDeGarde = $dto->numeroDdp . '.pdf';
                $cheminEtNom = $this->cheminDeBase . '/' . $dto->numeroDdp . '_New_1/' . $nomPageDeGarde;
                // $this->generatePdfDdp->genererPDF($dto, $cheminEtNom);

                // enrichissement Dto
                $dto  = $this->daSoumissionFacBlDdpaFactory->enrichissementDtoApresSoumission($dto, $nomPdfFusionner);
                /** ENREGISTREMENT DANS LA BASE DE DONNEE */
                $daSoumissionFacBl = $this->daSoumissionfacBlDdpaMapper->map($dto);
                $this->getEntityManager()->persist($daSoumissionFacBl);
                $this->getEntityManager()->flush();

                /** COPIER DANS DW */
                $this->generatePdf->copyToDWFacBlDa($nomPdfFusionner, $numDa);

                /** MODIFICATION DA AFFICHER */
                $this->modificationDaAfficher($numDa, $numCde);

                // enregisstrement dans la table demande de paiement
                $nomAvecCheminPdfFusionner = $dto->nomAvecCheminFichierDistant;
                $ddp = DemandePaiementMapper::map($dto, $nomAvecCheminPdfFusionner);
                $this->getEntityManager()->persist($ddp);
                $this->getEntityManager()->flush();

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
        $nomOriginalFichier = $dto->pieceJoint1->getClientOriginalName();

        /** ENREGISTREMENT DE FICHIER */
        $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

        /** AJOUT DES CHEMINS DANS LE TABLEAU */
        $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');

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
     * Modification du colonne est_facture_bl_soumis dans la table da_afficher
     *
     * @param string $numDa
     * @param int $numeroVersionMax
     */
    private function modificationDaAfficher(string $numDa, string $numCde): void
    {
        $numeroVersionMax = $this->getEntityManager()->getRepository(DaAfficher::class)->getNumeroVersionMax($numDa);
        $daAffichers = $this->getEntityManager()->getRepository(DaAfficher::class)->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax, 'numeroCde' => $numCde]);

        foreach ($daAffichers as  $daAfficher) {
            if (!$daAfficher instanceof DaAfficher) {
                throw new Exception('Erreur: L\'objet DaAfficher est invalide.');
            }
            $daAfficher->setEstFactureBlSoumis(true);
            $this->getEntityManager()->persist($daAfficher);
        }
        $this->getEntityManager()->flush();
    }

    private function verifierConditionDeBlocage(DaSoumissionFacBlDdpaDto $dto): bool
    {
        $numCde = $dto->numeroCde;
        $nomOriginalFichier = $dto->pieceJoint1->getClientOriginalName();

        $nonReceptionnes = array_filter($dto->receptions, function ($item) {
            return $item->statutRecep === 'Non receptionnee';
        });

        $message = '';
        $okey = true;

        // Blocage si le nom de fichier contient des caractères spéciaux
        if (preg_match('/[#\-_~]/', $nomOriginalFichier)) {
            $message = "Le nom de fichier ('{$nomOriginalFichier}') n'est pas valide. Il ne doit pas contenir les caractères suivants : #, -, _ ou ~. Merci de renommer votre fichier avant de le soumettre dans DocuWare.";
            $okey = false;
        } elseif (!empty($nonReceptionnes)) {
            $message = " il y des quantités non réceptionné sur la commande a fait objet d'une demande de paiement à l'avance (non refusé) ";
            $okey = false;
        }

        if (!$okey) $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');

        return $okey;
    }
}
