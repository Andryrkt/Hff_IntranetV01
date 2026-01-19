<?php

namespace App\Controller\da\ListeCdeFrn;

use Exception;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Service\genererPdf\GeneratePdf;
use App\Service\fichier\TraitementDeFichier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Dto\Da\ListeCdeFrn\DaSoumisionBlReapproDto;
use App\Form\da\daCdeFrn\DaSoumissionBlReapprotype;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Factory\da\CdeFrnDto\DaSoumissionBlReapproFactory;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionBlReapproController extends Controller
{
    private string $cheminDeBase;
    private TraitementDeFichier $traitementDeFichier;
    private GeneratePdf $generatePdf;
    private HistoriqueOperationService $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->generatePdf = new GeneratePdf();
        $this->historiqueOperation      = new HistoriqueOperationDaBcService($this->getEntityManager());
    }

    /**
     * @Route("/soumission-bl-reappro/{numCde}/{numDa}/{numOr}", name="da_soumission_bl_reappro", defaults={"numOr"=0})
     */
    public function index(string $numCde, string $numDa, string $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dto = DaSoumissionBlReapproFactory::createFromDto($numCde);

        $form = $this->getFormFactory()->createBuilder(DaSoumissionBlReapprotype::class, $dto)->getForm();

        $this->traitementFormulaire($form, $request, $numCde, $numDa);

        return $this->render('da/SoumissionBlReappro.html.twig', [
            'form' => $form->createView(),
            'numCde' => $numCde
        ]);
    }

    private function traitementFormulaire($form,  Request $request, string $numCde, string $numDa)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** ENREGISTREMENT DE FICHIER */
            $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

            /** FUSION DES PDF */
            $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');
            $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);
            $nomPdfFusionner =  'BlReappro_' . $numCde . '#' . $numDa . '_' . '.pdf';
            $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;
            $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

            /** COPIER DANS DW */
            $this->generatePdf->copyToDWBLReappro($nomPdfFusionner, $numDa);

            /** modification du table da_valider */
            $this->modificationDaAfficher($numDa, $numCde);

            /** HISTORISATION */
            $message = 'Le document est soumis pour validation';
            $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
            $nomDeRoute = 'da_list_cde_frn'; // route de redirection après soumission
            $nomInputSearch = 'cde_frn_list'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, $nomDeRoute, true, $criteria, $nomInputSearch);
        }
    }

    private function modificationDaAfficher(string $numDa, string $numCde): void
    {
        $daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
        $numeroVersionMaxCde = $daAfficherRepository->getNumeroVersionMax($numDa);
        $daAffichers = $daAfficherRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMaxCde, 'numeroCde' => $numCde]);
        if (!empty($daAffichers)) {
            foreach ($daAffichers as  $daAfficher) {
                $daAfficher
                    ->setEstBlReapproSoumis(true);
                $this->getEntityManager()->persist($daAfficher);
            }

            $this->getEntityManager()->flush();
        }
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
                            $nomDeFichier = sprintf('BlReappro_%s-%04d.%s', $numCde, $compteur, $extension);

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
}
