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
use App\Form\da\DaObservationValidationType;
use App\Form\da\DemandeApproReapproFormType;

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
        dd($dataHistoriqueConsommation);
        //================== Traitement du formulaire en général ===========================//
        $this->traitementFormulaire($formReappro, $formObservation, $request, $da);
        // =================================================================================//

        $observations = $this->daObservationRepository->findBy(['numDa' => $da->getNumeroDemandeAppro()]);

        return $this->render("da/validation-reappro.html.twig", [
            'da'                      => $da,
            'numDa'                   => $da->getNumeroDemandeAppro(),
            'formReappro'             => $formReappro->createView(),
            'formObservation'         => $formObservation->createView(),
            'observations'            => $observations,
            'connectedUser'           => $this->getUser(),
            'propValTemplate'         => 'proposition-validation-direct',
            'dossierJS'               => 'propositionDirect',
        ]);
    }

    private function traitementFormulaire($form, $formObservation, Request $request, DemandeAppro $da)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
            // ✅ Récupérer les valeurs des champs caché
            $dalrList = $form->getData()->getDALR();
            $observation = $form->getData()->getObservation();
            $statutChange = $form->get('statutChange')->getData();

            if ($request->request->has('brouillon')) {
                /** Enregistrer provisoirement */
                // $this->traitementPourBtnBrouillon($dalrList, $request, $dals, $observation, $numDa, $da);
            } elseif ($request->request->has('enregistrer')) {
                /** Envoyer proposition à l'atelier */
                // $this->traitementPourBtnEnregistrer($dalrList, $request, $dals, $observation, $numDa, $da);
            } elseif ($request->request->has('changement')) {
                /** Valider les articles par l'atelier */
                // $this->traitementPourBtnValiderAtelier($request, $dals, $numDa, $dalrList, $observation, $da);
            } elseif ($request->request->has('observation')) {
                /** Envoyer observation */
                // $this->traitementPourBtnEnvoyerObservation($observation, $da, $statutChange);
            } elseif ($request->request->has('valider')) {
                /** Valider les articles par l'appro */
                // $this->traitementPourBtnValiderAppro($request, $dals, $numDa, $dalrList, $observation, $da);
            }
        }

        $formObservation->handleRequest($request);

        if ($formObservation->isSubmitted() && $formObservation->isValid()) {
            /** @var DaObservation $daObservation daObservation correspondant au donnée du formObservation */
            $daObservation = $formObservation->getData();

            // $this->traitementEnvoiObservation($daObservation, $da);
        }
    }
}
