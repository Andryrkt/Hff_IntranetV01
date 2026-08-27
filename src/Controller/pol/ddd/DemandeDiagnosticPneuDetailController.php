<?php

namespace App\Controller\pol\ddd;

use App\Controller\Controller;
use App\Controller\Traits\lienGenerique;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\ddd\DemandeDiagnosticPneuDto;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Factory\pol\DemandeDiagnosticPneuFactory;
use App\Form\pol\ddd\DiagnosticPneuDetailType;
use App\Form\pol\ddd\DiagnosticPneuType;
use App\Model\ddd\DemandeDiagnosticPneuModel;
use App\Service\dit\fichier\DitNameFileService;
use App\Service\EmailService;
use App\Service\fichier\TraitementDeFichier;
use App\Service\fichier\UploderFileService;
use App\Service\genererPdf\pol\ddd\GenererPdfDdd;
use App\Service\historiqueOperation\HistoriqueOperationDDDService;
use App\Service\pol\ddd\fichier\DddNameFileService;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @Route("/pol/demande-diagnostic-pneu")
 */
class DemandeDiagnosticPneuDetailController extends Controller
{
    use lienGenerique;
    use PdfConversionTrait;

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
     * @Route("/details/{numeroDemande}", name="demande_diagnostic_pneu_details")
     */
    public function detailReadonly(string $numeroDemande): Response
    {
        $em = $this->getEntityManager();
        $demande = $em->getRepository(DemandeDiagnosticPneu::class)->findOneBy(['numeroDemande' => $numeroDemande]);
        if (!$demande) {
            throw new NotFoundHttpException(
                'Demande de Diagnostic Pneu introuvable'
            );
        }

        return $this->render('pol/ddd/detailReadOnly.html.twig', [
            'demande' => $demande
        ]);
    }

