<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Service\autres\VersionService;
use Symfony\Component\Form\FormInterface;
use App\Entity\magasin\devis\DevisMagasin;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\devis\DevisMagasinType;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Controller\Traits\magasin\devis\DevisMagasinTrait;
use App\Service\genererPdf\magasin\devis\GeneratePdfDeviMagasinVp;
use App\Service\magasin\devis\Fichier\DevisMagasinGenererNameFileService;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Validator\DevisMagasinValidationOrchestrator;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinValidationDevisController extends Controller
{
    use AutorisationTrait;
    use DevisMagasinTrait;
    use PdfConversionTrait;


    private const TYPE_SOUMISSION_VALIDATION_DEVIS = 'VD';
    private const MESSAGE_DE_CONFIRMATION = 'validation';


    private ListeDevisMagasinModel $listeDevisMagasinModel;
    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;
    private string $cheminBaseUpload;
    private DevisMagasinRepository $devisMagasinRepository;
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
        $this->devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);
        $this->nameGenerator = new DevisMagasinGenererNameFileService();
        $this->uploader = new UploderFileService($this->cheminBaseUpload, $this->nameGenerator);
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->generatePdfDevisMagasin = new GeneratePdfDeviMagasinVp();
    }

    /**
     * @Route("/soumission-devis-magasin-validation-devis/{numeroDevis}", name="devis_magasin_soumission_validation_devis", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $numeroDevis = null, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        //recupération des informations utile dans IPS
        $firstDevisIps = $this->getInfoDevisIps($numeroDevis);
        [$newSumOfLines, $newSumOfMontant] = $this->newSumOfLinesAndAmount($firstDevisIps);

        //instanciation de l'orchestrateur de validation
        $orchestrator = new DevisMagasinValidationOrchestrator($numeroDevis);
        // Validation avant soumission - utilise la nouvelle méthode qui retourne un booléen
        $orchestrator->validateBeforeSubmission($this->devisMagasinRepository, $this->listeDevisMagasinModel, $numeroDevis, $newSumOfLines, $newSumOfMontant);



        //instancier le devis magasin
        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(DevisMagasinType::class, $devisMagasin)->getForm();

        //traitement du formulaire
        $this->traitementFormulaire($form, $request, $devisMagasin, $orchestrator, $firstDevisIps);

        //affichage du formulaire
        return $this->render('magasin/devis/soumission.html.twig', [
            'form' => $form->createView(),
            'message' => self::MESSAGE_DE_CONFIRMATION,
            'numeroDevis' => $devisMagasin->getNumeroDevis()
        ]);
    }

    private function traitementFormulaire($form, Request $request, DevisMagasin $devisMagasin, DevisMagasinValidationOrchestrator $orchestrator, array $firstDevisIps)
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
            $numeroVersion = $this->devisMagasinRepository->getNumeroVersionMax($devisMagasin->getNumeroDevis());

            // TODO: creation de pdf (à specifier par Antsa)

            /** 
             * Enregistrement de fichier uploder
             * @var array $nomEtCheminFichiersEnregistrer 
             * @var string $nomAvecCheminFichier
             * @var string $nomFichier
             */
            [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $devisMagasin->getNumeroDevis(), VersionService::autoIncrement($numeroVersion), $suffixConstructeur, explode('@', $this->getUserMail())[0], self::TYPE_SOUMISSION_VALIDATION_DEVIS);

            /** @var array fusions des fichiers */
            $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
            $this->traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);


            //ajout des informations de IPS et des informations manuelles comme nombre de lignes, cat, nonCat dans le devis magasin
            $this->ajoutInfoIpsDansDevisMagasin($devisMagasin, $firstDevisIps, $numeroVersion, $nomFichier, self::TYPE_SOUMISSION_VALIDATION_DEVIS);

            //enregistrement du devis magasin
            $this->getEntityManager()->persist($devisMagasin);
            $this->getEntityManager()->flush();

            //envoie du fichier dans DW
            $this->generatePdfDevisMagasin->copyToDWDevisMagasin($nomFichier, $devisMagasin->getNumeroDevis());


            //HISTORISATION DE L'OPERATION
            $message = "la validation du devis numero : " . $devisMagasin->getNumeroDevis() . " a été envoyée avec succès .";
            $criteria = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin');
            $nomDeRoute = 'devis_magasin_liste'; // route de redirection après soumission
            $nomInputSearch = 'devis_magasin_search'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $devisMagasin->getNumeroDevis(), $nomDeRoute, true, $criteria, $nomInputSearch);
        }
    }
}
