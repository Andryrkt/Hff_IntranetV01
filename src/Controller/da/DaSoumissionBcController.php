<?php

namespace App\Controller\da;

use Exception;
use App\Controller\Controller;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Service\genererPdf\GenererPdfDa;
use App\Service\fichier\TraitementDeFichier;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaSoumissionBcRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\da\soumissionBC\DaSoumissionBcType;
use App\Repository\da\DemandeApproRepository;
use App\Repository\dit\DitRepository;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionBcController extends Controller
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private  DaSoumissionBc $daSoumissionBc;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private HistoriqueOperationService $historiqueOperation;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    private GenererPdfDa $genererPdfDa;
    private DemandeApproRepository $demandeApproRepository;
    private DitRepository $ditRepository;

    public function __construct()
    {
        parent::__construct();

        $this->daSoumissionBc = new DaSoumissionBc();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->historiqueOperation      = new HistoriqueOperationDaBcService();
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
        $this->genererPdfDa = new GenererPdfDa();
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/soumission-bc/{numCde}/{numDa}", name="da_soumission_bc")
     */
    public function index(string $numCde, string $numDa, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->daSoumissionBc->setNumeroCde($numCde);

        $form = self::$validator->createBuilder(DaSoumissionBcType::class, $this->daSoumissionBc, [
            'method' => 'POST',
        ])->getForm();

        $this->traitementFormulaire($request, $numCde, $form, $numDa);

        self::$twig->display('da/soumissionBc.html.twig', [
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
    private function traitementFormulaire(Request $request, string $numCde, $form, string $numDa): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $soumissionBc = $form->getData();
            if ($this->verifierConditionDeBlocage($soumissionBc, $numCde)) {
                /** ENREGISTREMENT DE FICHIER */
                $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

                /** FUSION DES PDF */
                $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');
                $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);
                $nomPdfFusionner =  'BC_' . $numCde . '.pdf';
                $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;
                $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

                /** AJOUT DES INFO NECESSAIRE */
                $numeroVersionMax = $this->daSoumissionBcRepository->getNumeroVersionMax($numCde);
                $numDit = $this->demandeApproRepository->getNumDitDa($numDa);
                $numOr = $this->ditRepository->getNumOr($numDit);
                $soumissionBc->setNumeroCde($numCde)
                    ->setUtilisateur($this->getUser()->getNomUtilisateur())
                    ->setPieceJoint1($nomDeFichiers[0])
                    ->setStatut(self::STATUT_SOUMISSION)
                    ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                    ->setNumeroDemandeAppro($numDa)
                    ->setNumeroDemandeDit($numDit)
                    ->setNumeroOR($numOr)
                ;

                /** ENREGISTREMENT DANS LA BASE DE DONNEE */
                self::$em->persist($soumissionBc);
                self::$em->flush();

                /** COPIER DANS DW */
                $this->genererPdfDa->copyToDWBcDa('BC_' . $numCde . '.pdf', $numDa);

                /** HISTORISATION */
                $message = 'Le document est soumis pour validation';
                $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'list_cde_frn', true);
            }
        }
    }

    private function conditionDeBlocage(DaSoumissionBc $soumissionBc, string $numCde): array
    {
        $nomdeFichier = $soumissionBc->getPieceJoint1()->getClientOriginalName();
        $statut = $this->daSoumissionBcRepository->getStatut($numCde);

        return [
            'nomDeFichier' => explode('_', $nomdeFichier)[0] <> 'BON DE COMMANDE' && explode('_', $nomdeFichier)[1] <> $numCde,
            'statut' => $statut === self::STATUT_SOUMISSION,
        ];
    }

    private function verifierConditionDeBlocage(DaSoumissionBc $soumissionBc, string $numCde): bool
    {
        $conditions = $this->conditionDeBlocage($soumissionBc, $numCde);
        $nomdeFichier = $soumissionBc->getPieceJoint1()->getClientOriginalName();
        $okey = false;

        if ($conditions['nomDeFichier']) {
            $message = "Le fichier '{$nomdeFichier}' soumis a été renommé ou ne correspond pas à un BC";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'list_cde_frn');
            $okey = false;
        } elseif ($conditions['statut']) {
            $message = "Echec lors de la soumission, un BC est déjà en cours de validation ";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'list_cde_frn');
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
