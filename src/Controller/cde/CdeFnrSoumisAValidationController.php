<?php

namespace App\Controller\cde;

use Exception;
use App\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Service\fichier\FileUploaderService;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\GenererPdfCdeFnr;
use App\Form\cde\CdeFnrSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use App\Model\cde\CdefnrSoumisAValidationModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\cde\CdefnrSoumisAValidationRepository;
use App\Service\historiqueOperation\HistoriqueOperationCDEFNRService;

/**
 * @Route("/magasin")
 */
class CdefnrSoumisAValidationController extends Controller
{
    private CdefnrSoumisAValidationRepository $cdeFnrRepository;
    private HistoriqueOperationCDEFNRService $historiqueOperation;
    private TraitementDeFichier $traitementDeFichier;

    public function __construct()
    {
        parent::__construct();
        $this->cdeFnrRepository = $this->getEntityManager()->getRepository(CdefnrSoumisAValidation::class);
        $this->historiqueOperation = new HistoriqueOperationCDEFNRService($this->getEntityManager());
        $this->traitementDeFichier = new TraitementDeFichier();
    }


    /**
     * @Route("/cde-fournisseur", name="cde_fournisseur")
     */
    public function cdeFournisseur(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(CdeFnrSoumisAValidationType::class)->getForm();

        $this->traitementFormulaire($request, $form);

        return $this->render('cde/cdeFnr.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementFormulaire(Request $request, $form): void
    {

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $originalName = $data->getPieceJoint01()->getClientOriginalName();
            $codeFournisseur = array_key_exists(0, explode('_', $originalName)) ? explode('_', $originalName)[0] : '';
            $originalNameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $numCdeFournisseur = array_key_exists(1, explode('_', $originalNameWithoutExt)) ? explode('_', $originalNameWithoutExt)[1] : '';

            $blockages = $this->conditionDeBlockage($originalName, $numCdeFournisseur);

            if ($this->blockageSoumissionCdeFnr($blockages, $numCdeFournisseur, $originalName)) {
                $cdeFournisseur = $this->ajoutDonnerEntity($numCdeFournisseur, $codeFournisseur);

                //Enregistrement du fichier
                $numFnrCde = $numCdeFournisseur . '_' . $codeFournisseur;
                $fileName = $this->enregistrementFichier($form, $numFnrCde, $cdeFournisseur->getNumVersion());
                $fileNameJoints = $this->enregistreFichierJoint($form);
                array_unshift($fileNameJoints, $fileName);
                $cheminAvecNomFichier = array_map(
                    function ($file) {
                        return $_ENV['BASE_PATH_FICHIER'] . '/cde_fournisseur/' . $file;
                    },
                    $fileNameJoints
                );
                $fichierConverties = $this->ConvertirLesPdf($cheminAvecNomFichier);
                //fusion des fichiers
                $cheminEtNomFichier = $_ENV['BASE_PATH_FICHIER'] . '/cde_fournisseur/' . $fileName;
                $this->traitementDeFichier->fusionFichers($fichierConverties, $cheminEtNomFichier);
                //envoyer le ficher dans docuware
                $genererPdfCdeFnr = new GenererPdfCdeFnr();
                $genererPdfCdeFnr->copyToDWCdeFnrSoumis($fileName);

                //ajout des données dans la base de donnée
                $this->ajoutDonnerDansDb($cdeFournisseur);

                //historisation de l'operation
                $message = 'La commade fournisseur a été soumis avec succès';
                $this->historiqueOperation->sendNotificationCreation($message, $numFnrCde, 'cde_fournisseur', true);
            }
        }
    }

    private function conditionDeBlockage(string $originalName, string $numCdeFournisseur): array
    {
        $statutCdeFrn = $this->cdeFnrRepository->findStatut($numCdeFournisseur);
        $statut = ['Soumis à validation', 'Validé', 'A valider PM', 'Validation DG'];
        return [
            'nomFichier'      => !$this->verifierFormatFichier($originalName),
            'conditionStatut' => in_array($statutCdeFrn, $statut),
        ];
    }

    private function blockageSoumissionCdeFnr($blockages, $numCdeFournisseur, $originalName): bool
    {
        if ($blockages['conditionStatut']) {
            $message = " Erreur lors de la soumission, Impossible de soumettre le cde fournisseur . . . La commande {$numCdeFournisseur} est déjà en cours de validation ou valié par DG";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCdeFournisseur, 'profil_acceuil');
        } elseif ($blockages['nomFichier']) {
            $message = " Erreur lors de la soumission, Impossible de soumettre le cde fournisseur . . . Le fichier '{$originalName}' soumis a été renommé";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCdeFournisseur, 'profil_acceuil');
        } else {
            return true;
        }
    }

    /**
     * permet de vérifier le format du nom du fichier
     *
     * @param string $nomFichier
     * @return void
     */
    private function verifierFormatFichier(string $nomFichier): bool
    {
        // Pattern: ^ = début de chaîne
        //          [a-zA-Z0-9]+ = un ou plusieurs caractères alphanumériques (numeroCde)
        //          _ = underscore
        //          [a-zA-Z0-9]+ = un ou plusieurs caractères alphanumériques (numeroFRN)
        //          \.pdf$ = extension .pdf à la fin
        return preg_match('/^[a-zA-Z0-9]+_[a-zA-Z0-9]+\.pdf$/i', $nomFichier) === 1;
    }

    /**
     * permet de vérifier si une chaîne de caractères contient tous les mots donnés
     *
     * @param string $chaine
     * @param [type] ...$mots
     * @return bool
     */
    private function contientTousLesMots(string $chaine, ...$mots): bool
    {
        foreach ($mots as $mot) {
            if (strpos($chaine, $mot) === false) {
                return false;
            }
        }
        return true;
    }

    private function autoIncrement($num)
    {
        if ($num === null) {
            $num = 0;
        }
        return $num + 1;
    }

    private function ajoutDonnerEntity(string $numCdeFournisseur, string $codeFournisseur): CdefnrSoumisAValidation
    {
        $numeroVersionMax = $this->cdeFnrRepository->findNumeroVersionMax($numCdeFournisseur);

        $cdeFournisseur = new CdefnrSoumisAValidation();
        return $cdeFournisseur
            ->setDateHeureSoumission(new \DateTime())
            ->setStatut('Soumis à validation')
            ->setNumVersion($this->autoIncrement($numeroVersionMax))
            ->setNumCdeFournisseur($numCdeFournisseur)
            ->setCodeFournisseur($codeFournisseur)
        ;
    }

    private function ajoutDonnerDansDb($cdeFournisseur)
    {
        $this->getEntityManager()->persist($cdeFournisseur);
        $this->getEntityManager()->flush();
    }

    private function enregistrementFichier(FormInterface $form, string $numFnrCde, string $numeroVersion)
    {
        $chemin = $_ENV['BASE_PATH_FICHIER'] . '/cde_fournisseur/';
        $fileUploader = new FileUploaderService($chemin);
        $options = [
            'prefix' => 'cdefrn',
            'numeroDoc' => $numFnrCde,
            'mergeFiles' => false,
            'numeroVersion' => $numeroVersion,
            'mainFirstPage' => false,
            'pathFichier' => '',
            'isIndex' => false,
            'fieldPattern' => '/^pieceJoint01$/',
        ];
        $fileName = $fileUploader->chargerEtOuFusionneFichier($form, $options);

        return $fileName;
    }

    /**
     * Enregistrement des fichiers téléchagrer dans le dossier de destination
     *
     * @param [type] $form
     * @return array
     */
    private function enregistreFichierJoint($form): array
    {
        $nomDesFichiers = [];
        $chemin = $_ENV['BASE_PATH_FICHIER'] . '/cde_fournisseur/';

        $file = $form->get('pieceJoint02')->getData();


        foreach ($file as $singleFile) {
            if (is_array($singleFile)) {
                $singleFile = $singleFile[0]; // ou traiter chaque fichier du sous-tableau
            }

            if ($singleFile instanceof UploadedFile) {
                $nomDeFichier = $singleFile->getClientOriginalName();
                $this->traitementDeFichier->upload(
                    $singleFile,
                    $chemin,
                    $nomDeFichier
                );
                $nomDesFichiers[] = $nomDeFichier;
            }
        }


        return $nomDesFichiers;
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
