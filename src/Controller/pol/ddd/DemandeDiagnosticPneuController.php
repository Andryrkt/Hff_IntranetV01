<?php


namespace App\Controller\pol\ddd;

use App\Controller\Controller;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Factory\pol\DemandeDiagnosticPneuFactory;
use App\Form\pol\ddd\DemandeDiagnosticPneuType;
use App\Model\ddd\DemandeDiagnosticPneuModel;
use App\Service\EmailService;
use App\Service\fichier\TraitementDeFichier;
use App\Service\historiqueOperation\HistoriqueOperationDDDService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\lienGenerique;


/**
 * @Route("/pol")
 */
class DemandeDiagnosticPneuController extends Controller
{
    use lienGenerique;

    private HistoriqueOperationDDDService $historiqueOperation;
    private DemandeDiagnosticPneuModel $demandeDiagnosticPneuModel;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private $demandeDiagnosticPneuRepository;
    private  $demandeDiagnosticPneuFactory;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDDDService($this->getEntityManager());
        $this->demandeDiagnosticPneuModel = new DemandeDiagnosticPneuModel();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/ddd/';

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
        $nomUtilisateur = $this->getSecurityService()->getUserName();
        $mailUtilisateur = $this->getSecurityService()->getUserEmail();


        // Code Société et Agence de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();
        $agenceService = $this->agenceServiceIpsObjet();


        //INITIALISATION DU FORMULAIRE
        $demandeDiagnosticPneu
            ->setDemandeur($nomUtilisateur);
        $demandeDiagnosticPneu
            ->setMailDemandeur($mailUtilisateur);

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
            $uploadedFiles = $form->get('piecesJointes')->getData();
            // ---- Sauvegarde via une méthode interne ----
            try {
                $this->saveDemande($demande, $uploadedFiles);
            } catch (\Exception $e) {
                // $this->addFlash('error', $e->getMessage());
                dump($e->getMessage());
            }
        }
        return null;
    }
    /**
     * Sauvegarde la demande avec transaction, génération du numéro, historique.
     */
    private function saveDemande(DemandeDiagnosticPneu $demande,  $uploadedFiles): void
    {
        $em = $this->getEntityManager();
        $em->beginTransaction();
        try {
            // Génération du numéro de demande
            $numero = $this->demandeDiagnosticPneuModel->genererNumeroDemande($em);
            $demande->setNumeroDemande($numero);

            // ---- Gestion des pièces jointes ---- 
            $ligne = 1;
            foreach ($demande->getDiagnosticPneus() as $pneu) {
                $pneu->setNumeroLigne($ligne++);
                $pneu->setDemande($demande);
                $pneu->setNumeroDemande($numero);
            }
            $em->persist($demande);
            $em->flush();
            $em->commit();

            if ($uploadedFiles) {
                $this->handlePiecesJointes($uploadedFiles, $demande);
            }

            // Envoye mail au atelier
            $this->envoyerMailAtelier($demande);
            // Historique (à décommenter après validation)
            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrée', $demande->getNumeroDemande(), 'demande_diagnostic_pneu_liste', true);
        } catch (\Exception $e) {
            $em->rollback();
            $this->historiqueOperation->sendNotificationCreation($e->getMessage(), '-', 'demande_diagnostic_pneu_liste');
            throw new \RuntimeException('Erreur lors de la sauvegarde : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Gère l'upload des pièces jointes (exemple).
     */
    private function handlePiecesJointes(array $files, DemandeDiagnosticPneu $demande): void
    {
        $numDa = $demande->getNumeroDemande();

        $basePath = rtrim($_ENV['BASE_PATH_FICHIER'], '/') . '/ddd/';
        $dossier = $basePath . $numDa . '/';

        // Créer le dossier s'il n'existe pas
        if (!is_dir($dossier)) {
            mkdir($dossier, 0777, true);
        }
        $nomsFichiers = [];

        foreach ($files as $index => $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $nomOriginal = $file->getClientOriginalName();
            $extension = $file->guessExtension();
            $nomUnique = $numDa . '_'
                . ($index + 1) . '_'
                . pathinfo($nomOriginal, PATHINFO_FILENAME)
                . '.' . $extension;

            // Upload via le service TraitementDeFichier
            try {
                $this->traitementDeFichier->upload($file, $dossier, $nomUnique);
            } catch (\Exception $e) {
                throw new \RuntimeException("Erreur lors de l'upload du fichier : " . $e->getMessage());
            }

            // Stocker uniquement le nom du fichier
            $nomsFichiers[] = $nomUnique;
        }

        $demande->setPiecesJointes($nomsFichiers);
    }


    /**
     * Envoie un email à l'atelier pour signaler une nouvelle demande.
     */
    public function envoyerMailAtelier(DemandeDiagnosticPneu $demande): void
    {
        $mailRespPneu1 = $_ENV['MAIL_TO_RESP_PNEUMATIQUE_1'];
        $mailRespPneu2 = $_ENV['MAIL_TO_RESP_PNEUMATIQUE_2'];
        $mailAtelier         = $_ENV['MAIL_TO_ATELIER'];
        $basePath = rtrim($_ENV['BASE_PATH_COURT'] ?? '', '/');
        $service  = 'Atelier';
        $dateYear = date('Y');

        // Common data
        $commonVariables = [
            'header'        => sprintf('%s - DEMANDE DIAGNOSTIC PNEU : NOUVELLE DEMANDE', $demande->getNumeroDemande()),
            'message'       => 'Une nouvelle demande de diagnostic pneu a été créée.',
            'nomDemandeur'  => $demande->getDemandeur(),
            'numeroDemande' => $demande->getNumeroDemande(),
            'statut'        => $demande->getStatut(),
            'urlIntranet'   => $this->urlGenerique($basePath),
            'service'       => $service,
            'dateYear'      => $dateYear,
        ];

        // Helper to send one email with a given recipient and URL type
        $sendToOne = function ($recipient, $urlType) use ($demande, $basePath, $commonVariables) {
            $relativePath = ($urlType === 'resp')
                ? 'pol/demande-diagnostic-pneu/details/' . $demande->getNumeroDemande()
                : 'pol/demande-diagnostic-pneu/details-atelier/' . $demande->getNumeroDemande();

            $urlDetail = $this->urlGenerique($basePath . '/' . ltrim($relativePath, '/'));

            $variables = array_merge($commonVariables, [
                'subject'   => $commonVariables['header'],
                'urlDetail' => $urlDetail,
            ]);

            $this->envoyerEmail([
                'to'        => [$recipient],
                'cc'        => [$_ENV['MAIL_CC_ATELIER']], // or adjust per recipient
                'variables' => $variables,
            ]);
        };

        // Send to responsables with "details" URL
        $sendToOne($mailRespPneu2, 'resp');
        $sendToOne($mailRespPneu1, 'resp');

        // Send to atelier with "details_atelier" URL
        $sendToOne($mailAtelier, 'atelier');
    }

    /** 
     * Méthode pour envoyer un email
     */
    public function envoyerEmail(array $content): void
    {
        $emailTemplate = 'pol/ddd/email/emailDemandeDiagnosticPneu.html.twig';

        $emailService = new EmailService($this->getTwig());

        $emailService->getMailer()->setFrom($_ENV['MAIL_FROM_ADDRESS'], 'noreply.ddd');

        $emailService->sendEmail($content['to'], $content['cc'] ?? [], $emailTemplate, $content['variables'] ?? [], $content['attachments'] ?? []);
    }
}
