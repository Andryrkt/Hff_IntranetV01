<?php

namespace App\Controller\da\Validation;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\admin\Application;
use App\Form\da\DaObservationType;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\validation\DaValidationReapproTrait;
use App\Entity\da\DemandeApproL;
use App\Form\da\DaObservationValidationType;

/**
 * @Route("/demande-appro")
 */
class DaValidationReapproController extends Controller
{
    use DaAfficherTrait;
    use AutorisationTrait;
    use DaValidationReapproTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initDaValidationReapproTrait();
    }

    /**
     * @Route("/validation/{id}", name="da_validate_reappro")
     */
    public function validationDaReappro($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation accès */

        $da = $this->demandeApproRepository->findAvecDernieresDALetLR($id);

        $daObservation = new DaObservation();

        $formReappro = $this->getFormFactory()->createBuilder(DaObservationValidationType::class, $daObservation)->getForm();
        $formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $da->getDaTypeId()])->getForm();

        $dateRange = $this->getLast12MonthsRange();
        $monthsList = $this->getMonthsList($dateRange['start'], $dateRange['end']);
        $dataHistoriqueConsommation = $this->getHistoriqueConsommation($da, $dateRange, $monthsList);
        $observations = $this->daObservationRepository->findBy(['numDa' => $da->getNumeroDemandeAppro()]);

        //========================================== Traitement du formulaire en général ===================================================//
        $this->traitementFormulaire($formReappro, $formObservation, $request, $da, $observations, $monthsList, $dataHistoriqueConsommation);
        // =================================================================================================================================//

        return $this->render("da/validation-reappro.html.twig", [
            'da'              => $da,
            'numDa'           => $da->getNumeroDemandeAppro(),
            'formReappro'     => $formReappro->createView(),
            'formObservation' => $formObservation->createView(),
            'observations'    => $observations,
            'dataHistorique'  => $dataHistoriqueConsommation,
            'monthsList'      => $monthsList,
            'connectedUser'   => $this->getUser(),
            'propValTemplate' => 'proposition-validation-direct',
            'dossierJS'       => 'propositionDirect',
        ]);
    }

    private function traitementFormulaire($formReappro, $formObservation, Request $request, DemandeAppro $da, iterable $observations, array $monthsList, array $dataHistoriqueConsommation)
    {
        $formReappro->handleRequest($request);

        if ($formReappro->isSubmitted() && $formReappro->isValid()) {
            // ✅ Récupérer les valeurs des champs caché
            $observation = $formReappro->getData()->getObservation();

            if ($observation) $this->insertionObservation($observation, $da);

            if ($request->request->has('refuser')) {
                $this->refuserDemande($da);
            } elseif ($request->request->has('valider')) {
                $this->validerDemande($da);
                $this->creationPDFReappro($da, $observations, $monthsList, $dataHistoriqueConsommation);
                $this->copyPDFToDW($da->getNumeroDemandeAppro());
                $this->ajouterDansDaSoumisAValidation($da);
            }
        }

        $formObservation->handleRequest($request);

        if ($formObservation->isSubmitted() && $formObservation->isValid()) {
            /** @var DaObservation $daObservation daObservation correspondant au donnée du formObservation */
            $daObservation = $formObservation->getData();

            $this->traitementEnvoiObservation($daObservation, $da);
        }
    }

    private function traitementEnvoiObservation(DaObservation $daObservation, DemandeAppro $demandeAppro)
    {
        $this->insertionObservation($daObservation->getObservation(), $demandeAppro);

        $notification = [
            'type'    => 'success',
            'message' => 'Votre observation a été enregistré avec succès.',
        ];

        /** ENVOIE D'EMAIL à l'APPRO pour l'observation */
        // $service = $this->estUserDansServiceAtelier() ? 'atelier' : ($this->estUserDansServiceAppro() ? 'appro' : '');
        // $this->emailDaService->envoyerMailObservationDaAvecDit($demandeAppro, [
        //     'service'       => $service,
        //     'observation'   => $daObservation->getObservation(),
        //     'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        // ]);

        $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        return $this->redirectToRoute("list_da");
    }
}
