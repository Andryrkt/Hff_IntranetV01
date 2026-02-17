<?php

namespace App\Controller\dit;


use App\Model\dit\DitModel;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\DitTrait;
use App\Entity\admin\StatutDemande;
use App\Dto\Dit\DemandeInterventionDto;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Form\dit\demandeInterventionType;
use App\Service\autres\AutoIncDecService;
use Symfony\Component\Form\FormInterface;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use App\Service\genererPdf\dit\GenererPdfDit;
use Symfony\Component\HttpFoundation\Request;
use App\Factory\Dit\DemandeInterventionFactory;
use App\Repository\dit\DitRepository;
use App\Service\dit\fichier\DitNameFileService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\historiqueOperation\HistoriqueOperationDITService;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitController extends Controller
{
    use DitTrait;
    use FormatageTrait;
    use AutorisationTrait;
    use PdfConversionTrait;


    private $historiqueOperation;
    private $demandeInterventionFactory;
    private DitModel $ditModel;
    private DitRepository $demandeRepository;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDITService($this->getEntityManager());
        $this->ditModel = new DitModel();
        $this->demandeInterventionFactory = new DemandeInterventionFactory($this->getEntityManager(), $this->ditModel, $this->historiqueOperation);
        $this->demandeRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/new", name="dit_new")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces(Application::ID_DIT);
        /** FIN AUtorisation acées */

        $demandeIntervention = new DemandeIntervention();

        //INITIALISATION DU FORMULAIRE
        $agenceService = $this->agenceServiceIpsObjet();
        $demandeIntervention->setAgenceEmetteur($agenceService['agenceIps']->getCodeAgence() . ' ' . $agenceService['agenceIps']->getLibelleAgence());
        $demandeIntervention->setServiceEmetteur($agenceService['serviceIps']->getCodeService() . ' ' . $agenceService['serviceIps']->getLibelleService());
        $demandeIntervention->setAgence($agenceService['agenceIps']);
        $demandeIntervention->setService($agenceService['serviceIps']);
        $demandeIntervention->setIdNiveauUrgence($this->getEntityManager()->getRepository(WorNiveauUrgence::class)->find(1));

        //AFFICHAGE ET TRAITEMENT DU FORMULAIRE
        $form = $this->getFormFactory()->createBuilder(demandeInterventionType::class, $demandeIntervention)->getForm();
        $this->traitementFormulaire($form, $request, $demandeIntervention);

        $this->logUserVisit('dit_new'); // historisation du page visité par l'utilisateur

        return $this->render('dit/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function traitementFormulaire($form, Request $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeIntervention $ditFromForm */
            $ditFromForm = $form->getData();

            if (empty($ditFromForm->getIdMateriel())) {
                $message = 'Échec lors de la création de la DIT... Impossible de récupérer les informations du matériel.';
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_index');
                return;
            }

            if ($ditFromForm->getInternetExterne() === "EXTERNE" && empty($ditFromForm->getNomClient()) && empty($ditFromForm->getNumeroClient())) {
                $message = 'Échec lors de la création de la DIT... Impossible de récupérer les informations du client.';
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_index');
                return;
            }

            // 1. Créer le DTO à partir des données du formulaire
            $dto = DemandeInterventionDto::createFromEntity($ditFromForm);

            // 2. Enrichir le DTO avec les informations système (initialisation ou ajout des info par defaut)
            $user = $this->getUser();
            $em = $this->getEntityManager();
            $dto->utilisateurDemandeur = $user->getNomUtilisateur();
            $dto->heureDemande = $this->getTime();
            $dto->dateDemande = new \DateTime($this->getDatesystem());
            $dto->idStatutDemande = $em->getRepository(StatutDemande::class)->find(50);
            $dto->mailDemandeur = $user->getMail();
            $dto->societe = $user->getSociettes(); //TODO: rectifier pour la recupération de la société dans user

            /**   @var DemandeIntervention[] $demandeInterventions 3. Utiliser la factory pour créer l'entité complète*/
            $demandeInterventions = $this->createDemandeInterventionFromDto($dto);

            foreach ($demandeInterventions as $demandeIntervention) {
                // 4. recuperation du dernière numero demande d'intervention et generation du numero de demande 
                $application = $em->getRepository(Application::class)->findOneBy(['codeApp' => DemandeIntervention::CODE_APP]);
                $numeroDemandeIntervention = $this->genererNumeroDemandeIntervention($application);

                // 5.enregistrement du numero demande d'intervention et Modifie la colonne dernière_id dans la table applications
                $demandeIntervention->setNumeroDemandeIntervention($numeroDemandeIntervention);
                AutoIncDecService::mettreAJourDerniereIdApplication($application, $em, $numeroDemandeIntervention);

                /** 6. Traitement des fichiers (PDF, pièces jointes) @var array $nomFichierEnregistrer @var string $nomFichier  */
                $genererPdfDit = new GenererPdfDit();
                [$nomFichierEnregistrer, $nomFichier]  = $this->traitementDeFichier($form, $demandeIntervention, $genererPdfDit);

                // 7. Enregistrement dans la base de donnée
                $this->enregistrementBd($demandeIntervention, $nomFichierEnregistrer);

                // 8.Copier le PDF DANS DOXCUWARE
                $reponse = $genererPdfDit->copyToDOCUWARE($nomFichier, $demandeIntervention->getNumeroDemandeIntervention());

                // 9. modification de la colonne pdf_deposer_dw et date_depot_pdf_dw
                $this->modificationBdPourHitorisationDw($em, $demandeIntervention, $reponse);
            }

            // 10. enregistrement dans l'historisation de la sucès de la demande
            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrée', $demandeInterventions[0]->getNumeroDemandeIntervention(), 'dit_index', true);
        }
    }

    private function modificationBdPourHitorisationDw($em, $demandeIntervention, bool $reponse): void
    {
        $dit = $this->demandeRepository->findOneBy(['numeroDemandeIntervention' => $demandeIntervention->getNumeroDemandeIntervention()]);
        $dit->setPdfDeposerDw($reponse)
            ->setDateDepotPdfDw(new \DateTime());
        $em->persist($dit);
        $em->flush();
    }

    public function genererNumeroDemandeIntervention(Application $application)
    {
        // 1. decrementation du dernière numero d'intervention recupérer dans la table applications
        $numeroDemandeIntervention = AutoIncDecService::autoGenerateNumero(DemandeIntervention::CODE_APP, $application->getDerniereId(), false);

        // 2. Vérification de l'unicité du numéro de demande
        $existingDemande = $this->demandeRepository->findOneBy(['numeroDemandeIntervention' => $numeroDemandeIntervention]);
        if ($existingDemande) {
            // Log de l'erreur et notification à l'utilisateur
            $message = sprintf(
                'Échec lors de la création de la DIT. Le numéro de demande "%s" existe déjà. Veuillez réessayer.',
                $numeroDemandeIntervention
            );
            error_log($message); // Log pour les développeurs
            $this->historiqueOperation->sendNotificationCreation($message, $numeroDemandeIntervention, 'dit_index');
            return; // Bloquer la suite du traitement
            exit();
        }

        //3. retourne le numero decrementer si le numero n'existe pas
        return $numeroDemandeIntervention;
    }

    private function enregistrementBd(DemandeIntervention $demandeIntervention, array $nomFichierEnregistrer): void
    {
        $demandeIntervention
            ->setPieceJoint01($nomFichierEnregistrer[0] ?? null)
            ->setPieceJoint02($nomFichierEnregistrer[1] ?? null)
            ->setPieceJoint03($nomFichierEnregistrer[2] ?? null);
        $this->getEntityManager()->persist($demandeIntervention);
        $this->getEntityManager()->flush();
    }

    private function traitementDeFichier(FormInterface $form, DemandeIntervention $demandeIntervention, GenererPdfDit $genererPdfDit): array
    {
        /** 
         * gestion des pieces jointes et generer le nom du fichier PDF
         * Enregistrement de fichier uploder
         * @var array $nomEtCheminFichiersEnregistrer 
         * @var array $nomFichierEnregistrer 
         * @var string $nomAvecCheminFichier
         * @var string $nomFichier
         */
        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $demandeIntervention->getNumeroDemandeIntervention(), str_replace("-", "", $demandeIntervention->getAgenceServiceEmetteur()));

        /** 1. CREATION DE LA PAGE DE GARDE*/
        $idMateriel = (int)$demandeIntervention->getIdMateriel();
        if (!in_array($idMateriel, $this->ditModel->getNumeroMatriculePasMateriel())) {
            //récupération des historique de materiel (informix)
            $historiqueMateriel = $this->historiqueInterventionMateriel($idMateriel, $demandeIntervention->getReparationRealise());
        } else {
            $historiqueMateriel = [];
        }
        $genererPdfDit->genererPdfDit($demandeIntervention, $historiqueMateriel, $nomAvecCheminFichier);

        // 2. ajout du page de garde à la premier position
        $traitementDeFichier = new TraitementDeFichier();
        $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, 0);
        // 3. fusion du page de garde et des pieces jointes (conversion avant la fusion)
        $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
        $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);


        return [$nomFichierEnregistrer, $nomFichier];
    }

    private function enregistrementFichier(FormInterface $form, string $numDit, string $agServEmetteur): array
    {
        $nameGenerator = new DitNameFileService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/dit/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $path = $cheminBaseUpload . $numDit . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        /**
         * recupère les noms + chemins dans un tableau et les noms dans une autre
         * @var array $nomEtCheminFichiersEnregistrer
         * @var array $nomFichierEnregistrer
         */
        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer] = $uploader->getFichiers($form, [
            'repertoire' => $path,
            'generer_nom_callback' => function (
                UploadedFile $file,
                int $index
            ) use ($numDit, $nameGenerator, $agServEmetteur) {
                return $nameGenerator->generateDitNameFile($file, $numDit, $agServEmetteur, $index);
            }
        ]);


        $nomFichier = $nameGenerator->generateDitNamePrincipal($numDit, $agServEmetteur);
        $nomAvecCheminFichier = $path . $nomFichier;



        return [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }
}
