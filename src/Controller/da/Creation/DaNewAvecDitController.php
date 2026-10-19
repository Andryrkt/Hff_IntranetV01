<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Service\da\DaCreationService;
use App\Service\da\DaEnregistrementService;
use App\Form\da\DemandeApproFormType;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;
use App\Constants\admin\ApplicationConstant;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * @Route("/demande-appro")
 */
class DaNewAvecDitController extends Controller
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
     * @Route("/new-avec-dit/{daId<\d+>}/{ditId}", name="da_new_avec_dit")
     */
    public function new(int $daId, int $ditId, Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        /**
         * @var DemandeIntervention $dit DIT correspondant à l'id $ditId
         */
        $dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->find($ditId);

        if (!$dit) throw new ResourceNotFoundException();

        $demandeAppro = $daId === 0 ? $this->daCreationService->initialisationDemandeApproAvecDit($dit) : $this->demandeApproRepository->find($daId);
        if (!$demandeAppro) throw new ResourceNotFoundException();

        $demandeAppro
            ->setDit($dit)
            ->setCodeSociete($codeSociete)
            ->setDateFinSouhaite($this->daCreationService->dateLivraisonPrevueDA($dit->getNumeroDemandeIntervention(), $dit->getIdNiveauUrgence()->getDescription()))
        ;

        $form = $this->getFormFactory()->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();
        $this->traitementForm($form, $request, $demandeAppro, $dit);

        return $this->render('da/new-avec-dit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementForm($form, Request $request, DemandeAppro $demandeAppro, DemandeIntervention $dit): void
    {
        $dateFinSouhaite = $demandeAppro->getDateFinSouhaite();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeAppro $demandeAppro */
            $demandeAppro = $form->getData();
            // suivi étape par étape de l'enregistrement (admin > historique > suivi des opérations)
            $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DA_CREATION_AVEC_DIT, $demandeAppro->getNumeroDemandeAppro());

            if ($demandeAppro->getDateFinSouhaite() < $dateFinSouhaite) {
                $message = 'La date fin souhaitée ne peut pas être antérieure à la date initiale prévue (' . $dateFinSouhaite->format('d/m/Y') . ')';
                $this->getSessionService()->set('notification', ['type' => 'error', 'message' => $message]);
                $suivi->echecOperation($message);
            } else {
                $firstCreation = $demandeAppro->getNumeroDemandeAppro() === null;
                $numDa = $firstCreation
                    ? $suivi->etape('Numérotation', function () {
                        return $this->autoDecrement(ApplicationConstant::CODE_DAP);
                    })
                    : $demandeAppro->getNumeroDemandeAppro();
                $suivi->setNumeroDocument($numDa);

                $notification = $suivi->etape('Enregistrement de la DA', function () use ($demandeAppro, $form, $dit, $numDa, $firstCreation, $request) {
                    return $this->daEnregistrementService->enregistrerDaAvecDit($demandeAppro, $form, $dit, $numDa, $firstCreation, $this->getButtonName($request));
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
