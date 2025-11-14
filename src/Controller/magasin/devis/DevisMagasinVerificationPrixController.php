<?php

namespace App\Controller\magasin\devis;

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

        // Instantiation et validation de la présence du numéro de devis
        $orchestrator = new DevisMagasinValidationVpOrchestrator($this->historiqueOperationDeviMagasinService, $numeroDevis ?? '');

        //recupération des informations utile dans IPS
        $firstDevisIps = $this->getInfoDevisIps($numeroDevis);
        [$newSumOfLines, $newSumOfMontant] = $this->newSumOfLinesAndAmount($firstDevisIps);

        /** @var DevisMagasinRepository */
        $devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);

        // Validation avant soumission - utilise la nouvelle méthode qui retourne un booléen
        $orchestrator->validateBeforeVpSubmission($devisMagasinRepository, $numeroDevis, $newSumOfLines, $newSumOfMontant);


        //instancier le devis magasin
        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);
        $devisMagasin->constructeur = trim($this->listeDevisMagasinModel->getConstructeur($numeroDevis));

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(DevisMagasinType::class, $devisMagasin)->getForm();

        //traitement du formualire
        $this->traitementFormualire($form, $request,  $devisMagasin, $firstDevisIps, $orchestrator, $devisMagasinRepository);

        //affichage du formulaire
        return $this->render('magasin/devis/soumission.html.twig', [
            'form' => $form->createView(),
            'message' => self::MESSAGE_DE_CONFIRMATION,
            'numeroDevis' => $devisMagasin->getNumeroDevis()
        ]);
    }

    private function traitementFormualire($form, Request $request,  DevisMagasin $devisMagasin, array $firstDevisIps, DevisMagasinValidationVpOrchestrator $orchestrator, DevisMagasinRepository $devisMagasinRepository)
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
             * Enregistrement de fichier uploder
             * @var array $nomEtCheminFichiersEnregistrer 
             * @var string $nomAvecCheminFichier
             * @var string $nomFichier
             */
            [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $devisMagasin->getNumeroDevis(), VersionService::autoIncrement($numeroVersion), $suffixConstructeur, explode('@', $this->getUserMail())[0], self::TYPE_SOUMISSION_VERIFICATION_PRIX);

            // creation de pdf 
            $this->generatePdfDevisMagasin->genererPdf($this->getUser(), $devisMagasin, $nomAvecCheminFichier);
            //insertion de la page de garde à la position 0
            $nomEtCheminFichiersEnregistrer = $this->traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, 0);
            /** @var array fusions des fichiers */
            $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
            $this->traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);

            //ajout des informations de IPS et des informations manuelles comme nombre de lignes, cat, nonCat dans le devis magasin
            $this->ajoutInfoIpsDansDevisMagasin($devisMagasin, $firstDevisIps, $numeroVersion, $nomFichier, self::TYPE_SOUMISSION_VERIFICATION_PRIX);

            //enregistrement du devis magasin
            $this->getEntityManager()->persist($devisMagasin);
            $this->getEntityManager()->flush();

            //envoie du fichier dans DW
            $this->generatePdfDevisMagasin->copyToDWDevisMagasin($nomFichier, $devisMagasin->getNumeroDevis());


            //HISTORISATION DE L'OPERATION
            $message = "la vérification de prix du devis numero : " . $devisMagasin->getNumeroDevis() . " a été envoyée avec succès .";
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $devisMagasin->getNumeroDevis(), 'devis_magasin_liste', true);
        }
    }
}
