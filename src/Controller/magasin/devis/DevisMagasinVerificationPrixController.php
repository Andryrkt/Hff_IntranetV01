<?php

namespace App\Controller\magasin\devis;

use DirectoryIterator;
use App\Service\EmailService;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Service\autres\VersionService;
use App\Entity\magasin\devis\DevisMagasin;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\devis\DevisMagasinType;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Controller\Traits\magasin\devis\DevisMagasinTrait;
use App\Service\genererPdf\magasin\devis\GeneratePdfDeviMagasinVp;
use App\Service\magasin\devis\Fichier\DevisMagasinGenererNameFileService;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Validator\DevisMagasinValidationVpOrchestrator;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinVerificationPrixController extends Controller
{
    use AutorisationTrait;
    use DevisMagasinTrait;
    use PdfConversionTrait;

    private const TYPE_SOUMISSION_VERIFICATION_PRIX = 'VP';
    private const MESSAGE_DE_CONFIRMATION = 'verification prix';


    private ListeDevisMagasinModel $listeDevisMagasinModel;
    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;
    private string $cheminBaseUpload;
    private string $cheminCourtUpload;
    private DevisMagasinGenererNameFileService $nameGenerator;
    private UploderFileService $uploader;
    private TraitementDeFichier $traitementDeFichier;
    private GeneratePdfDeviMagasinVp $generatePdfDevisMagasin;

    public function __construct()
    {
        parent::__construct();
        global $container;
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();
        $this->historiqueOperationDeviMagasinService = $container->get(HistoriqueOperationDevisMagasinService::class);
        $this->cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/magasin/devis/';
        $this->cheminCourtUpload = $_ENV['BASE_PATH_FICHIER_COURT'] . '/magasin/devis/';
        $this->generatePdfDevisMagasin = new GeneratePdfDeviMagasinVp();
        $this->nameGenerator = new DevisMagasinGenererNameFileService();
        $this->uploader = new UploderFileService($this->cheminBaseUpload, $this->nameGenerator);
        $this->traitementDeFichier = new TraitementDeFichier();
    }

    /**
     * @Route("/soumission-devis-magasin-verification-de-prix/{numeroDevis}", name="devis_magasin_soumission_verification_prix", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $numeroDevis = null, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        $remoteUrl = $this->getLastEditedDevis($numeroDevis);

        // Instantiation et validation de la présence du numéro de devis
        $orchestrator = new DevisMagasinValidationVpOrchestrator($numeroDevis ?? '', $remoteUrl["court"]);

        //recupération des informations utile dans IPS
        $firstDevisIps = $this->getInfoDevisIps($numeroDevis);
        [$newSumOfLines, $newSumOfMontant] = $this->newSumOfLinesAndAmount($firstDevisIps);

        /** @var DevisMagasinRepository */
        $devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);

        // Validation avant soumission - utilise la nouvelle méthode qui retourne un booléen
        $data = [
            'devisRepository' => $devisMagasinRepository,
            'numeroDevis' => $numeroDevis,
            'newSumOfLines' => $newSumOfLines,
            'newSumOfMontant' => $newSumOfMontant
        ];
        $orchestrator->validateBeforeVpSubmission($data);

        //instancier le devis magasin
        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);
        $devisMagasin->constructeur = trim($this->listeDevisMagasinModel->getConstructeur($numeroDevis));
        $devisMagasin->setTypeSoumission('VP');

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(DevisMagasinType::class, $devisMagasin, [
            "fichier_initialise" => (bool)$remoteUrl["court"]
        ])->getForm();

        //traitement du formualire
        $this->traitementFormualire($form, $request,  $devisMagasin, $firstDevisIps, $orchestrator, $devisMagasinRepository, $remoteUrl["long"]);

        //affichage du formulaire
        return $this->render('magasin/devis/soumission.html.twig', [
            'form'        => $form->createView(),
            'message'     => self::MESSAGE_DE_CONFIRMATION,
            'numeroDevis' => $numeroDevis,
            'remoteUrl'   => $remoteUrl["court"],
        ]);
    }

    private function traitementFormualire($form, Request $request,  DevisMagasin $devisMagasin, array $firstDevisIps, DevisMagasinValidationVpOrchestrator $orchestrator, DevisMagasinRepository $devisMagasinRepository, $remoteUrl)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Validation du fichier soumis via le service dédié
            if (!$orchestrator->validateSubmittedFile($form)) {
                return; // Arrête le traitement si la validation échoue
            }

            /** @var string recuperation des suffix selon le constructeur magasin */
            $suffixConstructeur = $this->listeDevisMagasinModel->constructeurPieceMagasin($devisMagasin->getNumeroDevis());

            /** @var int recupération de numero version max */
            $numeroVersion = $devisMagasinRepository->getNumeroVersionMax($devisMagasin->getNumeroDevis());

            if ($devisMagasin->constructeur === 'TOUS NEST PAS CAT')  $devisMagasin->setEstValidationPm(true);

            /** 
             * Enregistrement de fichier excel
             * @var array $nomEtCheminFichiersEnregistrerExcel 
             * @var string $nomAvecCheminFichierExcel
             * @var string $nomFichierExcel
             */
            [$nomEtCheminFichiersEnregistrerExcel, $nomAvecCheminFichierExcel, $nomFichierExcel] = $this->enregistrementFichierExcel($form, $devisMagasin->getNumeroDevis());

            /** 
             * Enregistrement de fichier uploder
             * @var array $nomEtCheminFichiersEnregistrer 
             * @var string $nomAvecCheminFichier
             * @var string $nomFichier
             */
            [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $devisMagasin->getNumeroDevis(), VersionService::autoIncrement($numeroVersion), $suffixConstructeur, explode('@', $this->getUserMail())[0], self::TYPE_SOUMISSION_VERIFICATION_PRIX, $remoteUrl);

            // creation du page de garde 
            $this->generatePdfDevisMagasin->genererPdf($this->getUser(), $devisMagasin, $nomAvecCheminFichier);
            //insertion de la page de garde à la position 0
            $nomEtCheminFichiersEnregistrer = $this->traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, 0);
            /** @var array fusions des fichiers */
            $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
            $this->traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);


            //ajout des informations de IPS et des informations manuelles comme nombre de lignes, cat, nonCat dans le devis magasin
            $this->ajoutInfoIpsDansDevisMagasin($devisMagasin, $firstDevisIps, $numeroVersion, $nomFichier, self::TYPE_SOUMISSION_VERIFICATION_PRIX, $nomFichierExcel);

            //enregistrement du devis magasin
            $this->getEntityManager()->persist($devisMagasin);
            $this->getEntityManager()->flush();

            //envoie du fichier dans DW
            $this->generatePdfDevisMagasin->copyToDWDevisVpMagasin($nomFichier, $devisMagasin->getNumeroDevis());

            //envoie de mail au PM
            if (!empty($nomAvecCheminFichierExcel) && !empty($nomFichierExcel) && $this->estValidationPm($devisMagasin)) {
                $this->envoyerMailDevisMagasin($devisMagasin->getNumeroDevis(), [
                    'filePath' => $nomAvecCheminFichierExcel,
                    'fileName' => $nomFichierExcel,
                ]);
            }

            //HISTORISATION DE L'OPERATION
            $message = "la vérification de prix du devis numero : " . $devisMagasin->getNumeroDevis() . " a été envoyée avec succès .";
            $criteria = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin');
            $nomDeRoute = 'devis_magasin_liste'; // route de redirection après soumission
            $nomInputSearch = 'devis_magasin_search'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $devisMagasin->getNumeroDevis(), $nomDeRoute, true, $criteria, $nomInputSearch);
        }
    }


    private function getLastEditedDevis(string $numeroDevis): array
    {
        $filePath = '';
        $destination = '';
        $dossier = "\\\\192.168.0.15\\hff_pdf\\VALIDATION VENTE NEGOCE\\";   // dossier contenant les fichiers
        $dernierFichier = null;
        $derniereDate = 0;

        $it = new DirectoryIterator($dossier);

        foreach ($it as $fichier) {
            if ($fichier->isFile()) {
                $nom = $fichier->getFilename();

                if (preg_match('/DEVIS MAGASIN_' . $numeroDevis . '_(\d{14})_\d+\.pdf$/', $nom, $matches)) {
                    $timestamp = $matches[1];

                    if ($timestamp > $derniereDate) {
                        $derniereDate = $timestamp;
                        $dernierFichier = $nom;
                    }
                }
            }
        }

        // Copier le fichier en local si existant
        if ($dernierFichier) {
            $remoteUrl = $dossier . $dernierFichier; // chemin du fichier dans le dossier partagé 192.168.0.15
            $devisPath = $this->cheminBaseUpload . $numeroDevis . '/'; // chemin complet du dossier local
            $destination = $devisPath . $dernierFichier; // chemin complet du fichier local
            if (!is_dir($devisPath)) mkdir($devisPath, 0777, true); // creation du dossier local si n'existe pas
            if (!file_exists($destination)) copy($remoteUrl, $destination); // copie du fichier local si n'existe pas
            $filePath = $this->cheminCourtUpload . "$numeroDevis/$dernierFichier"; // chemin court du fichier local
        }

        return [
            "court" => $filePath,
            "long"  => $destination
        ];
    }

    /** 
     * Méthode pour envoyer une email pour le PM
     * @param string $numDevis
     * @param array $resultatExport - nom et chemin du fichier excel
     */
    public function envoyerMailDevisMagasin(string $numDevis,  array $resultatExport)
    {
        $this->envoyerEmail([
            'to'          => $_ENV['MAIL_TO_NEG'],
            'variables'   => ['numDevis' => $numDevis],
            'attachments' => [
                $resultatExport['filePath'] => $resultatExport['fileName'],
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer un email
     */
    public function envoyerEmail(array $content): void
    {
        $emailTemplate = "magasin/devis/email/emailDevis.html.twig";

        $emailService = new EmailService($this->getTwig());

        $emailService->getMailer()->setFrom($_ENV['MAIL_FROM_ADDRESS'], 'noreply.neg');

        $emailService->sendEmail($content['to'], $content['cc'] ?? [], $emailTemplate, $content['variables'] ?? [], $content['attachments'] ?? []);
    }
}
