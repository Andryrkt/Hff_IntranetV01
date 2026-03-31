<?php

namespace App\Controller\magasin\devis\Soumission;

use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\Magasin\Devis\Soumission\BcDto;
use App\Factory\magasin\devis\Soumission\BcFactory;
use App\Form\magasin\devis\Soumission\BcType;
use App\Model\magasin\devis\Soumission\BcModel;
use App\Service\fichier\TraitementDeFichier;
use App\Service\fichier\UploderFileService;
use App\Service\genererPdf\magasin\devis\GeneratePdfBcNeg;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Fichier\DevisMagasinGenererNameFileService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisNegBcController extends Controller
{
    use PdfConversionTrait;

    private BcFactory $bcFactory;
    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;

    public function __construct()
    {
        parent::__construct();
        global $container;
        $this->historiqueOperationDeviMagasinService = $container->get(HistoriqueOperationDevisMagasinService::class);
        $this->bcFactory = new BcFactory();
    }

    /**
     * @Route("/soumission-bc-neg/{numeroDevis}", name="bc_neg_soumission", defaults={"numeroDevis"=null})
     */
    public function index($numeroDevis, Request $request)
    {
        $codeSociette = $this->getSecurityService()->getCodeSocieteUser();

        $bcDto = $this->bcFactory->create($numeroDevis, $codeSociette);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(BcType::class, $bcDto, [
            'method' => 'POST',
        ])->getForm();

        $this->tratitementFormulaire($form, $request);

        //affichage du formulaire
        return $this->render('magasin/devis/soumission/bc.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function tratitementFormulaire($form, Request $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            $dto = $this->bcFactory->createApresSoumission($dto, $this->getUserName(), $this->getUserMail());

            //traitemnet des fichiers
            $this->traitementDesFichiers($form, $dto);

            // Enregistrement de la soumission en base de données
            $bcModel = new BcModel();
            $bcModel->enregistrerBc($dto);

            // Modification du table devis_soumis_a_validation_neg
            // $bcModel->updateDevis($dto);

            //HISTORISATION DE L'OPERATION
            $message = 'Le bon de commande a été soumis avec succès.';
            $criteria = (array) ($this->getSessionService()->get('criteria_for_excel_liste_devis_neg') ?? []);
            $nomDeRoute = 'liste_devis_neg'; // route de redirection après soumission
            $nomInputSearch = 'devis_neg_search'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $dto->numeroDevis, $nomDeRoute, true, $criteria, $nomInputSearch);
        }
    }

    private function traitementDesFichiers(FormInterface $form, BcDto $dto): void
    {
        /** 
         * 1. gestion des pieces jointes et generer le nom du fichier PDF
         * Enregistrement de fichier uploder
         * @var array $nomEtCheminFichiersEnregistrer
         * @var array $nomFichierEnregistrer 
         * @var string $nomAvecCheminFichier (page de garde)
         * @var string $nomFichier (page de garde)
         */
        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $dto->numeroDevis, $dto->numeroVersion);

        // 2. creation de page de garde
        $generatePdf = new GeneratePdfBcNeg();
        // 2.1 recupération des information utile pour le page de garde et ajout dans le devis magasin
        $generatePdf->generer($dto, $nomAvecCheminFichier);

        // 3. ajout du page de garde à la dernière position
        $traitementDeFichier = new TraitementDeFichier();
        $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, count($nomEtCheminFichiersEnregistrer));

        // 4. fusion du page de garde et des pieces jointes (conversion avant la fusion)
        $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
        $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);

        // 5. copie du pdf fusioné dans DW
        $generatePdf->copyToDWBcMagasin($nomFichier, $dto->numeroDevis);
    }

    private function enregistrementFichier(FormInterface $form, string $numDevis, int $numeroVersion): array
    {
        $nameGenerator = new DevisMagasinGenererNameFileService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/magasin/devis/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $devisPath = $cheminBaseUpload . $numDevis . '/';
        if (!is_dir($devisPath)) {
            mkdir($devisPath, 0777, true);
        }

        /**
         * recupère les noms + chemins dans un tableau et les noms dans une autre
         * @var array $nomEtCheminFichiersEnregistrer
         * @var array $nomFichierEnregistrer
         */
        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer] = $uploader->getFichiers($form, [
            'repertoire' => $devisPath,
            'generer_nom_callback' => function (
                UploadedFile $file,
                int $index
            ) use ($numDevis, $numeroVersion, $nameGenerator) {
                return $nameGenerator->generateBonCommandeName($file, $numDevis, $numeroVersion, $index);
            }
        ]);


        $nomFichier = $nameGenerator->generatePageGardeBonCommandeName($numDevis, $numeroVersion);
        $nomAvecCheminFichier = $devisPath . $nomFichier;

        return [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }
}
