<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Service\da\DaCreationService;
use App\Service\da\DaEnregistrementService;
use Doctrine\ORM\EntityManagerInterface;
use App\Constants\admin\ApplicationConstant;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\da\DemandeApproReapproMensuelFormType;

/**
 * @Route("/demande-appro")
 */
class DaNewReApproMensuelController extends Controller
{
    private DemandeApproRepository $demandeApproRepository;
    private DaCreationService $daCreationService;
    private DaEnregistrementService $daEnregistrementService;

    public function __construct(
        EntityManagerInterface $em,
        DaCreationService $daCreationService,
        DaEnregistrementService $daEnregistrementService
    ) {
        $this->daCreationService       = $daCreationService;
        $this->daEnregistrementService = $daEnregistrementService;
        $this->demandeApproRepository  = $em->getRepository(DemandeAppro::class);
    }

    /**
     * @Route("/new-da-reappro-mensuel/{id<\d+>}", name="da_new_reappro_mensuel")
     */
    public function newDAReapproMensuel(int $id, Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        $demandeAppro     = $id === 0 ? $this->daCreationService->initialisationDemandeApproReapproMensuel($codeSociete) : $this->demandeApproRepository->find($id);
        $this->daCreationService->generateDemandApproLinesFromReappros($demandeAppro);

        $form = $this->getFormFactory()->createBuilder(DemandeApproReapproMensuelFormType::class, $demandeAppro, [
            'em' => $this->getEntityManager()
        ])->getForm();
        $this->traitementFormReappro($form, $request);

        return $this->render('da/new-da-reappro-mensuel.html.twig', [
            'form'         => $form->createView(),
            'codeCentrale' => $this->estAdmin() || $this->estEnergie(),
        ]);
    }

    private function traitementFormReappro($form, Request $request): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeAppro $demandeAppro */
            $demandeAppro = $form->getData();

            if ($demandeAppro->getDateFinSouhaite() < new \DateTime()) {
                $this->getSessionService()->set('notification', ['type' => 'error', 'message' => 'La date fin souhaitée ne peut pas être antérieure à la date du jour.']);
            } else {
                $firstCreation = $demandeAppro->getNumeroDemandeAppro() === null;
                $numDa = $firstCreation ? $this->autoDecrement(ApplicationConstant::CODE_DAP) : $demandeAppro->getNumeroDemandeAppro();

                $notification = $this->daEnregistrementService->enregistrerDaReapproMensuel($demandeAppro, $numDa, $firstCreation, $this->getButtonName($request), $this->getUserName(), $this->getUser());

                $this->getSessionService()->set('notification', $notification);
                $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
            }
        }
    }

    /** Nom du bouton cliqué : enregistrerBrouillon, soumissionAppro ou chaine vide */
    private function getButtonName(Request $request): string
    {
        foreach (['enregistrerBrouillon', 'soumissionAppro'] as $button) {
            if ($request->request->has($button)) return $button;
        }
        return '';
    }
}
