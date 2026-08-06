<?php

namespace App\Controller\pol\ddd;

use App\Controller\Controller;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Form\pol\ddd\DiagnosticPneuDetailType;
use App\Form\pol\ddd\DiagnosticPneuType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $demande = $em->getRepository(DemandeDiagnosticPneu::class)->findOneBy(['numeroDemande' => $numeroDemande]);
        // if (!$demande) {
        //     throw $this->createNotFoundException("Demande introuvable");
        // }

        // Créer un formulaire pour les pneus
        $form = $this->getFormFactory()->createBuilder()
            ->add('diagnosticPneus', CollectionType::class, [
                'entry_type' => DiagnosticPneuDetailType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'data' => $demande->getDiagnosticPneus()->toArray(),
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $pneusModifies = $data['diagnosticPneus'];
            // Persister les modifications
            foreach ($pneusModifies as $pneu) {
                $em->persist($pneu);
            }
            $em->flush();

            // return $this->redirectToRoute('demande_diagnostic_pneu_details', ['numeroDemande' => $numeroDemande]);
        }

        return $this->render('pol/ddd/detail.html.twig', [
            'demande' => $demande,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cloturer/{numeroDemande}", name="api_demande_diagnostic_pneu_cloturer")
     */
    public function cloturer(string $numeroDemande): Response
    {
        $em = $this->getEntityManager();
        $demande = $em->getRepository(DemandeDiagnosticPneu::class)->findOneBy(['numeroDemande' => $numeroDemande]);

        $demande->setStatut('cloturee');
        $em->flush();
        return $this->redirectToRoute('demande_diagnostic_pneu_liste');
    }
}
