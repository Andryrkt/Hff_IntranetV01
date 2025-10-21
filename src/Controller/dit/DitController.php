<?php

namespace App\Controller\dit;


use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\DitTrait;
use App\Dto\Dit\DemandeInterventionDto;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Form\dit\demandeInterventionType;
use App\Service\genererPdf\dit\GenererPdfDit;
use Symfony\Component\Form\FormInterface;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Factory\Dit\DemandeInterventionFactory;
use App\Service\application\ApplicationService;
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

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDITService($this->getEntityManager());
        $this->demandeInterventionFactory = new DemandeInterventionFactory($this->getEntityManager(), $this->getDitModel(), $this->historiqueOperation);
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
        $this->autorisationAcces($this->getUser(), Application::ID_DIT);
        /** FIN AUtorisation acées */

        $demandeIntervention = new DemandeIntervention();

        //INITIALISATION DU FORMULAIRE
        $agenceService = $this->agenceServiceIpsObjet();
        $demandeIntervention->setAgenceEmetteur($agenceService['agenceIps']->getCodeAgence() . ' ' . $agenceService['agenceIps']->getLibelleAgence());
        $demandeIntervention->setServiceEmetteur($agenceService['serviceIps']->getCodeService() . ' ' . $agenceService['serviceIps']->getLibelleService());
        $demandeIntervention->setAgence($agenceService['agenceIps']);
        $demandeIntervention->setService($agenceService['serviceIps']);
        $demandeIntervention->setIdNiveauUrgence($this->getEntityManager()->getRepository(\App\Entity\admin\dit\WorNiveauUrgence::class)->find(1));

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

            // 2. Enrichir le DTO avec les informations système (logique de l'ancien infoEntrerManuel)
            $user = $this->getUser();
            $em = $this->getEntityManager();
            $dto->utilisateurDemandeur = $user->getNomUtilisateur();
            $dto->heureDemande = $this->getTime();
            $dto->dateDemande = new \DateTime($this->getDatesystem());
            $dto->idStatutDemande = $em->getRepository(\App\Entity\admin\StatutDemande::class)->find(50);
            $dto->numeroDemandeIntervention = $this->autoDecrementDIT('DIT');
            $dto->mailDemandeur = $user->getMail();

            // 3. Utiliser la factory pour créer l'entité complète
            $demandeIntervention = $this->createDemandeInterventionFromDto($dto);

            /** 4. Modifie la colonne dernière_id dans la table applications */
            $applicationService = new ApplicationService($this->getEntityManager());
            $applicationService->mettreAJourDerniereIdApplication('DIT', $demandeIntervention->getNumeroDemandeIntervention());

            // 5. Enregistrement dans la base de données
            $this->getEntityManager()->persist($demandeIntervention);
            $this->getEntityManager()->flush();

            // 6. Traitement des fichiers (PDF, pièces jointes)
            $this->traitementDeFichier($form, $demandeIntervention);

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrée', $demandeIntervention->getNumeroDemandeIntervention(), 'dit_index', true);
        }
    }

    private function traitementDeFichier(FormInterface $form, DemandeIntervention $demandeIntervention)
    {
        /** 
         * gestion des pieces jointes et generer le nom du fichier PDF
         * Enregistrement de fichier uploder
         * @var array $nomEtCheminFichiersEnregistrer 
         * @var string $nomAvecCheminFichier
         * @var string $nomFichier
         */
        [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $demandeIntervention->getNumeroDemandeIntervention(), str_replace("-", "", $demandeIntervention->getAgenceServiceEmetteur()));

        /**CREATION DE LA PAGE DE GARDE*/
        $genererPdfDit = new GenererPdfDit();
        if (!in_array((int)$demandeIntervention->getIdMateriel(), [14571, 7669, 7670, 7671, 7672, 7673, 7674, 7675, 7677, 9863])) {
            //récupération des historique de materiel (informix)
            $historiqueMateriel = $this->historiqueInterventionMateriel($demandeIntervention);
        } else {
            $historiqueMateriel = [];
        }
        $genererPdfDit->genererPdfDit($demandeIntervention, $historiqueMateriel, $nomAvecCheminFichier);

        // ajout du page de garde à la premier position
        $traitementDeFichier = new TraitementDeFichier();
        $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, 0);
        // fusion du page de garde et des pieces jointes (conversion avant la fusion)
        $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
        $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);


        //Copier le PDF DANS DOXCUWARE
        $genererPdfDit->copyToDOCUWARE($nomFichier, $demandeIntervention->getNumeroDemandeIntervention());
    }

    private function enregistrementFichier(FormInterface $form, string $numDit, string $agServEmetteur): array
    {
        $nameGenerator = new DitNameFileService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . 'dit/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $devisPath = $cheminBaseUpload . $numDit . '/';
        if (!is_dir($devisPath)) {
            mkdir($devisPath, 0777, true);
        }

        $nomEtCheminFichiersEnregistrer = $uploader->getNomsEtCheminFichiers($form, [
            'repertoire' => $devisPath,
            'generer_nom_callback' => function (
                UploadedFile $file,
                int $index
            ) use ($numDit, $nameGenerator, $agServEmetteur) {
                return $nameGenerator->generateDitName($file, $numDit, $agServEmetteur, $index);
            }
        ]);

        $nomAvecCheminFichier = $nameGenerator->getCheminEtNomDeFichierSansIndex($nomEtCheminFichiersEnregistrer[0]);
        $nomFichier = $nameGenerator->getNomFichier($nomAvecCheminFichier);

        return [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }
}
