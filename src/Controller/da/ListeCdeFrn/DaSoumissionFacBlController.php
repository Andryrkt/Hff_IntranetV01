<?php

namespace App\Controller\da\ListeCdeFrn;

use Exception;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionFacBl;
use App\Form\da\DaSoumissionFacBlType;
use App\Model\da\DaModel;
use App\Service\genererPdf\GeneratePdf;
use App\Service\fichier\TraitementDeFichier;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Service\autres\VersionService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;

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

    public function __construct()
    {
        parent::__construct();

        $this->generatePdf = new GeneratePdf();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->historiqueOperation      = new HistoriqueOperationDaBcService($this->getEntityManager());
        $this->daSoumissionFacBlRepository = $this->getEntityManager()->getRepository(DaSoumissionFacBl::class);
        $this->demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
    }

    /**
     * @Route("/soumission-facbl/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl", defaults={"numOr"=0})
     */
    public function index(string $numCde, string $numDa, string $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $daSoumissionFacBl = $this->initialisationFacBl($numCde, $numDa, $numOr);

        $form = $this->getFormFactory()->createBuilder(DaSoumissionFacBlType::class, $daSoumissionFacBl, [
            'method' => 'POST',
        ])->getForm();

        $this->traitementFormulaire($request, $numCde, $form, $numDa, $numOr);

        return $this->render('da/soumissionFacBl.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function initialisationFacBl(string $numCde, string $numDa, string $numOr): DaSoumissionFacBl
    {
        $numDit = $this->demandeApproRepository->getNumDitDa($numDa);
        return (new DaSoumissionFacBl)
            ->setNumeroCde($numCde)
            ->setUtilisateur($this->getUserName())
            ->setStatut(self::STATUT_SOUMISSION)
            ->setNumeroDemandeAppro($numDa)
            ->setNumeroDemandeDit($numDit)
            ->setNumeroOR($numOr)
        ;
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
            $soumissionFacBl = $form->getData();
            $numLiv = $soumissionFacBl->getNumLiv();
            $infoLivraison = $this->getInfoLivraison($numCde, $numLiv);
            $nomOriginalFichier = $soumissionFacBl->getPieceJoint1()->getClientOriginalName();
            if ($this->verifierConditionDeBlocage($soumissionFacBl, $numCde, $infoLivraison, $nomOriginalFichier)) {
                /** ENREGISTREMENT DE FICHIER */
                $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

                //numeroversion max
                $numeroVersionMax = VersionService::autoIncrement($this->daSoumissionFacBlRepository->getNumeroVersionMax($numCde));
                /** FUSION DES PDF */
                $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');
                $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);
                $nomPdfFusionner =  'FACBL' . $numCde . '#' . $numDa . '-' . $numOr . '_' . $numeroVersionMax . '~' . $nomOriginalFichier;
                $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;
                $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

                /** AJOUT DES INFO NECESSAIRE */
                $this->ajoutInfoNecesaireSoumissionFacBl($soumissionFacBl, $numCde, $nomPdfFusionner, $numeroVersionMax);

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

    private function ajoutInfoNecesaireSoumissionFacBl(DaSoumissionFacBl $soumissionFacBl, string $numCde, string $nomPdfFusionner, int $numeroVersionMax)
    {
        $soumissionFacBl
            ->setNumeroCde($numCde)
            ->setPieceJoint1($nomPdfFusionner)
            ->setNumeroVersion($numeroVersionMax)
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

    private function getInfoLivraison(string $numCde, string $numLiv)
    {
        $infosLivraison = (new DaModel)->getInfoLivraison($numLiv);

        if (empty($infosLivraison)) return [];

        foreach ($infosLivraison as $data) {
            if ($data['num_cde'] === $numCde) return $data;
        }
        return ['num_cde' => false, 'num_liv' => $numLiv];
    }

    private function conditionDeBlocage(DaSoumissionFacBl $soumissionFacBl, array $infoLivraison): array
    {
        $nomDeFichier = $soumissionFacBl->getPieceJoint1()->getClientOriginalName();

        return [
            'nomDeFichier'        => preg_match('/[#\-_~]/', $nomDeFichier), // contient au moins un des caractères
            'livraisonVide'       => empty($infoLivraison),
            'pasDeCorrespondance' => $infoLivraison['num_cde'] === false,
            'nonCloture'          => isset($infoLivraison['date_clot']) && $infoLivraison['date_clot'] === null,
        ];
    }

    private function verifierConditionDeBlocage(DaSoumissionFacBl $soumissionFacBl, $numCde, $infoLivraison, $nomOriginalFichier): bool
    {
        $conditions = $this->conditionDeBlocage($soumissionFacBl, $infoLivraison);

        $okey = true;

        if ($conditions['livraisonVide']) {
            $message = "Le numéro de la livraison n'existe pas dans IPS. Merci de bien vérifier le numéro de la livraison.";

            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        } elseif ($conditions['pasDeCorrespondance']) {
            $message = "Le numéro de livraison '" . $infoLivraison['num_liv'] . "' ne correspond pas au numéro de commande '$numCde'. Merci de bien vérifier le numéro de la livarison de la commande.";

            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        } elseif ($conditions['nonCloture']) {
            $message = "La livraison n'est pas encore clôturée. Merci de clôturer d'abord la livraison.";

            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        } elseif ($conditions['nomDeFichier']) {
            $message = "Le nom de fichier ('{$nomOriginalFichier}') n'est pas valide. Il ne doit pas contenir les caractères suivants : #, -, _ ou ~. Merci de renommer votre fichier avant de le soumettre dans DocuWare.";

            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        }

        return $okey;
    }
}
