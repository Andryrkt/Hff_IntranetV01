<?php

namespace App\Controller\magasin\bc;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use App\Form\magasin\bc\BcMagasinType;
use App\Model\magasin\bc\BcMagasinDto;
use App\Service\autres\VersionService;
use App\Model\magasin\bc\BcMagasinModel;
use Symfony\Component\Form\FormInterface;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Factory\magasin\bc\BcMagasinFactory;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Factory\magasin\bc\BcMagasinDtoFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\genererPdf\magasin\bc\GeneratePdfBcMagasin;
use App\Service\historiqueOperation\magasin\bc\HistoriqueOperationBcMagasinService;
use App\Service\magasin\devis\Fichier\DevisMagasinGenererNameFileService;

/**
 * @Route("/magasin/dematerialisation")
 */
class BcMagasinController extends Controller
{
    use AutorisationTrait;
    use PdfConversionTrait;

    private string $cheminBaseUpload;
    private HistoriqueOperationBcMagasinService $historiqueOperationBcMagasinService;

    public function __construct()
    {
        parent::__construct();
        global $container;
        $this->cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . 'magasin/devis/';
        $this->historiqueOperationBcMagasinService = $container->get(HistoriqueOperationBcMagasinService::class);
    }

    /**
     * @Route("/soumission-bc-magasin/{numeroDevis}", name="bc_magasin_soumission", defaults={"numeroDevis"=null})
     */
    public function index(?string $numeroDevis = null, Request $request): Response
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        $factory = new BcMagasinDtoFactory();
        $bcMagasinDto = $factory->create($numeroDevis);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(BcMagasinType::class, $bcMagasinDto)->getForm();

        //tratiement formulaire
        $this->tratitementFormulaire($form, $request, $numeroDevis);

        //affichage du formulaire
        return $this->render('magasin/bc/soumission.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function tratitementFormulaire($form, Request $request, ?string $numeroDevis = null): void
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BcMagasinDto $dto */
            $dto = $form->getData();

            // recupération montant devis
            $bcMagasinModel = new BcMagasinModel();
            $montantDevis  = $bcMagasinModel->getMontantDevis($numeroDevis)[0] ?? 0.00;

            // recuperation de numero version
            $numeroVersionMax = $this->getEntityManager()->getRepository(BcMagasin::class)->getNumeroVersionMax($numeroDevis);
            $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

            //traitemnet des fichiers
            $this->traitementDesFichiers($form, $numeroDevis, $dto, $montantDevis, $numeroVersion);

            // Enregistrement des données dans la base de données
            $this->enregistrementDonnees($dto, (float) $montantDevis, $numeroVersion);

            // historique du document
            $message = 'Le bon de commande a été soumis avec succès.';
            $this->historiqueOperationBcMagasinService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', true);
        }
    }

    private function traitementDesFichiers(FormInterface $form, string $numeroDevis, BcMagasinDto $dto, float $montantDevis, int $numeroVersion): void
    {
        /** 
         * gestion des pieces jointes et generer le nom du fichier PDF
         * Enregistrement de fichier uploder
         * @var array $nomEtCheminFichiersEnregistrer 
         * @var string $nomAvecCheminFichier
         * @var string $nomFichier
         */
        [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $numeroDevis, $numeroVersion);

        // creation de page de garde
        $generatePdf = new GeneratePdfBcMagasin();
        $generatePdf->generer($this->getUser(), $dto, $nomAvecCheminFichier, (float) $montantDevis);

        // ajout du page de garde à la dernière position
        $traitementDeFichier = new TraitementDeFichier();
        $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, count($nomEtCheminFichiersEnregistrer));
        // fusion du page de garde et des pieces jointes (conversion avant la fusion)
        $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
        $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);

        // copie du pdf fusioné dans DW
        $generatePdf->copyToDWBcMagasin($nomFichier, $numeroDevis);
    }

    private function enregistrementDonnees(BcMagasinDto $dto, ?float $montantDevis, $numeroVersionMax): void
    {
        $entityManager = $this->getEntityManager();

        $factory = new BcMagasinFactory();
        $bcMagasin = $factory->createFromDto($dto, $this->getUser(), $montantDevis, $numeroVersionMax);

        $entityManager->persist($bcMagasin);
        $entityManager->flush();
    }

    private function enregistrementFichier(FormInterface $form, string $numDevis, int $numeroVersion): array
    {
        $nameGenerator = new DevisMagasinGenererNameFileService();
        $uploader = new UploderFileService($this->cheminBaseUpload, $nameGenerator);
        $devisPath = $this->cheminBaseUpload . $numDevis . '/';
        if (!is_dir($devisPath)) {
            mkdir($devisPath, 0777, true);
        }

        $nomEtCheminFichiersEnregistrer = $uploader->getNomsEtCheminFichiers($form, [
            'repertoire' => $devisPath,
            'generer_nom_callback' => function (
                UploadedFile $file,
                int $index
            ) use ($numDevis, $numeroVersion, $nameGenerator) {
                return $nameGenerator->generateBonCommandeName($file, $numDevis, $numeroVersion, $index);
            }
        ]);

        $nomAvecCheminFichier = $nameGenerator->getCheminEtNomDeFichierSansIndex($nomEtCheminFichiersEnregistrer[0]);
        $nomFichier = $nameGenerator->getNomFichier($nomAvecCheminFichier);

        return [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }
}
