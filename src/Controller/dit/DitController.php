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
use App\Controller\Traits\MiseAjourAppTrait;
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
    use MiseAjourAppTrait;


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

            /**   @var array 3. Utiliser la factory pour créer l'entité complète*/
            $demandeInterventions = $this->createDemandeInterventionFromDto($dto);

            /** 4. Modifie la colonne dernière_id dans la table applications */
            $this->mettreAJourDerniereIdApplication('DIT', $demandeInterventions[0]->getNumeroDemandeIntervention());

            /** @var array 5. Traitement des fichiers (PDF, pièces jointes) */
            $nomFichierEnregistrer  = $this->traitementDeFichier($form, $demandeInterventions);

            // 6. Enregistrement dans la base de données
            $this->enregistrementBd($demandeInterventions, $nomFichierEnregistrer);

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrée', $demandeInterventions[0]->getNumeroDemandeIntervention(), 'dit_index', true);
        }
    }

    private function enregistrementBd(array $demandeInterventions, array $nomFichierEnregistrer): void
    {
        foreach ($demandeInterventions as  $demandeIntervention) {
            $demandeIntervention
                ->setPieceJoint01($nomFichierEnregistrer[0] ?? null)
                ->setPieceJoint02($nomFichierEnregistrer[1] ?? null)
                ->setPieceJoint03($nomFichierEnregistrer[2] ?? null);
            $this->getEntityManager()->persist($demandeIntervention);
        }

        $this->getEntityManager()->flush();
    }

    private function traitementDeFichier(FormInterface $form, array $demandeInterventions): array
    {
        /** 
         * gestion des pieces jointes et generer le nom du fichier PDF
         * Enregistrement de fichier uploder
         * @var array $nomEtCheminFichiersEnregistrer 
         * @var array $nomFichierEnregistrer 
         * @var array $nomAvecCheminFichier
         * @var array $nomFichier
         */
        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $demandeInterventions[0]->getNumeroDemandeIntervention(), str_replace("-", "", $demandeInterventions[0]->getAgenceServiceEmetteur()), $demandeInterventions[0]->getEstAtePolTana());

        /**CREATION DE LA PAGE DE GARDE*/
        $genererPdfDit = new GenererPdfDit();
        $idMateriel = (int)$demandeInterventions[0]->getIdMateriel();
        if (!in_array($idMateriel, [14571, 7669, 7670, 7671, 7672, 7673, 7674, 7675, 7677, 9863])) {
            //récupération des historique de materiel (informix)
            $historiqueMateriel = $this->historiqueInterventionMateriel($idMateriel);
        } else {
            $historiqueMateriel = [];
        }

        if ($demandeInterventions[0]->getEstAtePolTana()) {

            for ($i = 0; $i < 2; $i++) {

                $genererPdfDit->genererPdfDit($demandeInterventions[$i], $historiqueMateriel, $nomAvecCheminFichier[$i]);

                // ajout du page de garde à la premier position
                $traitementDeFichier = new TraitementDeFichier();
                $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier[$i], 0);
                // fusion du page de garde et des pieces jointes (conversion avant la fusion)
                $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
                $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier[$i]);

                //Copier le PDF DANS DOXCUWARE
                $genererPdfDit->copyToDOCUWARE($nomFichier[$i], $demandeInterventions[0]->getNumeroDemandeIntervention());
            }
        } else {
            $genererPdfDit->genererPdfDit($demandeInterventions[0], $historiqueMateriel, $nomAvecCheminFichier[0]);

            // ajout du page de garde à la premier position
            $traitementDeFichier = new TraitementDeFichier();
            $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier[0], 0);
            // fusion du page de garde et des pieces jointes (conversion avant la fusion)
            $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
            $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier[0]);


            //Copier le PDF DANS DOXCUWARE
            $genererPdfDit->copyToDOCUWARE($nomFichier[0], $demandeInterventions[0]->getNumeroDemandeIntervention());
        }



        return $nomFichierEnregistrer;
    }

    private function enregistrementFichier(FormInterface $form, string $numDit, string $agServEmetteur, bool $estAteTanaPol = false): array
    {
        $nameGenerator = new DitNameFileService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . 'dit/';
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


        if ($estAteTanaPol) {
            $nomFichier = $nameGenerator->generateDitNamePrincipal($numDit, $agServEmetteur);
            $nomFichierPol = $nameGenerator->generateDitNamePrincipalPol($numDit, $agServEmetteur);
            $nomAvecCheminFichier = $path . $nomFichier;
            $nomAvecCheminFichierPol = $path . $nomFichierPol;
            $nomFichiers = [$nomFichier, $nomFichierPol];
            $nomAvecCheminFichiers = [$nomAvecCheminFichier, $nomAvecCheminFichierPol];
        } else {
            $nomFichier = $nameGenerator->generateDitNamePrincipal($numDit, $agServEmetteur);
            $nomAvecCheminFichier = $path . $nomFichier;
            $nomFichiers = [$nomFichier];
            $nomAvecCheminFichiers = [$nomAvecCheminFichier];
        }



        return [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichiers, $nomFichiers];
    }
}
