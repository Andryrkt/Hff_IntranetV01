<?php

namespace App\Controller\da\Affectation;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\admin\Application;
use App\Entity\da\DemandeApproParent;
use App\Repository\da\DemandeApproParentRepository;
use App\Form\da\DaAffectationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/demande-appro") */
class DaAffectationAchatController extends Controller
{
    use AutorisationTrait;
    private DemandeApproParentRepository $demandeApproParentRepository;

    public function __construct()
    {
        parent::__construct();

        $em = $this->getEntityManager();
        $this->demandeApproParentRepository = $em->getRepository(DemandeApproParent::class);
    }

    /**
     * @Route("/affectation-achat/{id}", name="da_affectation_achat")
     */
    public function affectationDaAchat($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation accès */

        /** @var DemandeApproParent $daParent */
        $daParent = $this->demandeApproParentRepository->find($id);

        $form = $this->getFormFactory()->createBuilder(DaAffectationType::class, $daParent)->getForm();

        // $daObservation = new DaObservation();

        // $formReappro = $this->getFormFactory()->createBuilder(DaObservationValidationType::class, $daObservation)->getForm();
        // $formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $da->getDaTypeId()])->getForm();

        // $dateRange = $this->getLast12MonthsRange();
        // $monthsList = $this->getMonthsList($dateRange['start'], $dateRange['end']);
        // $dataHistoriqueConsommation = $this->getHistoriqueConsommation($da, $dateRange, $monthsList);
        // $observations = $this->daObservationRepository->findBy(['numDa' => $da->getNumeroDemandeAppro()]);

        // //========================================== Traitement du formulaire en général ===================================================//
        // $this->traitementFormulaire($formReappro, $formObservation, $request, $da, $observations, $monthsList, $dataHistoriqueConsommation);
        // //==================================================================================================================================//

        // $fichiers = $this->getAllDAFile([
        //     'baiPath'   => $this->getBaIntranetPath($da),
        //     'badPath'   => $this->getBaDocuWarePath($da),
        // ]);

        return $this->render("da/affectation-da.html.twig", [
            'form'               => $form->createView(),
            'demandeApproParent' => $daParent,
        ]);
    }
}
