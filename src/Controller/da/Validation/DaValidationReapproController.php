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
use App\Entity\admin\utilisateur\Role;
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
     * @Route("/validation-reappro/{id}", name="da_validate_reappro")
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
        //==================================================================================================================================//

        return $this->render("da/validation-reappro.html.twig", [
            'demandeAppro'    => $da,
            'numDa'           => $da->getNumeroDemandeAppro(),
            'codeCentrale'    => $this->hasRoles(Role::ROLE_ADMINISTRATEUR) || in_array($da->getAgenceEmetteur()->getCodeAgence(), ['90', '91', '92']),
            'formReappro'     => $formReappro->createView(),
            'formObservation' => $formObservation->createView(),
            'observations'    => $observations,
            'dataHistorique'  => $dataHistoriqueConsommation,
            'monthsList'      => $monthsList,
            'connectedUser'   => $this->getUser(),
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

                $this->emailDaService->envoyerMailValidationReappro($da, $observation ?? '-', $this->getUser(), false);

                $notification = [
                    'type'    => 'success',
                    'message' => 'La demande de réappro a été refusé avec succès.',
                ];
            } elseif ($request->request->has('valider')) {
                $this->validerDemande($da);
                $this->creationPDFReappro($da, $observations, $monthsList, $dataHistoriqueConsommation);
                $this->copyPDFToDW($da->getNumeroDemandeAppro());
                $this->ajouterDansDaSoumisAValidation($da);

                $this->emailDaService->envoyerMailValidationReappro($da, $observation ?? '-', $this->getUser());

                $notification = [
                    'type'    => 'success',
                    'message' => 'La demande de réappro a été validé avec succès.',
                ];
            }

            $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
            $this->redirectToRoute("list_da");
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

        $this->emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $this->getUser(), $this->estUserDansServiceAppro());

        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre observation a été enregistré avec succès.']);
        return $this->redirectToRoute("list_da");
    }
}
