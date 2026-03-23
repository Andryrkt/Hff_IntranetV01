<?php

namespace App\Controller\ddp;

use Exception;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\ddp\DemandePaiement;
use App\Form\ddp\DdpDossierRegulType;
use App\Model\ddp\DdpDossierRegulModel;
use App\Service\genererPdf\GeneratePdfDdr;
use App\Entity\admin\ddp\DocDemandePaiement;
use App\Service\fichier\FileUploaderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ddp\DemandePaiementRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\admin\ddp\DocDemandePaiementRepository;
use App\Service\historiqueOperation\HistoriqueOperationDDPService;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DdpDossierRegularisationController extends Controller
{
    private DdpDossierRegulModel $DdpDossierRegulModel;
    private DemandePaiementRepository $demandePaiementRepository;
    private DocDemandePaiementRepository $docRepository;
    private string $cheminDeBase;
    private HistoriqueOperationDDPService $historiqueOperation;
    private string $baseCheminDocuware;
    private GeneratePdfDdr $generatePdfDdr;

    public function __construct()
    {
        parent::__construct();
        $this->DdpDossierRegulModel = new DdpDossierRegulModel;
        $this->demandePaiementRepository  = $this->getEntityManager()->getRepository(DemandePaiement::class);
        $this->docRepository = $this->getEntityManager()->getRepository(DocDemandePaiement::class);
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $this->historiqueOperation = new HistoriqueOperationDDPService($this->getEntityManager());
        $this->baseCheminDocuware = $_ENV['BASE_PATH_DOCUWARE'] . '/';
        $this->generatePdfDdr = new GeneratePdfDdr();
    }
    /**
     * @Route("/dossierRegul/{numDdp}/{numVersion}", name="demande_regulation")
     */
    public function afficheForm(Request $request, $numDdp, $numVersion)
    {
        $form = $this->getFormFactory()->createBuilder(DdpDossierRegulType::class, null)->getForm();
        $form->handleRequest($request);
        $Ddp = $this->demandePaiementRepository->findOneBy(['numeroDdp' => $numDdp, 'numeroVersion' => $numVersion]);
        // dd($Ddp);

        if ($form->isSubmitted() && $form->isValid()) {
            $numDdr = $this->autoDecrementDDP('DDR'); // decrementation du numero DDP

            $this->modificationDernierIdApp($numDdr); // modification du dernière id dans la table applications
            $fileUploaderService = new FileUploaderService($this->cheminDeBase);

            /** Uplode le fichier */
            /** @var UploadedFile|null $file */
            $file = $form->get('pieceJoint01')->getData();
            if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                $fileName = $this->moveFichierUploder($file, $numDdp, $fileUploaderService);
                $this->modificationBdDoc($numDdp, $numDdr);
                $this->enregistrementUpload($numDdp, $fileName, $numDdr);
            } else {
                throw new \Exception('Le fichier n\'est pas valide');
            }
            /**GENERETE PDF (page de garde) */
            $nomPageDeGarde = $numDdr . '.pdf';
            $cheminEtNom = $this->cheminDeBase . '/' . $numDdp . '_Regul/' . $nomPageDeGarde;
            // $cheminEtNom = $this->cheminDeBase . '/' . $nomPageDeGarde;
            $this->generatePdfDdr->genererPDF($Ddp, $this->getUserMail(), $numDdr, $cheminEtNom);
            /** FUSIONER LES PDFS */
            $cheminDesFichierFinale = $this->recupCheminTousLesFichier($numDdp, $fileName, $cheminEtNom);
            $fichierConvertir = $this->ConvertirLesPdf($cheminDesFichierFinale);
            $cheminEtNomFichierFusioner = $this->cheminDeBase . '/' . $numDdp . '_Regul/' . $numDdr . '.pdf';
            $fileUploaderService->fusionFichers($fichierConvertir, $cheminEtNomFichierFusioner);

            /** Copie du fichier fusionner dans DW */
            $this->copyDocuware($cheminEtNomFichierFusioner, $numDdr);
            /** modifier le statut de regularisation */
            $this->modificationStatutDossierRegul($numDdp);
            /** enregistrer dans l'historique */
            $this->historiqueOperation->sendNotificationSoumission('Les documents de régularisation a été stockée avec succès', $numDdr, 'ddp_liste', true);
        }

        if (!$this->getSessionService()->has('page_loaded')) {

            /** creation du session */
            $this->getSessionService()->set('page_loaded', true);

            /** COPIER LES FICHIERS */
            $this->copierFichierDistant($numDdp);

            /** Enregistrement des noms de fichier dans la table document_demande_paiement */
            $this->EnregistrementBdDocDdp($numDdp, $numVersion);

            /** recupération de nom de fichier */
            $groupes = $this->recupInfoFichier($numDdp, $numVersion);
        }




        return $this->render('ddp/DdpDossierRegul.html.twig', [
            'form' => $form->createView(),
            'groupes' => $groupes
        ]);
    }

    private function copyDocuware(string $cheminEtNomFichierFusioner, string $numDdr)
    {
        $cheminDeFichier = $cheminEtNomFichierFusioner;
        $destinationFinal = $this->baseCheminDocuware . 'DEMANDE_DE_REGULARISATION/' . $numDdr . '.pdf';
        copy($cheminDeFichier, $destinationFinal);
    }

    private function modificationBdDoc(string $numDdp, string $numDdr): void
    {
        $docDdps = $this->docRepository->findBy(["numeroDdp" => $numDdp]);
        foreach ($docDdps as  $docDdp) {
            $docDdp->setNumDdr($numDdr);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * modification du dernier id de l'application dans la table application
     *
     * @param string $numDdr
     * @return void
     */
    private function modificationDernierIdApp(string $numDdr): void
    {
        $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DDR']);
        $application->setDerniereId($numDdr);
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();
    }
    /**
     * modification de statut_dossier_regul dans la table demande_paiement
     * 
     * @param string $nimDdp
     * @return void
     */
    private function modificationStatutDossierRegul($numDdp)
    {
        $demande_paiement = $this->getEntityManager()->getRepository(DemandePaiement::class)->findOneBy(['numeroDdp' => $numDdp]);
        $demande_paiement->setStatutDossierRegul('DOSSIER DE REGULARISTATION');
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        $this->getEntityManager()->persist($demande_paiement);
        $this->getEntityManager()->flush();
    }
    /**
     * Decrementation de Numero_Applications (DOMAnnéeMoisNuméro)
     *
     * @param string $nomDemande
     * @return string
     */
    protected function autoDecrementDDP(string $nomDemande): string
    {
        //NumDOM auto
        $YearsOfcours = date('y'); //24
        $MonthOfcours = date('m'); //01
        //$MonthOfcours = "08"; //01
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours; //2401
        //var_dump($AnneMoisOfcours);
        // dernier NumDOM dans la base

        //$Max_Num = $this->casier->RecupereNumCAS()['numCas'];


        if ($nomDemande === 'DDR') {
            $Max_Num = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DDR'])->getDerniereId();
        } else {
            $Max_Num = $nomDemande . $AnneMoisOfcours . '9999';
        }

        //var_dump($Max_Num);
        //$Max_Num = 'CAS24040000';
        //num_sequentielless
        $vNumSequential =  substr($Max_Num, -4); // lay 4chiffre msincrimente
        //dump($vNumSequential);
        $DateAnneemoisnum = substr($Max_Num, -8);
        //dump($DateAnneemoisnum);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        //dump($DateYearsMonthOfMax);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential =  $vNumSequential - 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 9999;
            }
        }

        //dump($vNumSequential);
        //var_dump($vNumSequential);
        $Result_Num = $nomDemande . $AnneMoisOfcours . $vNumSequential;
        //var_dump($Result_Num);
        //dd($Result_Num);
        return $Result_Num;
    }


    private function recupCheminTousLesFichier(string $numDdp, string $fileName, string $cheminEtNom): array
    {
        $chemins = $this->docRepository->getFileName($numDdp);
        $nomFichierAvecChemin = [];
        foreach ($chemins as  $chemin) {
            $nomFichierAvecChemin[] = $this->cheminDeBase . '/' . $numDdp . '_Regul/' . $chemin['nomDossier'] . '/' . $chemin['nomFichier'];
        }
        $nomFichierAvecCheminUploder = [$cheminEtNom, $this->cheminDeBase . '/' . $numDdp . '_Regul/' . $fileName];
        return array_merge($nomFichierAvecCheminUploder, $nomFichierAvecChemin);
    }

    private function enregistrementUpload(string $numDdp, string $fileName, string $numDdr)
    {
        $docDdp = new DocDemandePaiement();
        $docDdp->setNumeroDdp($numDdp)
            ->setNomFichier($fileName)
            ->setNomDossier(null)
            ->setNumDdr($numDdr)
        ;
        $this->getEntityManager()->persist($docDdp);
        $this->getEntityManager()->flush();
    }
    private function moveFichierUploder(UploadedFile $file, string $numDdp, FileUploaderService $fileUploaderService): string
    {
        $fileName = 'control_livraison_' . $numDdp . '.pdf';
        $pathFichier = '/' . $numDdp . '_Regul';
        $fileUploaderService->uploadFileSansName($file, $fileName, $pathFichier);
        return $fileName;
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

    private function recupInfoFichier(string $numDdp, string $numVersion): array
    {
        $documents = $this->docRepository->findBy(["numeroDdp" => $numDdp, "numeroVersion" => $numVersion]);

        $groupes = [];

        foreach ($documents as $doc) {
            $nomDossier = $doc->getNomDossier();

            if (!isset($groupes[$nomDossier])) {
                $groupes[$nomDossier] = [];
            }

            $groupes[$nomDossier][] = $doc;
        }
        return $groupes;
    }

    private function recupDonnerDocDdp(string $numDdp, string $numVersion): array
    {
        $docDddps = [];
        $cheminDeFichiers = $this->recupCheminFichierDistant15($numDdp);
        foreach ($cheminDeFichiers as $key => $cheminDeFichieres) {
            foreach ($cheminDeFichieres as $cheminDeFichier) {
                /** @var DocDemandePaiement */
                $docDdp = new DocDemandePaiement();
                $docDddps[] = $docDdp
                    ->setNumeroDdp($numDdp)
                    ->setNomFichier($this->nomFichier($cheminDeFichier))
                    ->setNomDossier($key)
                    ->setNumeroVersion($numVersion);
            }
        }
        return $docDddps;
    }

    private function EnregistrementBdDocDdp(string $numDdp, int $numVersion): void
    {
        $docDddps = $this->recupDonnerDocDdp($numDdp, $numVersion);

        foreach ($docDddps as $docDddp) {
            $this->getEntityManager()->persist($docDddp);
        }

        $this->getEntityManager()->flush();
    }

    /** 
     * recuperation code fournisseur et commande
     * */
    public function recupCheminFichierDistant15(string $numDdp): array
    {
        $FrsCde = $this->demandePaiementRepository->recuperation_numFrs_numCde($numDdp);
        $list = $this->DdpDossierRegulModel->getListeGcot($FrsCde);
        $cheminFichier = [];
        $index = 1;
        foreach ($list as  $value) {
            $cheminFichier[$value . '_' . $index] = $this->DdpDossierRegulModel->getListeDoc($value);
            $index++;
        }

        return $cheminFichier;
    }

    /**
     * Copie des fichiers dans un serveur '192.168.0.15' dans le repertoire uplode/ddp/
     *
     * @param string $numDdp
     * @return void
     */
    private function copierFichierDistant(string $numDdp): void
    {
        //chemin des fichier à copier
        $cheminDeFichiers = $this->recupCheminFichierDistant15($numDdp);
        // repertoire des fichier à coller
        $cheminDestination = $this->cheminDeBase . '/' . $numDdp . '_Regul';

        foreach ($cheminDeFichiers as $key => $cheminDeFichieres) {
            $sousDossier = $cheminDestination  . '/' . $key;

            // Créer le dossier s'il n'existe pas
            if (!is_dir($sousDossier)) {
                mkdir($sousDossier, 0777, true);
            }

            foreach ($cheminDeFichieres as $cheminDeFichier) {
                $nomFichier = $this->nomFichier($cheminDeFichier);
                $destinationFinal = $sousDossier . '/' . $nomFichier;
                copy($cheminDeFichier, $destinationFinal);
            }
        }
    }




    private function nomFichier(string $cheminFichier): string
    {
        $motExacteASupprimer = [
            '\\\\192.168.0.15',
            '\\GCOT_DATA',
            '\\TRANSIT',
        ];

        $motCommenceASupprimer = ['\\DD'];

        return $this->enleverPartiesTexte($cheminFichier, $motExacteASupprimer, $motCommenceASupprimer);
    }

    private function enleverPartiesTexte(string $texte, array $motsExacts, array $motsCommencent): string
    {
        // Supprimer les correspondances exactes
        foreach ($motsExacts as $mot) {
            $texte = str_replace($mot, '', $texte);
        }

        // Supprimer les parties qui commencent par un mot donné
        foreach ($motsCommencent as $motDebut) {
            $pattern = '/' . preg_quote($motDebut, '/') . '[^\\\\]*/';
            $texte = preg_replace($pattern, '', $texte);
        }

        // Supprimer les éventuels slashes de début
        return ltrim($texte, '\\/');
    }
}
