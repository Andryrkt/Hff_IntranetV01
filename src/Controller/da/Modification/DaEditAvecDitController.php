<?php

namespace App\Controller\da\Modification;

use App\Constants\da\StatutDaConstant;
use App\Controller\Controller;
use App\Repository\da\DemandeApproRepository;
use App\Service\da\EmailDaService;
use App\Entity\da\DaObservation;
use App\Service\da\DaEditionService;
use App\Entity\da\DemandeAppro;
use App\Service\Admin\UrlIdCipher;
use App\Form\da\DemandeApproFormType;
use App\Service\da\DaObservationService;
use App\Service\da\DaAfficherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * @Route("/demande-appro")
 */
class DaEditAvecDitController extends Controller
{
    private DemandeApproRepository $demandeApproRepository;
    private EmailDaService $emailDaService;

    private UrlIdCipher $urlIdCipher;
    private DaObservationService $daObservationService;
    private DaAfficherService $daAfficherService;
    private DaEditionService $daEditionService;

    public function __construct(
        EntityManagerInterface $em,
        DaObservationService $daObservationService,
        DaAfficherService $daAfficherService,
        DaEditionService $daEditionService,
        EmailDaService $emailDaService
    ) {
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->emailDaService = $emailDaService;
        $this->urlIdCipher = new UrlIdCipher;
        $this->daObservationService = $daObservationService;
        $this->daAfficherService = $daAfficherService;
        $this->daEditionService = $daEditionService;
    }

    /**
     * @Route("/edit-avec-dit/{token}", name="da_edit_avec_dit")
     */
    public function edit(string $token, Request $request)
    {
        $id = $this->urlIdCipher->decryptInt($token);

        if (empty($id) && $id !== 0) throw new ResourceNotFoundException();

        /** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
        $demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
        $numDa = $demandeAppro->getNumeroDemandeAppro();

        $ancienDals = $this->daEditionService->getAncienDAL($demandeAppro);

        $form = $this->getFormFactory()->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();

        $this->traitementForm($form, $request, $ancienDals);

        $observations = $this->getEntityManager()->getRepository(DaObservation::class)->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'DESC']);

        return $this->render('da/edit-avec-dit.html.twig', [
            'form'         => $form->createView(),
            'observations' => $observations,
            'peutModifier' => $this->daEditionService->peutModifier($demandeAppro->getStatutDal(), $this->estAtelier()),
            'numDa'        => $numDa,
        ]);
    }

    private function traitementForm($form, Request $request, iterable $ancienDals): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeAppro = $form->getData();
            $numDa = $demandeAppro->getNumeroDemandeAppro();
            // suivi étape par étape de la modification (admin > historique > suivi des opérations)
            $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DA_MODIFICATION_AVEC_DIT, $numDa);

            $suivi->etape('Lignes de DA et base de données', function () use ($demandeAppro, $form) {
                $this->daEditionService->modificationDa($demandeAppro, $this->lignesFormulaire($form->get('DAL')), StatutDaConstant::STATUT_SOUMIS_APPRO);
            });
            if ($demandeAppro->getObservation() !== null) {
                $suivi->etape('Observation', function () use ($numDa, $demandeAppro) {
                    $this->daObservationService->insertionObservation($numDa, $demandeAppro->getObservation());
                });
            }

            $suivi->etape('Mise à jour da_afficher', function () use ($numDa) {
                $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa); // ajout dans la table DaAfficher si le statut a changé
            });

            $suivi->etape('Mail de modification', function () use ($demandeAppro, $ancienDals) {
                $this->emailDaService->envoyerMailModificationDa($demandeAppro, $ancienDals);
            });

            //notification
            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
        }
    }

    /** Valeurs du formulaire des lignes DAL, passées au service (qui ne connaît pas les formulaires) */
    private function lignesFormulaire($formDAL): array
    {
        $lignes = [];
        foreach ($formDAL as $subFormDAL) {
            $lignes[] = [
                'dal'               => $subFormDAL->getData(),
                'filesToDelete'     => $subFormDAL->get('filesToDelete')->getData(),
                'existingFileNames' => $subFormDAL->get('existingFileNames')->getData(),
                'newFiles'          => $subFormDAL->get('fileNames')->getData(),
            ];
        }
        return $lignes;
    }
}
