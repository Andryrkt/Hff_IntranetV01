<?php

namespace App\Controller\pol\ddd;

use App\Controller\Controller;
use App\Dto\ddd\DemandeDiagnosticPneuDto;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Form\pol\ddd\DiagnosticPneuDetailType;
use App\Form\pol\ddd\DiagnosticPneuType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pol/demande-diagnostic-pneu")
 */
class DemandeDiagnosticPneuDetailController extends Controller
{
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

        $isReadOnly = !$isAllowed || $demande->getStatut() !== 'a traiter atelier';

        // Créer un formulaire pour les pneus
        $form = $this->getFormFactory()->createBuilder()
            ->add('diagnosticPneus', CollectionType::class, [
                'entry_type' => DiagnosticPneuDetailType::class,
                'allow_add' => false,
                'entry_options' => [
                    'disabled' => $isReadOnly,
                ],
                'allow_delete' => false,
                'required' => true,
                'data' => $demande->getDiagnosticPneus()->toArray(),
            ])
            ->getForm();

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            foreach ($data['diagnosticPneus'] as $pneu) {
                $em->persist($pneu);
            }

            // Optional: only close if all diagnostics are filled
            $allFilled = true;
            foreach ($data['diagnosticPneus'] as $pneu) {
                if (!$pneu->getDiagnostic()) { // adjust to your field
                    $allFilled = false;
                    break;
                }
            }

            if ($allFilled && $demande->getStatut() !== 'cloturee') {
                $demande->setStatut('traitee atelier');
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
}