    /**
     * @Route("/details-atelier/{numeroDemande}", name="demande_diagnostic_pneu_details_atelier")
     */
    public function detailAtelier(string $numeroDemande, Request $request): Response
    {
        $em = $this->getEntityManager();
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();
        $agenceService = $this->agenceServiceIpsObjet();

        // [codeAgence , codeService] Autorisé 
        $allowed = [
            ['80', 'INF'],
            ['60', 'ATE'],
        ];

        $statut = [
            $agenceService['agenceIps']->getCodeAgence(),
            $agenceService['serviceIps']->getCodeService(),
        ];

        $demande = $em->getRepository(DemandeDiagnosticPneu::class)->findOneBy(['numeroDemande' => $numeroDemande]);
        if (!$demande) {
            throw new NotFoundHttpException(
                'Demande de Diagnostic Pneu introuvable'
            );
        }

        $isAllowed = in_array($statut, $allowed, true);


        $isReadOnly =  !in_array($demande->getStatut(), [
            'a traiter atelier',
            'diag en cours',
        ], true);

        // Créer un formulaire pour les pneus
        $form = $this->getFormFactory()->createBuilder()
            ->add('diagnosticPneus', CollectionType::class, [
                'entry_type' => DiagnosticPneuDetailType::class,
                'allow_add' => false,
                'entry_options' => [
                    'disabled' => $isReadOnly,
                ],
                'allow_delete' => false,
                'data' => $demande->getDiagnosticPneus()->toArray(),
            ])
            ->add(
                'observationGlobalAtelier',
                TextareaType::class,
                [
                    'label' => 'Observation global atelier',
                    'required' => false,
                    'disabled' => $isReadOnly,
                    'data' => $demande->getObservationGlobalAtelier(),
                    'attr' => [
                        'rows' => 5,
                        'class' => 'observation global atelier'
                    ],

                ]
            )
            ->add(
                'piecesJointesAtelier',
                FileType::class,
                [
                    'label' => 'Pièces Jointes Atelier',
                    'help' => 'Formats acceptés : PDF, Images (.pdf, .jpg, .jpeg, .png) • Taille max : 5 Mo par fichier',
                    'required' => false,
                    'multiple' => true,
                    'attr' => [
                        'accept' => '.pdf, .jpg, .jpeg, .png',
                        'class' => 'form-control-file',
                        'data-max-size' => '5M',
                    ],
                    'mapped' => false,
                    'constraints' => [
                        new Callback([$this, 'validateFiles']),
                    ],
                ]
            )
            ->getForm();

        $form->handleRequest($request);


        $genererPdfDit = new GenererPdfDdd();

        $allFilled = true;
        foreach ($demande->getDiagnosticPneus() as $pneu) {
            if (!$pneu->getDiagnostic()) {
                $allFilled = false;
                break;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $action = $request->request->get('action');
            $uploadedFiles = $form->get('piecesJointesAtelier')->getData();


            // Always persist the diagnostic pneus
            foreach ($data['diagnosticPneus'] as $pneu) {
                $em->persist($pneu);
            }

            // Always update observation
            $demande->setObservationGlobalAtelier($data['observationGlobalAtelier']);

            // --- Handle button actions ---
            $demande->setStatut('diag en cours');

            // Check if all diagnostics are filled
            if ($action == "valider") {
                $demande->setStatut('traitee atelier');
                $this->traitementDeFichier($form, $demande, $genererPdfDit);
                $this->envoyerMail($demande);
            } else {
                $demande->setStatut('diag en cours');
                $this->handlePiecesJointesAtelier($uploadedFiles, $demande);
            }

            $em->flush();

            return $this->redirectToRoute('demande_diagnostic_pneu_details_atelier', [
                'numeroDemande' => $numeroDemande
            ]);
        }

        return $this->render('pol/ddd/detail.html.twig', [
            'demande' => $demande,
            'form' => $form->createView(),
            'isReadOnly' => $isReadOnly,
            'isAllowed' => $isAllowed,
            'allFilled' => $allFilled,
        ]);
    }

    /**
     * @Route("/cloturer/{numeroDemande}", name="api_demande_diagnostic_pneu_cloturer")
     */
    public function cloturer(string $numeroDemande): Response
    {

        return $this->redirectToRoute('dit_new', [
            'numeroDemandePneu' => $numeroDemande,
        ]);
    }

    /**
     * Envoie un email à l'atelier pour signaler une la validation de la diagnostic de la demande.
     */
    public function envoyerMail(DemandeDiagnosticPneu $demande): void
    {

        $mailRespAtelier = $_ENV['MAIL_TO_RESP_ATELIER'];

        $mailDemandeur = $demande->getMailDemandeur();

        $destinataires = [$mailRespAtelier];
        if (!empty($mailDemandeur)) {
            $destinataires[] = $mailDemandeur;
        }

        $service = 'Atelier Pneu';

        // Construction de l'URL de détail : BASE_PATH_COURT + chemin relatif
        $basePath = rtrim($_ENV['BASE_PATH_COURT'] ?? '', '/');

        $relativePath = 'pol/demande-diagnostic-pneu/details/' . $demande->getNumeroDemande();
        $urlDetail = $this->urlGenerique($basePath . '/' . ltrim($relativePath, '/'));

        $urlIntranet = $this->urlGenerique($basePath);

        $header = sprintf(
            '%s - DEMANDE DIAGNOSTIC PNEU : MISE À JOUR ATELIER',
            $demande->getNumeroDemande()
        );

        $variables = [
            'subject'        => $header,
            'header'         => $header,
            'message'        => 'La demande de diagnostic pneu a été mise à jour.',
            'nomDemandeur'   => $demande->getDemandeur(),
            'numeroDemande'  => $demande->getNumeroDemande(),
            'statut'         => $demande->getStatut(),
            'urlDetail'      => $urlDetail,
            'urlIntranet'    => $urlIntranet,
            'service'        => $service,
            'dateYear'       => date('Y'),
        ];


        $this->envoyerEmail([
            'to'          => $destinataires,
            'cc'          => [$_ENV['MAIL_CC_ATELIER']],
            'variables'   => $variables,
        ]);
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


    private function traitementDeFichier(
        FormInterface $form,
        DemandeDiagnosticPneu $demandeDiagnosticPneu,
        GenererPdfDdd $genererPdfDdd
    ): array {
        [
            $nomEtCheminFichiersEnregistrer,
            $nomFichierEnregistrer,
            $nomAvecCheminFichier,
            $nomFichier
        ] = $this->enregistrementFichier(
            $form,
            $demandeDiagnosticPneu->getNumeroDemande(),
            'DDD-ATE'
        );

        if (!is_array($nomEtCheminFichiersEnregistrer)) {
            $nomEtCheminFichiersEnregistrer = [];
        }

        /*
     * Récupération des pièces jointes
     */
        $piecesJointes = $demandeDiagnosticPneu->getPiecesJointes();

        if (is_string($piecesJointes)) {
            $piecesJointes = json_decode($piecesJointes, true);
        }

        if (!is_array($piecesJointes)) {
            $piecesJointes = [];
        }

        /*
     * Répertoire des pièces jointes
     */
        $basePath = rtrim($_ENV['BASE_PATH_FICHIER'], '/\\');

        $dossierPiecesJointes = $basePath
            . DIRECTORY_SEPARATOR
            . 'ddd'
            . DIRECTORY_SEPARATOR
            . $demandeDiagnosticPneu->getNumeroDemande()
            . DIRECTORY_SEPARATOR;

        /*
     * Séparation :
     * - images -> directement dans le PDF DDD
     * - autres fichiers -> fusion classique
     */
        $images = [];
        $autresFichiers = [];

        foreach ($piecesJointes as $pieceJointe) {
            if (empty($pieceJointe)) {
                continue;
            }

            $cheminPieceJointe = $dossierPiecesJointes . $pieceJointe;

            if (!file_exists($cheminPieceJointe)) {
                continue;
            }

            $extension = strtolower(
                pathinfo($cheminPieceJointe, PATHINFO_EXTENSION)
            );

            if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
                $images[] = $cheminPieceJointe;
            } else {
                $autresFichiers[] = $cheminPieceJointe;
            }
        }

        /*
     * Ajouter également les nouveaux fichiers
     * retournés par enregistrementFichier().
     */
        foreach ($nomEtCheminFichiersEnregistrer as $fichier) {
            $extension = strtolower(
                pathinfo($fichier, PATHINFO_EXTENSION)
            );

            if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
                $images[] = $fichier;
            } else {
                $autresFichiers[] = $fichier;
            }
        }

        /*
     * Génération du PDF principal avec les images
     */
        $genererPdfDdd->genererPdfDiagnosticPneu(
            $demandeDiagnosticPneu,
            $nomAvecCheminFichier,
            $images
        );

        /*
     * Traitement des autres pièces jointes
     */
        $traitementDeFichier = new TraitementDeFichier();

        if (!empty($autresFichiers)) {

            /*
         * Le PDF principal est toujours en première position
         */
            $autresFichiers = $traitementDeFichier->insertFileAtPosition(
                $autresFichiers,
                $nomAvecCheminFichier,
                0
            );

            /*
         * Conversion des autres fichiers
         */
            $fichiersConvertis = $this->ConvertirLesPdf(
                $autresFichiers
            );

            /*
         * Fusion finale
         */
            $traitementDeFichier->fusionFichers(
                $fichiersConvertis,
                $nomAvecCheminFichier
            );
        }

        return [
            $nomFichierEnregistrer,
            $nomFichier
        ];
    }

