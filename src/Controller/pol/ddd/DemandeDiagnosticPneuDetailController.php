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
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();


            foreach ($data['diagnosticPneus'] as $pneu) {
                $em->persist($pneu);
            }



            foreach ($data['diagnosticPneus'] as $pneu) {
                if (!$pneu->getDiagnostic()) { // adjust to your field
                    $allFilled = false;
                    break;
                }
            }

            if ($allFilled) {
                if ($demande->getStatut() !== 'cloturee') {
                    $demande->setObservationGlobalAtelier($data['observationGlobalAtelier']);
                    $demande->setStatut('traitee atelier');
                    $this->envoyerMailAtelier($demande);
                }
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
            '%s - DEMANDE DIAGNOSTIC PNEU : <span class="commente">NOUVELLE DEMANDE</span>',
            $demande->getNumeroDemande()
        );


        $variables = [
            'subject'        => 'Nouvelle demande de diagnostic pneu',
            'header'         => $header,
            'nomDemandeur'   => $demande->getDemandeur(),
            'numeroDemande'  => $demande->getNumeroDemande(),
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
