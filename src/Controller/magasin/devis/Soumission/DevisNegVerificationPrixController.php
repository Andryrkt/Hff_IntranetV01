<?php

namespace App\Controller\magasin\devis\Soumission;

use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\Magasin\Devis\Soumission\SoumissionDto;
use App\Factory\magasin\devis\Soumission\VerificationPrixFactory;
use App\Form\magasin\devis\Soumission\VerificationPrixType;
use App\Model\magasin\devis\Soumission\SoumissionModel;
use App\Service\EmailService;
use App\Service\fichier\TraitementDeFichier;
use App\Service\fichier\UploderFileService;
use App\Service\genererPdf\magasin\devis\GeneratePdfDeviMagasinVp;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Fichier\DevisMagasinGenererNameFileService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisNegVerificationPrixController extends Controller
{
    use PdfConversionTrait;

    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;
    private string $cheminBaseUpload;
    private string $cheminCourtUpload;
    private DevisMagasinGenererNameFileService $nameGenerator;
    private UploderFileService $uploader;

    public function __construct()
    {
        parent::__construct();
        global $container;
        $this->historiqueOperationDeviMagasinService = $container->get(HistoriqueOperationDevisMagasinService::class);
        $this->cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/magasin/devis/';
        $this->cheminCourtUpload = $_ENV['BASE_PATH_FICHIER_COURT'] . '/magasin/devis/';
        $this->nameGenerator = new DevisMagasinGenererNameFileService();
        $this->uploader = new UploderFileService($this->cheminBaseUpload, $this->nameGenerator);
    }

    /**
     * @Route("/soumission-devis-neg-verification-de-prix/{typeSoumission}/{numeroDevis}", name="devis_neg_soumission_verification_prix", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $typeSoumission = null, ?string $numeroDevis = null, Request $request)
    {
        $dto = VerificationPrixFactory::create($typeSoumission, $numeroDevis, $this->getSecurityService());

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(VerificationPrixType::class, $dto, [
            "fichier_initialise" => (bool)$dto->remoteUrlCourt, // Indique si un fichier existe déjà pour ce devis
        ])->getForm();

        // traitement du formulaire
        $this->traitementFormulaire($form, $request);

        return $this->render('magasin/devis/soumission/verification_prix.html.twig', [
            'form'        => $form->createView(),
            'remoteUrl'   => $dto->remoteUrlCourt,
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            $dto = VerificationPrixFactory::CreateBeforeSoumission($dto, $this->getUserName(), $this->getUserMail());

            /** 
             * Enregistrement de fichier excel
             * @var array $nomEtCheminFichiersEnregistrerExcel 
             * @var string $nomAvecCheminFichierExcel
             * @var string $nomFichierExcel
             */
            [$nomEtCheminFichiersEnregistrerExcel, $nomAvecCheminFichierExcel, $nomFichierExcel] = $this->enregistrementFichierExcel($form, $dto->numeroDevis);

            /** 
             * Enregistrement de fichier uploder
             * @var array $nomEtCheminFichiersEnregistrer 
             * @var string $nomAvecCheminFichier
             * @var string $nomFichier
             */
            [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $dto->numeroDevis, $dto->numeroVersion, $dto->suffix, explode('@', $dto->userMail)[0], $dto->remoteUrlCourt);

            // creation du page de garde 
            $generatePdfDevisMagasin = new GeneratePdfDeviMagasinVp();
            $generatePdfDevisMagasin->genererPdf($dto, $nomAvecCheminFichier);
            //insertion de la page de garde à la position 0
            $traitementDeFichier = new TraitementDeFichier();
            $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, 0);
            /** @var array fusions des fichiers */
            $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
            $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);

            // Enregistrement de la soumission en base de données
            $soumissionModel = new SoumissionModel();
            $soumissionModel->enregistrerSoumission($dto, $nomFichier, $nomFichierExcel);

            //envoie du fichier dans DW
            $generatePdfDevisMagasin->copyToDWDevisVpMagasin($nomFichier, $dto->numeroDevis);

            //envoie de mail au PM
            if (!empty($nomAvecCheminFichierExcel) && !empty($nomFichierExcel) && $this->estValidationPm($dto)) {
                $this->envoyerMailDevisMagasin($dto->numeroDevis, [
                    'filePath' => $nomAvecCheminFichierExcel,
                    'fileName' => $nomFichierExcel,
                ]);
            }

            //HISTORISATION DE L'OPERATION
            $message = "la vérification de prix du devis numero : " . $dto->numeroDevis . " a été envoyée avec succès .";
            $criteria = (array) ($this->getSessionService()->get('criteria_for_excel_liste_devis_neg') ?? []);
            $nomDeRoute = 'liste_devis_neg'; // route de redirection après soumission
            $nomInputSearch = 'devis_neg_search'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $dto->numeroDevis, $nomDeRoute, true, $criteria, $nomInputSearch);
        }
    }

    private function enregistrementFichierExcel(FormInterface $form, string $numDevis): array
    {
        $devisPath = $_ENV['BASE_PATH_FICHIER'] . '/magasin/devis/' . $numDevis . '/';
        if (!is_dir($devisPath)) mkdir($devisPath, 0777, true);

        $nomEtCheminFichiersEnregistrer = $this->uploader->getNomsEtCheminFichiers($form, [
            'pattern' => '/^pieceJointExcel$/i',
            'repertoire' => $devisPath,
            'generer_nom_callback' => function (UploadedFile $file) use ($numDevis) {
                return $this->nameGenerator->generateFichierExcelName($numDevis, $file->getClientOriginalExtension());
            }
        ]);

        $nomAvecCheminFichier = $nomEtCheminFichiersEnregistrer[0] ?? '';
        $nomFichier = $nomAvecCheminFichier ? basename($nomAvecCheminFichier) : "";

        return [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }

    private function enregistrementFichier(FormInterface $form, string $numDevis, int $numeroVersion, string $suffix, string $mail, string $remoteUrl = ""): array
    {
        $devisPath = $_ENV['BASE_PATH_FICHIER'] . '/magasin/devis/' . $numDevis . '/';
        if (!is_dir($devisPath)) mkdir($devisPath, 0777, true);

        $nomEtCheminFichiersEnregistrer = $remoteUrl ? [$remoteUrl] : $this->uploader->getNomsEtCheminFichiers($form, [
            'repertoire' => $devisPath,
            'generer_nom_callback' => function (
                UploadedFile $file,
                int $index
            ) use ($numDevis, $numeroVersion, $suffix, $mail) {
                return $this->nameGenerator->generateVerificationPrixName($file, $numDevis, $numeroVersion, $suffix, $mail, $index);
            }
        ]);

        $nomAvecCheminFichier = $this->nameGenerator->getCheminEtNomDeFichierSansIndex($nomEtCheminFichiersEnregistrer[0]);
        $nomFichier = $this->nameGenerator->getNomFichier($nomAvecCheminFichier);

        return [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }

    private function estValidationPm($dto): bool
    {
        if ($dto->constructeur === 'TOUS NEST PAS CAT') {
            return true;
        } elseif ($dto->constructeur === 'TOUT CAT' && $dto->validationPm === true) {
            return true;
        }
        return false;
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
