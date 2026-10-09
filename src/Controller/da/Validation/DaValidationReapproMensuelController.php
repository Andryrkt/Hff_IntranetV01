<?php

namespace App\Controller\da\Validation;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Form\da\DaObservationType;
use App\Form\da\DaObservationValidationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\da\DemandeApproRepository;
use App\Service\da\EmailDaService;
use App\Service\da\DaValidationService;
use App\Service\da\DaSoumissionValidationService;
use App\Service\da\DaConsumptionHistory;
use App\Service\da\DaService;
use App\Service\da\DocRattacheService;
use App\Service\Admin\UrlIdCipher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @Route("/demande-appro")
 */
class DaValidationReapproMensuelController extends Controller
{
    private DemandeApproRepository $demandeApproRepository;
    private EmailDaService $emailDaService;

    private DocRattacheService $docRattacheService;
    private UrlIdCipher $urlIdCipher;
    private DaService $daService;
    private DaValidationService $daValidationService;
    private DaSoumissionValidationService $daSoumissionValidationService;

    public function __construct(DocRattacheService $docRattacheService, DaService $daService, DaValidationService $daValidationService, DaSoumissionValidationService $daSoumissionValidationService)
    {
        $em = $this->getEntityManager();
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->emailDaService = new EmailDaService($this->getTwig(), $this->getUrlGenerator());
        $this->daValidationService = $daValidationService;
        $this->daSoumissionValidationService = $daSoumissionValidationService;
        $this->docRattacheService = $docRattacheService;
        $this->daService = $daService;
        $this->urlIdCipher = new UrlIdCipher;
    }

    /**
     * @Route("/validation-reappro-mensuel/{token}", name="da_validate_reappro_mensuel")
     */
    public function validationDaReapproMensuel(string $token, Request $request)
    {
        $id = $this->urlIdCipher->decryptInt($token);

        if (empty($id) && $id !== 0) throw new ResourceNotFoundException();

        $demandeAppro = $this->demandeApproRepository->find($id);

        $daObservation = new DaObservation();

        $formReappro = $this->getFormFactory()->createBuilder(DaObservationValidationType::class, $daObservation)->getForm();
        $formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

        $daConsumptionHistory = new DaConsumptionHistory();
        $dateRange = $daConsumptionHistory->getLast13MonthsDateRange();
        $monthsList = $daConsumptionHistory->getMonthsList($dateRange['start'], $dateRange['end']);
        $dataHistoriqueConsommation = $daConsumptionHistory->getHistoriqueConsommation($demandeAppro, $dateRange, $monthsList);

        $observations = $this->daService->getObservations($demandeAppro->getNumeroDemandeAppro());

        //========================================== Traitement du formulaire en général ===================================================//
        $this->traitementFormulaire($formReappro, $formObservation, $request, $demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);
        //==================================================================================================================================//

        $fichiers = $this->docRattacheService->getAllAttachedFiles($demandeAppro);

        return $this->render("da/validation-reappro.html.twig", [
            'demandeAppro'      => $demandeAppro,
            'urlRetour'         => $this->getUrlGenerator()->generate('list_da'),
            'titreBoutonRetour' => 'Liste des demandes d’achats',
            'numDa'           => $demandeAppro->getNumeroDemandeAppro(),
            'fichiers'        => $fichiers,
            'codeCentrale'    => in_array($demandeAppro->getAgenceEmetteur()->getCodeAgence(), ['90', '91', '92']),
            'formReappro'     => $formReappro->createView(),
            'formObservation' => $formObservation->createView(),
            'observations'    => $observations,
            'dataHistorique'  => $dataHistoriqueConsommation,
            'monthsList'      => $monthsList,
            'connectedUser'   => $this->getUser(),
        ]);
    }

    private function traitementFormulaire($formReappro, $formObservation, Request $request, DemandeAppro $demandeAppro, iterable $observations, array $monthsList, array $dataHistoriqueConsommation)
    {
        $formReappro->handleRequest($request);

        if ($formReappro->isSubmitted() && $formReappro->isValid()) {
            // ✅ Récupérer les valeurs des champs caché
            $observation = $formReappro->getData()->getObservation();

            if ($observation) $this->daService->insertionObservation($demandeAppro->getNumeroDemandeAppro(), $observation, $this->getUserName());

            if ($request->request->has('refuser')) {
                $this->daValidationService->refuserDemandeReappro($demandeAppro);

                $this->emailDaService->envoyerMailValidationReappro($demandeAppro, $observation ?? '-', $this->getUser(), false);

                $notification = [
                    'type'    => 'success',
                    'message' => 'La demande de réappro a été refusé avec succès.',
                ];
            } elseif ($request->request->has('valider')) {
                $this->daValidationService->validerDemandeReappro($demandeAppro);
                $this->daSoumissionValidationService->creationPDFReappro($demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);
                $this->daSoumissionValidationService->copyPDFToDW($demandeAppro->getNumeroDemandeAppro());
                $this->daSoumissionValidationService->ajouterDansDaSoumisAValidation($demandeAppro);

                $this->emailDaService->envoyerMailValidationReappro($demandeAppro, $observation ?? '-', $this->getUser());

                $notification = [
                    'type'    => 'success',
                    'message' => 'La demande de réappro a été validé avec succès.',
                ];
            }

            $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
            $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
        }

        $formObservation->handleRequest($request);

        if ($formObservation->isSubmitted() && $formObservation->isValid()) {
            /** @var DaObservation $daObservation daObservation correspondant au donnée du formObservation */
            $daObservation = $formObservation->getData();

            $this->traitementEnvoiObservation($daObservation, $demandeAppro);
        }
    }

    private function traitementEnvoiObservation(DaObservation $daObservation, DemandeAppro $demandeAppro)
    {
        $this->daService->insertionObservation($demandeAppro->getNumeroDemandeAppro(), $daObservation->getObservation(), $this->getUserName(), $daObservation->getFileNames());

        $this->emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $this->getUser(), $this->estAppro());

        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre observation a été enregistré avec succès.']);
        return $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }
}
