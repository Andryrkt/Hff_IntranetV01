<?php

namespace App\Controller\da\ListeCdeFrn;

use Exception;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionFacBl;
use App\Entity\dw\DwBcAppro;
use App\Form\da\DaSoumissionFacBlType;
use App\Model\da\DaModel;
use App\Model\dit\DitModel;
use App\Repository\da\DaAfficherRepository;
use App\Service\genererPdf\GeneratePdf;
use App\Service\fichier\TraitementDeFichier;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Repository\dw\DwBcApproRepository;
use App\Service\autres\VersionService;
use App\Service\dataPdf\ordreReparation\Recapitulation;
use App\Service\genererPdf\bap\GenererPdfBonAPayer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;
use DateTime;
use Symfony\Component\Form\FormInterface;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionFacBlController extends Controller
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private HistoriqueOperationService $historiqueOperation;
    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private GeneratePdf $generatePdf;
    private DemandeApproRepository $demandeApproRepository;
    private DwBcApproRepository $dwBcApproRepository;
    private DaAfficherRepository $daAfficherRepository;

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
    }

    /**
     * @Route("/soumission-facbl/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl", defaults={"numOr"=0})
     */
    public function index(string $numCde, string $numDa, string $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $infosLivraison = $this->getInfoLivraison($numCde, $numDa);

        $daSoumissionFacBl = $this->initialisationFacBl($numCde, $numDa, $numOr);
        $form = $this->getFormFactory()->createBuilder(DaSoumissionFacBlType::class, $daSoumissionFacBl, [
            'method'  => 'POST',
            'numLivs' => array_keys($infosLivraison),
        ])->getForm();

        $this->traitementFormulaire($request, $form, $infosLivraison);

        return $this->render('da/soumissionFacBl.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function initialisationFacBl(string $numCde, string $numDa, string $numOr): DaSoumissionFacBl
    {
        $numDit = $this->demandeApproRepository->getNumDitDa($numDa);
        $dateLivraisonPrevue = $this->daAfficherRepository->getDateLivraisonPrevue($numDa, $numCde);
        return (new DaSoumissionFacBl)
            ->setNumeroCde($numCde)
            ->setUtilisateur($this->getUserName())
            ->setStatut(self::STATUT_SOUMISSION)
            ->setNumeroDemandeAppro($numDa)
            ->setNumeroDemandeDit($numDit)
            ->setNumeroOR($numOr)
            ->setDateBlFac($dateLivraisonPrevue ? new DateTime($dateLivraisonPrevue) : null)
        ;
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
    private function traitementFormulaire(Request $request, FormInterface $form, array $infosLivraison): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSoumissionFacBl $soumissionFacBl */
            $soumissionFacBl = $form->getData();
            $numCde  = $soumissionFacBl->getNumeroCde();
            $numDa   = $soumissionFacBl->getNumeroDemandeAppro();
            $numOr   = $soumissionFacBl->getNumeroOR();
            $numLiv  = $soumissionFacBl->getNumLiv();
            $infoLiv = $infosLivraison[$numLiv];
            $nomOriginalFichier = $soumissionFacBl->getPieceJoint1()->getClientOriginalName();

            if ($this->verifierConditionDeBlocage($soumissionFacBl, $infoLiv, $nomOriginalFichier)) {
                /** ENREGISTREMENT DE FICHIER */
                $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

                /** AJOUT DES CHEMINS DANS LE TABLEAU */
                $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');

                /** CREATION DE LA PAGE DE GARDE */
                $pageDeGarde = $this->genererPageDeGarde($infoLiv, $soumissionFacBl);

                /** AJOUT DE LA PAGE DE GARDE A LA PREMIERE POSITION */
                $nomFichierAvecChemins = $this->traitementDeFichier->insertFileAtPosition($nomFichierAvecChemins, $pageDeGarde, 0);

                /** CONVERTIR LES PDF */
                $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);

                /** GENERATION DU NOM DU FICHIER */
                $numeroVersionMax          = VersionService::autoIncrement($this->daSoumissionFacBlRepository->getNumeroVersionMax($numCde));
                $nomPdfFusionner           =  "FACBL$numCde#$numDa-{$numOr}_{$numeroVersionMax}~{$nomOriginalFichier}";
                $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;

                /** FUSION DES PDF */
                $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

                /** AJOUT DES INFO NECESSAIRE */
                $this->ajoutInfoNecesaireSoumissionFacBl($soumissionFacBl, $nomPdfFusionner, $numeroVersionMax, $infoLiv);

                /** ENREGISTREMENT DANS LA BASE DE DONNEE */
                $this->getEntityManager()->persist($soumissionFacBl);
                $this->getEntityManager()->flush();

                /** COPIER DANS DW */
                $this->generatePdf->copyToDWFacBlDa($nomPdfFusionner, $numDa);

                /** MODIFICATION DA AFFICHER */
                $this->modificationDaAfficher($numDa, $numCde);

                /** HISTORISATION */
                $message = 'Le document est soumis pour validation';
                $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn', true);
            }
        }
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

    private function ajoutInfoNecesaireSoumissionFacBl(DaSoumissionFacBl $soumissionFacBl, string $nomPdfFusionner, int $numeroVersionMax, array $infoLivraison)
    {
        $soumissionFacBl
            ->setPieceJoint1($nomPdfFusionner)
            ->setNumeroVersion($numeroVersionMax)
            ->setDateClotLiv(new DateTime($infoLivraison["date_clot"]))
            ->setRefBlFac($infoLivraison["ref_fac_bl"])
        ;
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

    private function ConvertirLesPdf(array $tousLesFichersAvecChemin)
    {
        $tousLesFichiers = [];
        foreach ($tousLesFichersAvecChemin as $filePath) {
            $tousLesFichiers[] = $this->convertPdfWithGhostscript($filePath);
        }

        return $tousLesFichiers;
    }


    private function convertPdfWithGhostscript($filePath)
    {
        $gsPath = 'C:\Program Files\gs\gs10.05.0\bin\gswin64c.exe'; // Modifier selon l'OS
        $tempFile = $filePath . "_temp.pdf";

        // Vérifier si le fichier existe et est accessible
        if (!file_exists($filePath)) {
            throw new Exception("Fichier introuvable : $filePath");
        }

        if (!is_readable($filePath)) {
            throw new Exception("Le fichier PDF ne peut pas être lu : $filePath");
        }

        // Commande Ghostscript
        $command = "\"$gsPath\" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -o \"$tempFile\" \"$filePath\"";
        // echo "Commande exécutée : $command<br>";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            echo "Sortie Ghostscript : " . implode("\n", $output);
            throw new Exception("Erreur lors de la conversion du PDF avec Ghostscript");
        }

        // Remplacement du fichier
        if (!rename($tempFile, $filePath)) {
            throw new Exception("Impossible de remplacer l'ancien fichier PDF.");
        }

        return $filePath;
    }

    private function getInfoLivraison(string $numCde, string $numDa): array
    {
        $infosLivraisons = (new DaModel)->getInfoLivraison($numCde);

        if (empty($infosLivraisons)) {
            $message = "La commande n° <b>$numCde</b> n'a pas de livraison associé dans IPS. Merci de bien vérifier le numéro de la commande.";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
        }

        $livraisonSoumis = $this->daSoumissionFacBlRepository->getAllLivraisonSoumis($numDa, $numCde);

        foreach ($livraisonSoumis as $numLiv) {
            unset($infosLivraisons[$numLiv]); // exclure les livraisons déjà soumises
        }

        if (empty($infosLivraisons)) {
            $message = "La commande n° <b>$numCde</b> n'a plus de livraison à soumettre. Toutes les livraisons associées ont déjà été soumises.";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
        }

        return $infosLivraisons;
    }

    private function conditionDeBlocage(array $infoLivraison, string $nomOriginalFichier): array
    {
        return [
            'nomDeFichier' => preg_match('/[#\-_~]/', $nomOriginalFichier), // contient au moins un des caractères
            'nonCloture'   => !empty($infoLivraison) && isset($infoLivraison['date_clot']) && $infoLivraison['date_clot'] === null,
        ];
    }

    private function verifierConditionDeBlocage(DaSoumissionFacBl $soumissionFacBl, array $infoLivraison, string $nomOriginalFichier): bool
    {
        $numCde = $soumissionFacBl->getNumeroCde();
        $numLiv = $soumissionFacBl->getNumLiv();
        $conditions = $this->conditionDeBlocage($infoLivraison, $nomOriginalFichier);

        $okey = true;

        if ($conditions['nonCloture']) {
            $message = "La livraison n° '$numLiv' associée à la commande n° '$numCde' n'est pas encore clôturée. Merci de clôturer la livraison avant de soumettre le document dans DocuWare.";

            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        } elseif ($conditions['nomDeFichier']) {
            $message = "Le nom de fichier ('{$nomOriginalFichier}') n'est pas valide. Il ne doit pas contenir les caractères suivants : #, -, _ ou ~. Merci de renommer votre fichier avant de le soumettre dans DocuWare.";

            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        }

        return $okey;
    }

    private function genererPageDeGarde(array $infoLivraison, DaSoumissionFacBl $soumissionFacBl): string
    {
        $daModel          = new DaModel();
        $ditModel         = new DitModel();
        $generatePdfBap   = new GenererPdfBonAPayer();
        $recapitulationOR = new Recapitulation();

        $numCde           = $soumissionFacBl->getNumeroCde();
        $numOr            = $soumissionFacBl->getNumeroOR();

        $infoBC           = $daModel->getInfoBC($numCde);
        $infoValidationBC = $this->dwBcApproRepository->getInfoValidationBC($numCde) ?? [];
        $infoMateriel     = $ditModel->recupInfoMateriel($numOr);
        $dataRecapOR      = $recapitulationOR->getData($numOr);
        $demandeAppro     = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $soumissionFacBl->getNumeroDemandeAppro()]);
        $infoFacBl        = [
            "refBlFac"   => $infoLivraison["ref_fac_bl"],
            "dateBlFac"  => $soumissionFacBl->getDateBlFac(),
            "numLivIPS"  => $infoLivraison["num_liv"],
            "dateLivIPS" => $infoLivraison["date_clot"],
        ];

        return $generatePdfBap->genererPageDeGarde($infoBC, $infoValidationBC, $infoMateriel, $dataRecapOR, $demandeAppro, $soumissionFacBl, $infoFacBl);
    }
}