    private function enregistrementFichier(
        FormInterface $form,
        string $numDemande,
        string $identifiant,
        bool $withSuffix = false // optionnel
    ): array {
        $nameGenerator = new DddNameFileService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/ddd/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $path = $cheminBaseUpload . $numDemande . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer] = $uploader->getFichiers($form, [
            'repertoire' => $path,
            'generer_nom_callback' => function (
                UploadedFile $file,
                int $index
            ) use ($numDemande, $identifiant, $nameGenerator) {
                return $nameGenerator->generateDddFileName($file, $numDemande, $identifiant, $index);
            }
        ]);

        // Nom du fichier principal
        $nomFichier = $nameGenerator->generateDddNamePrincipal($numDemande, $identifiant, $withSuffix);
        $nomAvecCheminFichier = $path . $nomFichier;

        return [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }

    public function validateFiles($files, ExecutionContextInterface $context)
    {
        $maxSize = '5M';
        $mimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
        ];

        if ($files) {
            foreach ($files as $file) {
                $fileConstraint = new File([
                    'maxSize' => $maxSize,
                    'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser 5 Mo.',
                    'mimeTypes' => $mimeTypes,
                    'mimeTypesMessage' => 'Veuillez télécharger un fichier valide.',
                ]);

                $violations = $context->getValidator()->validate($file, $fileConstraint);

                if (count($violations) > 0) {
                    foreach ($violations as $violation) {
                        $context->buildViolation($violation->getMessage())
                            ->addViolation();
                    }
                }
            }
        }
    }

    /**
     * Gère l'upload des pièces jointes atelier.
     */
    private function handlePiecesJointesAtelier(array $files, DemandeDiagnosticPneu $demande): void
    {
        $numDa = $demande->getNumeroDemande();
        $basePath = rtrim($_ENV['BASE_PATH_FICHIER'], '/') . '/ddd/';
        $dossier = $basePath . $numDa . '/';

        if (!is_dir($dossier)) {
            mkdir($dossier, 0777, true);
        }

        // Récupère la liste existante (peut être un tableau ou une chaîne JSON)
        $existants = $demande->getPiecesJointesAtelier();
        if (is_string($existants)) {
            $existants = json_decode($existants, true);
        }
        if (!is_array($existants)) {
            $existants = [];
        }

        $nouveauxNoms = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $nomOriginal = $file->getClientOriginalName();
            $nomSansExtension = pathinfo($nomOriginal, PATHINFO_FILENAME);
            $extension = $file->guessExtension() ?: pathinfo($nomOriginal, PATHINFO_EXTENSION);

            // Génère le nom qui serait utilisé pour ce fichier
            $nomUnique = $numDa . '_ATE__' . $nomSansExtension . '.' . $extension;

            // Vérifie si ce nom exact existe déjà dans la liste existante ou dans les nouveaux ajoutés
            if (in_array($nomUnique, $existants, true) || in_array($nomUnique, $nouveauxNoms, true)) {
                continue; // Ignorer ce fichier (déjà présent)
            }

            try {
                $this->traitementDeFichier->upload($file, $dossier, $nomUnique);
            } catch (\Exception $e) {
                throw new \RuntimeException("Erreur lors de l'upload du fichier : " . $e->getMessage());
            }

            $nouveauxNoms[] = $nomUnique;
        }

        // Fusionner les anciens et les nouveaux (uniquement ceux qui n'existaient pas)
        $tous = array_merge($existants, $nouveauxNoms);
        $demande->setPiecesJointesAtelier($tous);
    }
}
