<?php

namespace App\Controller\da\Creation;

use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Entity\da\DemandeApproParent;
use App\Form\da\DemandeApproAchatFormType;
use App\Service\da\DaCreationService;
use App\Service\da\DaEnregistrementService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/** @Route("/demande-appro") */
class DaNewAchatController extends Controller
{
    private DaCreationService $daCreationService;
    private DaEnregistrementService $daEnregistrementService;

    public function __construct(DaCreationService $daCreationService, DaEnregistrementService $daEnregistrementService)
    {
        $this->daCreationService = $daCreationService;
        $this->daEnregistrementService = $daEnregistrementService;
    }

    /**
     * @Route("/new-da-achat/{id<\d+>}", name="da_new_achat")
     */
    public function newDaAchat(int $id, Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        if ($id === 0) {
            $demandeApproParent = $this->daCreationService->initialisationDemandeApproAchat($codeSociete);
        } else {
            $demandeApproParentRepository = $this->getEntityManager()->getRepository(DemandeApproParent::class);
            $demandeApproParent = $demandeApproParentRepository->find($id);
        }

        $form = $this->getFormFactory()->createBuilder(DemandeApproAchatFormType::class, $demandeApproParent)->getForm();
        $this->traitementFormAchat($form, $request);

        return $this->render('da/new-da-achat.html.twig', [
            'form'        => $form->createView(),
            'codeCentrale' => $this->estAdmin() || $this->estEnergie(),
        ]);
    }

    private function traitementFormAchat(FormInterface $form, Request $request): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeApproParent $demandeApproParent */
            $demandeApproParent = $form->getData();
            // suivi étape par étape de l'enregistrement (admin > historique > suivi des opérations)
            $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DA_CREATION_ACHAT, $demandeApproParent->getNumeroDemandeAppro());

            if ($demandeApproParent->getDateFinSouhaite() < new \DateTime()) {
                $message = 'La date fin souhaitée ne peut pas être antérieure à la date du jour';
                $this->getSessionService()->set('notification', ['type' => 'error', 'message' => $message]);
                $suivi->echecOperation($message);
            } else {
                $firstCreation = $demandeApproParent->getNumeroDemandeAppro() === null;
                $numDa = $firstCreation
                    ? $suivi->etape('Numérotation', function () {
                        return $this->autoDecrement(ApplicationConstant::CODE_DAP);
                    })
                    : $demandeApproParent->getNumeroDemandeAppro();
                $suivi->setNumeroDocument($numDa);

                $notification = $suivi->etape('Enregistrement de la DA', function () use ($demandeApproParent, $form, $numDa, $firstCreation, $request) {
                    return $this->daEnregistrementService->enregistrerDaParent($demandeApproParent, $form, $numDa, $firstCreation, $this->getButtonName($request));
                });

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
