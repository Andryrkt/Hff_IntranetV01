<?php

namespace App\Controller\da;

use Exception;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionFacBl;
use App\Repository\dit\DitRepository;
use App\Form\da\DaSoumissionFacBlType;
use App\Entity\dit\DemandeIntervention;
use App\Service\fichier\TraitementDeFichier;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Service\genererPdf\GenererPdfDaAvecDit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionFacBlController extends Controller
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private  DaSoumissionFacBl $daSoumissionFacBl;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private HistoriqueOperationService $historiqueOperation;
    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private GenererPdfDaAvecDit $genererPdfDaAvecDit;
    private DemandeApproRepository $demandeApproRepository;
    private DitRepository $ditRepository;

    public function __construct()
    {
        parent::__construct();

        $this->daSoumissionFacBl = new DaSoumissionFacBl();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->historiqueOperation      = new HistoriqueOperationDaBcService();
        $this->daSoumissionFacBlRepository = self::$em->getRepository(DaSoumissionFacBl::class);
        $this->genererPdfDaAvecDit = new GenererPdfDaAvecDit();
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/soumission-facbl/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl")
     */
    public function index(string $numCde, string $numDa, string $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->daSoumissionFacBl->setNumeroCde($numCde);

        $form = self::$validator->createBuilder(DaSoumissionFacBlType::class, $this->daSoumissionFacBl, [
            'method' => 'POST',
        ])->getForm();

        $this->traitementFormulaire($request, $numCde, $form, $numDa, $numOr);

        self::$twig->display('da/soumissionFacBl.html.twig', [
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
            $soumissionFacBl = $form->getData();

            /** ENREGISTREMENT DE FICHIER */
            $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

            //numeroversion max
            $numeroVersionMax = $this->autoIncrement($this->daSoumissionFacBlRepository->getNumeroVersionMax($numCde));
            /** FUSION DES PDF */
            $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');
            $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);
            $nomPdfFusionner =  'FACBL' . $numCde . '#' . $numDa . '-' . $numOr . '_' . $numeroVersionMax . '.pdf';
            $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;
            $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

            /** AJOUT DES INFO NECESSAIRE */
            $soumissionFacBl = $this->ajoutInfoNecesaireSoumissionFacBl($numCde, $numDa, $soumissionFacBl, $nomPdfFusionner, $numeroVersionMax);

            /** ENREGISTREMENT DANS LA BASE DE DONNEE */
            self::$em->persist($soumissionFacBl);
            self::$em->flush();

            /** COPIER DANS DW */
            $this->genererPdfDaAvecDit->copyToDWFacBlDa($nomPdfFusionner, $numDa);

            /** HISTORISATION */
            $message = 'Le document est soumis pour validation';
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn', true);
        }
    }

    private function ajoutInfoNecesaireSoumissionFacBl(string $numCde, string $numDa, DaSoumissionFacBl $soumissionFacBl, string $nomPdfFusionner, int $numeroVersionMax): DaSoumissionFacBl
    {
        $numDit = $this->demandeApproRepository->getNumDitDa($numDa);
        $numOr = $this->ditRepository->getNumOr($numDit);
        $soumissionFacBl->setNumeroCde($numCde)
            ->setUtilisateur($this->getUserNameUser())
            ->setPieceJoint1($nomPdfFusionner)
            ->setStatut(self::STATUT_SOUMISSION)
            ->setNumeroVersion($numeroVersionMax)
            ->setNumeroDemandeAppro($numDa)
            ->setNumeroDemandeDit($numDit)
            ->setNumeroOR($numOr)
        ;
        return $soumissionFacBl;
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

    private function autoIncrement(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return (int)$num + 1;
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
}
