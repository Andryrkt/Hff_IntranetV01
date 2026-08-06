<?php


namespace App\Controller\pol\ddd;

use App\Controller\Controller;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Factory\pol\DemandeDiagnosticPneuFactory;
use App\Form\pol\ddd\DemandeDiagnosticPneuType;
use App\Model\ddd\DemandeDiagnosticPneuModel;
use App\Service\historiqueOperation\HistoriqueOperationDDDService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



/**
 * @Route("/pol")
 */
class DemandeDiagnosticPneuController extends Controller
{
    private HistoriqueOperationDDDService $historiqueOperation;
    private DemandeDiagnosticPneuModel $demandeDiagnosticPneuModel;
    private $demandeDiagnosticPneuRepository;
    private  $demandeDiagnosticPneuFactory;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDDDService($this->getEntityManager());
        $this->demandeDiagnosticPneuModel = new DemandeDiagnosticPneuModel();

        $this->demandeDiagnosticPneuFactory = new DemandeDiagnosticPneuFactory($this->getEntityManager(), $this->demandeDiagnosticPneuModel, $this->historiqueOperation);

        $this->demandeDiagnosticPneuRepository = $this->getEntityManager()->getRepository(DemandeDiagnosticPneu::class);
    }

    /**
     * @Route("/nouveau-demande-diagnostic-pneu", name="nouveau_demande_diagnostic_pneu")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request)
    {
        $demandeDiagnosticPneu = new DemandeDiagnosticPneu();

        //Nom d'utilisateur
        $utilisateur = $this->getSecurityService()->getUserName();


        // Code Société et Agence de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();
        $agenceService = $this->agenceServiceIpsObjet();

        //INITIALISATION DU FORMULAIRE
        $demandeDiagnosticPneu
            ->setDemandeur($utilisateur);

        //AFFICHAGE ET TRAITEMENT DU FORMULAIRE


        $form = $this->getFormFactory()->createBuilder(DemandeDiagnosticPneuType::class, $demandeDiagnosticPneu)->getForm();


        $this->traitementFormulaire($form, $request);

        // $this->logUserVisit('demande_diag_pneu_new');

        return $this->render('pol/ddd/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Traite le formulaire, gère l'upload et la sauvegarde.
     * Retourne une Response si le formulaire est soumis et valide, sinon null.
     */
    private function traitementFormulaire($form, Request $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeDiagnosticPneu $demande */
            $demande = $form->getData();
            dump($demande);
            $this->saveDemande($demande);

            // ---- Gestion des pièces jointes ---- 
            $uploadedFiles = $form->get('piecesJointes')->getData();
            if ($uploadedFiles) {
                $this->handlePiecesJointes($uploadedFiles, $demande);
            }

            // ---- Sauvegarde via une méthode interne ----
            try {
                // $this->addFlash('success', 'Demande de diagnostic créée avec succès.');
                // return $this->redirectToRoute('liste_demandes_diagnostic_pneu');
            } catch (\Exception $e) {
                // $this->addFlash('error', $e->getMessage());
            }
        }
        return null;
    }
    /**
     * Sauvegarde la demande avec transaction, génération du numéro, historique.
     */
    private function saveDemande(DemandeDiagnosticPneu $demande): void
    {
        $em = $this->getEntityManager();
        $em->beginTransaction();

        try {
            // Génération du numéro de demande
            $numero = $this->demandeDiagnosticPneuModel->genererNumeroDemande($em);
            $demande->setNumeroDemande($numero);

            // Assigner le numéro de ligne à chaque pneu
            $ligne = 1;
            foreach ($demande->getDiagnosticPneus() as $pneu) {
                $pneu->setNumeroLigne($ligne++);
                $pneu->setDemande($demande);
            }

            // Persist de la demande (les pneus seront persistés grâce à cascade={"persist"})
            $em->persist($demande);
            $em->flush();
            $em->commit();

            // Message de succès (temporaire pour le debug)
            dd("Sauvegarde réussie !");

            // Historique (à décommenter après validation)
            // $this->historiqueOperation->sendNotificationCreation(...);      
        } catch (\Exception $e) {
            $em->rollback();
            throw new \RuntimeException('Erreur lors de la sauvegarde : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Gère l'upload des pièces jointes (exemple).
     */
    private function handlePiecesJointes(array $files, DemandeDiagnosticPneu $demande): void
    {
        // Définir un répertoire de stockage (ex: public/uploads/diagnostic_pneu/)
        // $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/diagnostic_pneu/';
        // if (!is_dir($uploadDir)) {
        //     mkdir($uploadDir, 0777, true);
        // }

        // $uploadedPaths = [];
        // foreach ($files as $file) {
        //     $newFilename = uniqid() . '.' . $file->guessExtension();
        //     $file->move($uploadDir, $newFilename);
        //     $uploadedPaths[] = '/uploads/diagnostic_pneu/' . $newFilename;
        // }
        // $demande->setPiecesJointes($uploadedPaths);
    }
}
