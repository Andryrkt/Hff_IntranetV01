<?php

namespace App\Controller\dit;

use App\Service\FusionPdf;
use App\Model\dit\DitModel;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\DitTrait;
use App\Dto\Dit\DemandeInterventionDto;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Form\dit\demandeInterventionType;
use App\Service\autres\AutoIncDecService;
use Symfony\Component\Form\FormInterface;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use App\Service\genererPdf\dit\GenererPdfDit;
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
class DitDuplicationController extends Controller
{
    use DitTrait;
    use FormatageTrait;
    use AutorisationTrait;
    use PdfConversionTrait;

    private $historiqueOperation;
    private $demandeInterventionFactory;
    private DitModel $ditModel;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDITService($this->getEntityManager());
        $this->ditModel = new DitModel();
        $this->demandeInterventionFactory = new DemandeInterventionFactory($this->getEntityManager(), $this->ditModel, $this->historiqueOperation);
    }

    /**
     * @Route("/dit-duplication/{id<\d+>}/{numDit<\w+>}", name="dit_duplication")
     */
    public function Duplication($numDit, $id, Request $request)
    {
        $this->verifierSessionUtilisateur();
        $this->autorisationAcces($this->getUser(), Application::ID_DIT);

        $user = $this->getUser();
        $dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->find($id);

        // Simplification de la logique de duplication
        $demandeInterventions = $this->initialisationForm($dit);

        $form = $this->getFormFactory()->createBuilder(demandeInterventionType::class, $demandeInterventions)->getForm();
        $this->traitementFormulaire($form, $request, $user);

        $this->logUserVisit('dit_duplication', ['id' => $id, 'numDit' => $numDit]);

        return $this->render('dit/duplication.html.twig', [
            'form' => $form->createView(),
            'dit' => $dit,
            'estAvoir' => $this->estAvoir($dit),
            'estRefactorisation' => $this->estRefacturation($dit)
        ]);
    }

    public function  initialisationForm(DemandeIntervention $dit): DemandeIntervention
    {
        $codeEmetteur = explode('-', $dit->getAgenceServiceEmetteur());
        $agenceEmetteur = $this->getEntityManager()->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeEmetteur[0]]);
        $serviceEmetteur = $this->getEntityManager()->getRepository(Service::class)->findOneBy(['codeService' => $codeEmetteur[1]]);
        $codeDebiteur = explode('-', $dit->getAgenceServiceDebiteur());
        $ditModel = new DitModel();
        $data = $ditModel->findAll($dit->getIdMateriel(), $dit->getNumParc(), $dit->getNumSerie());
        $dit
            ->setNumParc($data[0]['num_parc'])
            ->setNumSerie($data[0]['num_serie'])
            ->setIdMateriel($data[0]['num_matricule'])
            ->setConstructeur($data[0]['constructeur'])
            ->setModele($data[0]['modele'])
            ->setDesignation($data[0]['designation'])
            ->setCasier($data[0]['casier_emetteur'])
            ->setKm($data[0]['km'])
            ->setHeure($data[0]['heure'])
        ;

        $demandeInterventions = new DemandeIntervention();
        $demandeInterventions
            ->setNumeroDemandeIntervention($dit->getNumeroDemandeIntervention())
            ->setAgenceServiceEmetteur($dit->getAgenceServiceEmetteur())
            ->setAgenceEmetteur($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence())
            ->setServiceEmetteur($serviceEmetteur->getCodeService() . ' ' . $serviceEmetteur->getLibelleService())
            ->setAgence($this->getEntityManager()->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeDebiteur[0]]))
            ->setService($this->getEntityManager()->getRepository(Service::class)->findOneBy(['codeService' => $codeDebiteur[1]]))
            ->setTypeDocument($dit->getTypeDocument())
            ->setCodeSociete($dit->getCodeSociete())
            ->setTypeReparation($dit->getTypeReparation())
            ->setReparationRealise($dit->getReparationRealise())
            ->setCategorieDemande($dit->getCategorieDemande())
            ->setInternetExterne($dit->getInternetExterne())
            ->setNomClient($dit->getNomClient())
            ->setNumeroTel($dit->getNumeroTel())
            ->setDatePrevueTravaux($dit->getDatePrevueTravaux())
            ->setDemandeDevis($dit->getDemandeDevis())
            ->setIdNiveauUrgence($dit->getIdNiveauUrgence())
            ->setAvisRecouvrement($dit->getAvisRecouvrement())
            ->setClientSousContrat($dit->getClientSousContrat())
            ->setObjetDemande($dit->getObjetDemande())
            ->setDetailDemande($dit->getDetailDemande())
            ->setLivraisonPartiel($dit->getLivraisonPartiel())
            ->setNumParc($dit->getNumParc())
            ->setNumSerie($dit->getNumSerie())
            ->setIdMateriel($dit->getIdMateriel())
            ->setConstructeur($dit->getConstructeur())
            ->setModele($dit->getModele())
            ->setDesignation($dit->getDesignation())
            ->setCasier($dit->getCasier())
            ->setKm($dit->getKm())
            ->setHeure($dit->getHeure())
        ;

        return $demandeInterventions;
    }

    private function estAvoir(DemandeIntervention $dit): bool
    {
        $position = $this->ditModel->getPosition($dit->getNumeroDemandeIntervention());
        if (!empty($position)) {
            $positionOR =  in_array($position[0], ['FC', 'CP']); //l'OR rattaché à la DIT initale est facturé / comptabilisé (seor_pos in ('FC','CP')
            $statutDit = $dit->getIdStatutDemande()->getId() === DemandeIntervention::STATUT_CLOTUREE_VALIDER; // le dernier statut de la DIT inital est 'Validé'
            $numeroAvoir = $dit->getNumeroDemandeDitAvoit() === null;
            return $positionOR && $statutDit && $numeroAvoir;
        }

        return false;
    }

    private function estRefacturation(DemandeIntervention $dit): bool
    {
        $position = $this->ditModel->getPosition($dit->getNumeroDemandeIntervention());
        if (!empty($position)) {
            $niAvoirNiRefac = $dit->getEstDitAvoir() === false && $dit->getEstDitRefacturation() === false; //b. la DIT initiale n'est ni une DIT d'avoir, ni une DIT de refacturation 
            $positionOR =  in_array($position[0], ['FC', 'CP']); //c. l'OR rattaché à la DIT initale est facturé / comptabilisé (seor_pos in ('FC','CP')
            $numeroAvoir = $dit->getNumeroDemandeDitAvoit() <> null;
            return $positionOR && $niAvoirNiRefac && $numeroAvoir;
        }

        return false;
    }

    private function traitementFormulaire($form, Request $request, $user)
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

            // 1. Créer le DTO
            $dto = DemandeInterventionDto::createFromEntity($ditFromForm);

            // 2. Enrichir le DTO (logique de infoEntrerManuel)
            $em = $this->getEntityManager();
            $application = $em->getRepository(Application::class)->findOneBy(['codeApp' => DemandeIntervention::CODE_APP]);
            $dto->utilisateurDemandeur = $user->getNomUtilisateur();
            $dto->heureDemande = $this->getTime();
            $dto->dateDemande = new \DateTime($this->getDatesystem());
            $dto->idStatutDemande = $em->getRepository(\App\Entity\admin\StatutDemande::class)->find(50);
            $dto->numeroDemandeIntervention = $this->autoDecrementDIT('DIT');
            $dto->mailDemandeur = $user->getMail();

            /**   @var array 3. Utiliser la factory pour créer l'entité complète*/
            $demandeInterventions = $this->createDemandeInterventionFromDto($dto, $application);


            foreach ($demandeInterventions as $demandeIntervention) {
                /** 4. Modifie la colonne dernière_id dans la table applications */
                AutoIncDecService::mettreAJourDerniereIdApplication($application, $this->getEntityManager(), $demandeIntervention->getNumeroDemandeIntervention());

                /** @var array 5. Traitement des fichiers (PDF, pièces jointes) */
                $nomFichierEnregistrer  = $this->traitementDeFichier($form, $demandeIntervention);

                // 6. Enregistrement dans la base de données
                $this->enregistrementBd($demandeIntervention, $nomFichierEnregistrer);
            }

            $this->getEntityManager()->flush();

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrée', $demandeIntervention->getNumeroDemandeIntervention(), 'dit_index', true);
        }
    }
    private function enregistrementBd(DemandeIntervention $demandeIntervention, array $nomFichierEnregistrer): void
    {
        $demandeIntervention
            ->setPieceJoint01($nomFichierEnregistrer[0] ?? null)
            ->setPieceJoint02($nomFichierEnregistrer[1] ?? null)
            ->setPieceJoint03($nomFichierEnregistrer[2] ?? null);
        $this->getEntityManager()->persist($demandeIntervention);
    }

    private function traitementDeFichier(FormInterface $form, DemandeIntervention $demandeIntervention): array
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

        /**CREATION DE LA PAGE DE GARDE*/
        $genererPdfDit = new GenererPdfDit();
        $idMateriel = (int)$demandeIntervention->getIdMateriel();
        if (!in_array($idMateriel, $this->ditModel->getNumeroMatriculePasMateriel())) {
            //récupération des historique de materiel (informix)
            $historiqueMateriel = $this->historiqueInterventionMateriel($idMateriel);
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




        return $nomFichierEnregistrer;
    }

    private function enregistrementFichier(FormInterface $form, string $numDit, string $agServEmetteur): array
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


        $nomFichier = $nameGenerator->generateDitNamePrincipal($numDit, $agServEmetteur);
        $nomAvecCheminFichier = $path . $nomFichier;



        return [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }
}
