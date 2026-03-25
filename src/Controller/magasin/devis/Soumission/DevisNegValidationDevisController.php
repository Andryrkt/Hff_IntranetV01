<?php

namespace App\Controller\magasin\devis\Soumission;

use App\Controller\Controller;
use App\Factory\magasin\devis\Soumission\ValidationDevisFactory;
use App\Form\magasin\devis\Soumission\ValidationDevisType;
use App\Model\magasin\devis\Soumission\SoumissionModel;
use App\Service\fichier\UploderFileService;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Fichier\DevisMagasinGenererNameFileService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisNegValidationDevisController extends Controller
{
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
     * @Route("/soumission-devis-neg-validation-devis/{typeSoumission}/{numeroDevis}", name="devis_neg_soumission_validation_devis", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $typeSoumission = null, ?string $numeroDevis = null, Request $request)
    {
        $codeSociette = $this->getSecurityService()->getCodeSocieteUser();

        // Création du DTO à partir des paramètres de la requête
        $dto = ValidationDevisFactory::create($typeSoumission, $numeroDevis, $codeSociette);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(ValidationDevisType::class, $dto, [
            "fichier_initialise" => (bool)$dto->remoteUrlCourt, // Indique si un fichier existe déjà pour ce devis
        ])->getForm();

        // traitement du formulaire
        $this->traitementFormulaire($form, $request);

        return $this->render('magasin/devis/soumission/validation_devis.html.twig', [
            'form'        => $form->createView(),
            'remoteUrl'   => $dto->remoteUrlCourt,
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            $dto = ValidationDevisFactory::CreateBeforeSoumission($dto, $this->getUserName(), $this->getUserMail());

            /** 
             * Enregistrement de fichier uploder
             * @var array $nomEtCheminFichiersEnregistrer 
             * @var string $nomAvecCheminFichier
             * @var string $nomFichier
             */
            [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $dto->numeroDevis, $dto->numeroVersion, $dto->suffix, explode('@', $dto->userMail)[0], $dto->remoteUrlCourt);


            // Enregistrement de la soumission en base de données
            $soumissionModel = new SoumissionModel();
            $soumissionModel->enregistrerSoumissionValidationDevis($dto, $nomFichier);


            //HISTORISATION DE L'OPERATION
            $message = "la validation du devis numero : " . $dto->numeroDevis . " a été envoyée avec succès .";
            $criteria = (array) ($this->getSessionService()->get('criteria_for_excel_liste_devis_neg') ?? []);
            $nomDeRoute = 'liste_devis_neg'; // route de redirection après soumission
            $nomInputSearch = 'devis_neg_search'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $dto->numeroDevis, $nomDeRoute, true, $criteria, $nomInputSearch);
        }
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
                return $this->nameGenerator->generateValidationDevisName($file, $numDevis, $numeroVersion, $suffix, $mail, $index);
            }
        ]);

        $nomAvecCheminFichier = $this->nameGenerator->getCheminEtNomDeFichierSansIndex($nomEtCheminFichiersEnregistrer[0]);
        $nomFichier = $this->nameGenerator->getNomFichier($nomAvecCheminFichier);

        return [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }
}
