<?php

namespace App\Controller\da\ListeCdeFrn;

use Exception;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionFacBl;
use App\Repository\dit\DitRepository;
use App\Form\da\DaSoumissionFacBlType;
use App\Entity\dit\DemandeIntervention;
use App\Service\genererPdf\GeneratePdf;
use App\Service\fichier\TraitementDeFichier;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\da\DaSoumissionFacBlRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;
use DateTime;

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
    private GeneratePdf $generatePdf;
    private DemandeApproRepository $demandeApproRepository;
    private DitRepository $ditRepository;

    public function __construct()
    {
        parent::__construct();

        $this->generatePdf = new GeneratePdf();
        $this->daSoumissionFacBl = new DaSoumissionFacBl();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->historiqueOperation      = new HistoriqueOperationDaBcService($this->getEntityManager());
        $this->daSoumissionFacBlRepository = $this->getEntityManager()->getRepository(DaSoumissionFacBl::class);
        $this->demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/soumission-facbl/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl", defaults={"numOr"=0})
     */
    public function index(string $numCde, string $numDa, string $numOr, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $infosLivraison = $this->getInfoLivraison($numCde, $numDa);

        $this->daSoumissionFacBl->setNumeroCde($numCde);

        $form = $this->getFormFactory()->createBuilder(DaSoumissionFacBlType::class, $this->daSoumissionFacBl, [
            'method' => 'POST',
        ])->getForm();

        $this->traitementFormulaire($request, $form, $infosLivraison);

        return $this->render('da/soumissionFacBl.html.twig', [
            'form' => $form->createView(),
            'numCde' => $numCde,
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
    private function traitementFormulaire(Request $request, FormInterface $form, array $infosLivraison): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSoumissionFacBl $soumissionFacBl */
            $soumissionFacBl = $form->getData();
            $nomOriginalFichier = $soumissionFacBl->getPieceJoint1()->getClientOriginalName();
            if ($this->verifierConditionDeBlocage($soumissionFacBl, $numCde, $nomOriginalFichier)) {
                /** ENREGISTREMENT DE FICHIER */
                $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

                //numeroversion max
                $numeroVersionMax = $this->autoIncrement($this->daSoumissionFacBlRepository->getNumeroVersionMax($numCde));
                /** FUSION DES PDF */
                $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');
                $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);
                $nomPdfFusionner =  'FACBL' . $numCde . '#' . $numDa . '-' . $numOr . '_' . $numeroVersionMax . '~' . $nomOriginalFichier;
                $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;
                $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

                /** AJOUT DES INFO NECESSAIRE */
                $soumissionFacBl = $this->ajoutInfoNecesaireSoumissionFacBl($numCde, $numDa, $soumissionFacBl, $nomPdfFusionner, $numeroVersionMax, $numOr);

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

    private function ajoutInfoNecesaireSoumissionFacBl(string $numCde, string $numDa, DaSoumissionFacBl $soumissionFacBl, string $nomPdfFusionner, int $numeroVersionMax, string $numOr): DaSoumissionFacBl
    {
        $numDit = $this->demandeApproRepository->getNumDitDa($numDa);
        $soumissionFacBl->setNumeroCde($numCde)
            ->setUtilisateur($this->getUserName())
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

    private function conditionDeBlocage(DaSoumissionFacBl $soumissionFacBl): array
    {
        $nomDeFichier = $soumissionFacBl->getPieceJoint1()->getClientOriginalName();

        return [
            'nomDeFichier' => preg_match('/[#\-_~]/', $nomDeFichier), // contient au moins un des caractères
        ];
    }

    private function verifierConditionDeBlocage(DaSoumissionFacBl $soumissionFacBl, $numCde, $nomOriginalFichier): bool
    {
        $conditions = $this->conditionDeBlocage($soumissionFacBl);

        $okey = false;

        if ($conditions['nomDeFichier']) {
            $message = "Le nom de fichier ('{$nomOriginalFichier}') n'est pas valide. Il ne doit pas contenir les caractères suivants : #, -, _ ou ~. Merci de renommer votre fichier avant de le soumettre dans DocuWare.";

            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
            $okey = false;
        } else {
            $okey = true; // Aucune condition de blocage n'est remplie
        }

        return $okey;
    }
}
