<?php

namespace App\Controller\pol\bc;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use App\Form\magasin\bc\BcMagasinType;
use App\Model\magasin\bc\BcMagasinDto;
use App\Service\autres\VersionService;
use App\Model\magasin\bc\BcMagasinModel;
use Symfony\Component\Form\FormInterface;
use App\Service\fichier\UploderFileService;
use App\Factory\magasin\bc\BcMagasinFactory;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use App\Entity\magasin\devis\DevisMagasin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Factory\magasin\bc\BcMagasinDtoFactory;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\magasin\bc\BcMagasinValidationService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\genererPdf\magasin\bc\GeneratePdfBcMagasin;
use App\Service\magasin\devis\Fichier\DevisMagasinGenererNameFileService;
use App\Service\historiqueOperation\magasin\bc\HistoriqueOperationBcMagasinService;
use DateTime;

/**
 * @Route("/pol")
 */
class BcMagasinPolController extends Controller
{
    use PdfConversionTrait;

    private HistoriqueOperationBcMagasinService $historiqueOperationBcMagasinService;

    public function __construct()
    {
        parent::__construct();
        global $container;
        $this->historiqueOperationBcMagasinService = $container->get(HistoriqueOperationBcMagasinService::class);
    }

    /**
     * @Route("/soumission-bc-magasin-pol/{numeroDevis}", name="bc_magasin_pol_soumission", defaults={"numeroDevis"=null})
     */
    public function index(?string $numeroDevis = null, Request $request): Response
    {
        /** Gestion de blocage */
        $this->gestionDeBlocage($numeroDevis);

        $factory = new BcMagasinDtoFactory();
        $bcMagasinDto = $factory->create($numeroDevis);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(BcMagasinType::class, $bcMagasinDto)->getForm();

        //tratiement formulaire
        $this->tratitementFormulaire($form, $request, $numeroDevis);

        //affichage du formulaire
        return $this->render('pol/bc/soumission.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function gestionDeBlocage(string $numeroDevis): Response
    {
        $validateur = new BcMagasinValidationService();
        $data = [
            'numeroDevis' => $numeroDevis,
            'bcRepository' => $this->getEntityManager()->getRepository(BcMagasin::class),
            'devisMagasinRepository' => $this->getEntityManager()->getRepository(DevisMagasin::class)
        ];
        if (!$validateur->validateData($data)) {
            return $this->redirectToRoute($validateur->getRedirectRoute());
        }
        return new Response();
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

            //modification du statu bc dans la table devis_soumis_a_validation_neg
            $this->modificationStatutBCDansDevisMagasin($numeroDevis, $dto->dateBc);

            // historique du document
            $message = 'Le bon de commande a été soumis avec succès.';
            $this->historiqueOperationBcMagasinService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_pol_liste', true);
        }
    }

    private function traitementDesFichiers(FormInterface $form, string $numeroDevis, BcMagasinDto $dto, float $montantDevis, int $numeroVersion): void
    {
        /** 
         * 1. gestion des pieces jointes et generer le nom du fichier PDF
         * Enregistrement de fichier uploder
         * @var array $nomEtCheminFichiersEnregistrer
         * @var array $nomFichierEnregistrer 
         * @var string $nomAvecCheminFichier (page de garde)
         * @var string $nomFichier (page de garde)
         */
        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $numeroDevis, $numeroVersion);

        // 2. creation de page de garde
        $generatePdf = new GeneratePdfBcMagasin();
        // 2.1 recupération des information utile pour le page de garde et ajout dans le devis magasin
        $listeDevisMagasinModel = new ListeDevisMagasinModel();
        $clientAndModePaiement = $listeDevisMagasinModel->getClientAndModePaiement($numeroDevis);
        $dto->codeClient = $clientAndModePaiement[0]['code_client'];
        $dto->nomClient = $clientAndModePaiement[0]['nom_client'];
        $dto->modePayement = $clientAndModePaiement[0]['mode_paiement'];
        $generatePdf->generer($this->getUser(), $dto, $nomAvecCheminFichier, (float) $montantDevis);



        // 3. ajout du page de garde à la dernière position
        $traitementDeFichier = new TraitementDeFichier();
        $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, count($nomEtCheminFichiersEnregistrer));

        // 4. fusion du page de garde et des pieces jointes (conversion avant la fusion)
        $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
        $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);

        // 5. copie du pdf fusioné dans DW
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

    private function modificationStatutBCDansDevisMagasin(string $numeroDevis, DateTime $dateBc): void
    {
        $devisRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);
        $numeroVersionMax = $devisRepository->getNumeroVersionMax($numeroDevis);
        $devisMagasin = $devisRepository->findOneBy(['numeroDevis' => $numeroDevis, 'numeroVersion' => $numeroVersionMax]);

        if ($devisMagasin) {
            $devisMagasin->setStatutBc(BcMagasin::STATUT_SOUMIS_VALIDATION);
            $devisMagasin->setDateBc($dateBc);
        }

        $this->getEntityManager()->flush();
    }
}
