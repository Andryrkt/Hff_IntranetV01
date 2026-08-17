<?php

namespace App\Controller\pol\ddd;

use App\Controller\Controller;
use App\Controller\Traits\lienGenerique;
use App\Dto\ddd\DemandeDiagnosticPneuDto;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Form\pol\ddd\DiagnosticPneuDetailType;
use App\Form\pol\ddd\DiagnosticPneuType;
use App\Service\dit\fichier\DitNameFileService;
use App\Service\EmailService;
use App\Service\fichier\TraitementDeFichier;
use App\Service\fichier\UploderFileService;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\PdfConversionTrait;

use App\Service\genererPdf\pol\ddd\GenererPdfDdd;
use App\Service\pol\ddd\fichier\DddNameFileService;

/**
 * @Route("/pol/demande-diagnostic-pneu")
 */
class DemandeDiagnosticPneuDetailController extends Controller
{
    use lienGenerique;
    use PdfConversionTrait;
    /**
     * @Route("/details/{numeroDemande}", name="demande_diagnostic_pneu_details")
     */
    public function detail(string $numeroDemande, Request $request): Response
    {
        $em = $this->getEntityManager();
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();
        $agenceService = $this->agenceServiceIpsObjet();

        // [codeAgence , codeService] Autorisé 
        $allowed = [
            ['80', 'INF'],
            ['01', 'ATE'],
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

        $isReadOnly = !$isAllowed
            || !in_array($demande->getStatut(), [
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
            ->getForm();

        $form->handleRequest($request);


        $genererPdfDit = new GenererPdfDdd();
        [$nomFichierEnregistrer, $nomFichier]  = $this->traitementDeFichier($form, $demande, $genererPdfDit);
        dump("Tonga eto");
        $allFilled = true;
        foreach ($demande->getDiagnosticPneus() as $pneu) {
            if (!$pneu->getDiagnostic()) {
                $allFilled = false;
                break;
            }
        }



        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $action =  $request->request->get("action");
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
                $this->envoyerMailAtelier($demande);
            } else {
                $demande->setStatut('diag en cours');
            }


            $em->flush();

            return $this->redirectToRoute('demande_diagnostic_pneu_details', [
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
     * Envoie un email à l'atelier pour signaler une nouvelle demande.
     */
    public function envoyerMailAtelier(DemandeDiagnosticPneu $demande): void
    {
        $destinataire = $_ENV['MAIL_TO_ATELIER'];
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
            'subject'      => $header,
            'header'         => $header,
            'message'       => 'Votre demande de diagnostic pneu a été mise à jour.',
            'nomDemandeur'   => $demande->getDemandeur(),
            'numeroDemande'  => $demande->getNumeroDemande(),
            'statut'        => $demande->getStatut(),
            'urlDetail'      => $urlDetail,
            'urlIntranet'    => $urlIntranet,
            'service'        => $service,
            'dateYear'       => date('Y'),
        ];


        $this->envoyerEmail([
            'to'          => $destinataire,
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
        // 1. Enregistrement des fichiers (pièces jointes)
        [$nomEtCheminFichiersEnregistrer, $nomFichierEnregistrer, $nomAvecCheminFichier, $nomFichier] =
            $this->enregistrementFichier(
                $form,
                $demandeDiagnosticPneu->getNumeroDemande(),
                'DDD', // ou un identifiant fixe pour éviter les caractères spéciaux
            );

        // 2. Génération du PDF principal (sans historique)
        $genererPdfDdd->genererPdfDiagnosticPneu($demandeDiagnosticPneu, $nomAvecCheminFichier);

        // 3. Fusion avec les pièces jointes uniquement s'il y en a
        $traitementDeFichier = new TraitementDeFichier();
        if (!empty($nomEtCheminFichiersEnregistrer)) {
            // Insère la page de garde en première position
            $nomEtCheminFichiersEnregistrer = $traitementDeFichier->insertFileAtPosition(
                $nomEtCheminFichiersEnregistrer,
                $nomAvecCheminFichier,
                0
            );

            // Convertit les fichiers non-PDF en PDF
            $nomEtCheminFichierConvertie = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);

            // Fusionne tous les PDF en un seul fichier final
            $traitementDeFichier->fusionFichers($nomEtCheminFichierConvertie, $nomAvecCheminFichier);
        }

        // 4. On retourne les noms des fichiers enregistrés (pour suivi)
        return [$nomFichierEnregistrer, $nomFichier];
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
}
