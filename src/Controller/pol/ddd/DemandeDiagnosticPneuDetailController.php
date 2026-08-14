<?php

namespace App\Controller\pol\ddd;

use App\Controller\Controller;
use App\Controller\Traits\lienGenerique;
use App\Dto\ddd\DemandeDiagnosticPneuDto;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Form\pol\ddd\DiagnosticPneuDetailType;
use App\Form\pol\ddd\DiagnosticPneuType;
use App\Service\EmailService;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pol/demande-diagnostic-pneu")
 */
class DemandeDiagnosticPneuDetailController extends Controller
{
    use lienGenerique;
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
            'subject'      => 'Mise à jour et état d’avancement de votre demande de diagnostic pneu',
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
}
