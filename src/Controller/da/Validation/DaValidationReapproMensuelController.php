<?php

namespace App\Controller\da\Validation;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Form\da\DaObservationType;
use App\Form\da\DaObservationValidationType;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\validation\DaValidationReapproTrait;
use App\Service\da\DaConsumptionHistory;
use App\Service\da\DocRattacheService;

/**
 * @Route("/demande-appro")
 */
class DaValidationReapproMensuelController extends Controller
{
    use DaAfficherTrait;
    use DaValidationReapproTrait;

    private DocRattacheService $docRattacheService;

    public function __construct(DocRattacheService $docRattacheService)
    {
        parent::__construct();

        $this->initDaValidationReapproTrait();
        $this->docRattacheService = $docRattacheService;
    }

    /**
     * @Route("/validation-reappro-mensuel/{id}", name="da_validate_reappro_mensuel")
     */
    public function validationDaReapproMensuel($id, Request $request)
    {
        $demandeAppro = $this->demandeApproRepository->find($id);

        $daObservation = new DaObservation();

        $formReappro = $this->getFormFactory()->createBuilder(DaObservationValidationType::class, $daObservation)->getForm();
        $formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $demandeAppro->getDaTypeId()])->getForm();

        $daConsumptionHistory = new DaConsumptionHistory();
        $dateRange = $daConsumptionHistory->getLast12MonthsRange();
        $monthsList = $daConsumptionHistory->getMonthsList($dateRange['start'], $dateRange['end']);
        $dataHistoriqueConsommation = $daConsumptionHistory->getHistoriqueConsommation($demandeAppro, $dateRange, $monthsList);

        $observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'ASC']);

        //========================================== Traitement du formulaire en général ===================================================//
        $this->traitementFormulaire($formReappro, $formObservation, $request, $demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);
        //==================================================================================================================================//

        $fichiers = $this->docRattacheService->getAllAttachedFiles($demandeAppro);

        return $this->render("da/validation-reappro.html.twig", [
            'demandeAppro'    => $demandeAppro,
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

            if ($observation) $this->insertionObservation($demandeAppro->getNumeroDemandeAppro(), $observation);

            if ($request->request->has('refuser')) {
                $this->refuserDemande($demandeAppro);

                $this->emailDaService->envoyerMailValidationReappro($demandeAppro, $observation ?? '-', $this->getUser(), false);

                $notification = [
                    'type'    => 'success',
                    'message' => 'La demande de réappro a été refusé avec succès.',
                ];
            } elseif ($request->request->has('valider')) {
                $this->validerDemande($demandeAppro);
                $this->creationPDFReappro($demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);
                $this->copyPDFToDW($demandeAppro->getNumeroDemandeAppro());
                $this->ajouterDansDaSoumisAValidation($demandeAppro);

                $this->emailDaService->envoyerMailValidationReappro($demandeAppro, $observation ?? '-', $this->getUser());

                $notification = [
                    'type'    => 'success',
                    'message' => 'La demande de réappro a été validé avec succès.',
                ];
            }

            $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
            $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 1, 'page' => 1]);
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
        $this->insertionObservation($demandeAppro->getNumeroDemandeAppro(), $daObservation->getObservation(), $daObservation->getFileNames());

        $this->emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $this->getUser(), $this->estAppro());

        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre observation a été enregistré avec succès.']);
        return $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 1, 'page' => 1]);
    }
}
