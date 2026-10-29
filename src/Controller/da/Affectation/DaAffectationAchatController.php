<?php

namespace App\Controller\da\Affectation;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Form\da\DaAffectationType;
use App\Entity\da\DemandeApproParent;
use App\Service\da\DaAfficherService;
use App\Constants\da\StatutDaConstant;
use App\Service\da\DaAffectationService;
use App\Service\da\DaObservationService;
use App\Entity\da\DemandeApproParentLine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/** @Route("/demande-appro") */
class DaAffectationAchatController extends Controller
{
    private DaObservationService $daObservationService;
    private DaAfficherService $daAfficherService;
    private DaAffectationService $daAffectationService;

    public function __construct(DaObservationService $daObservationService, DaAfficherService $daAfficherService, DaAffectationService $daAffectationService)
    {
        $this->daObservationService         = $daObservationService;
        $this->daAfficherService = $daAfficherService;
        $this->daAffectationService = $daAffectationService;
    }

    /**
     * @Route("/affectation-achat/{id}", name="da_affectation_achat")
     */
    public function affectationDaAchat($id, Request $request)
    {
        /** @var DemandeApproParent $daParent */
        $daParent = $this->getEntityManager()->getRepository(DemandeApproParent::class)->find($id);

        if (!$daParent) throw new ResourceNotFoundException();

        foreach ($daParent->getDemandeApproParentLines() as $dapl) {
            if ($dapl->getArtRefp() === "-") $dapl->setArtRefp("");
        }

        $form = $this->getFormFactory()->createBuilder(DaAffectationType::class, $daParent)->getForm();

        //========================================== Traitement du formulaire en général ===================================================//
        $this->traitementFormulaire($form, $request, $daParent);
        //==================================================================================================================================//

        return $this->render("da/affectation-da.html.twig", [
            'form'               => $form->createView(),
            'demandeApproParent' => $daParent,
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request, DemandeApproParent $daParent)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeApproParent $daParent */
            $daParent = $form->getData();
            // suivi étape par étape de l'affectation (admin > historique > suivi des opérations)
            SuiviOperationService::demarrer(SuiviOperationService::TYPE_DA_AFFECTATION, $daParent->getNumeroDemandeAppro());
            $btn = $this->getButtonName($request);

            if ($btn === "passerDA")         $this->traitementTransmissionDA($daParent);
            elseif ($btn === "subdiviserDA") $this->traitementSubdivisionDA($daParent);
            else {
                $this->getSessionService()->set('notification', ['type' => 'error', 'message' => 'Veuillez cliquer sur l\'un des boutons valides pour continuer.']);
            }
        }
    }

    public function getButtonName(Request $request): string
    {
        if ($request->request->has('passerDA'))         return 'passerDA';
        elseif ($request->request->has('subdiviserDA')) return 'subdiviserDA';
        else return 'N\A';
    }

    private function traitementTransmissionDA(DemandeApproParent $daParent)
    {
        $motif = trim((string) $daParent->getObservation());

        if ($motif === "") {
            $this->getSessionService()->set('notification', ['type' => 'error', 'message' => 'Le champ motif est obligatoire pour passer la DA au demandeur.']);
        } else {
            $daParent->setStatutDal(StatutDaConstant::STATUT_AUTORISER_EMETTEUR);

            foreach ($daParent->getDemandeApproParentLines() as $dapl) {
                $dapl->setStatutDal(StatutDaConstant::STATUT_AUTORISER_EMETTEUR);
            }

            $this->getEntityManager()->flush();

            // Ajout de l'observation dans la table da_observation 
            $this->daObservationService->insertionObservation($daParent->getNumeroDemandeAppro(), $motif);

            // Ajout des données dans la table DaAfficher
            $this->daAfficherService->generateDaAfficherOnCreationDaParent($daParent, false);

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'La transmission de la DA a été effectuée']);
            $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
        }
    }

    private function traitementSubdivisionDA(DemandeApproParent $daParent)
    {

        $daParentLines = $daParent->getDemandeApproParentLines();

        $lineConstpVide = $daParentLines->filter(function (DemandeApproParentLine $dapl) {
            return $dapl->getArtConstp() === "-";
        });

        if (!empty($lineConstpVide)) $this->getSessionService()->set('notification', ['type' => 'error', 'message' => 'Certain(s) article(s) ont des constructeurs non définis. Veuillez vérifier les articles concernées']);

        $allDaDirect = $daParentLines->filter(function (DemandeApproParentLine $dapl) {
            return !$dapl->getArticleStocke();
        });
        $allDaPonctuel = $daParentLines->filter(function (DemandeApproParentLine $dapl) {
            return $dapl->getArticleStocke();
        });

        // traitement des DA direct
        if ($allDaDirect->count() > 0) $this->daAffectationService->traitementDaParentLines($allDaDirect, $daParent, DemandeAppro::TYPE_DA_DIRECT);

        // traitement des DA ponctuel
        if ($allDaPonctuel->count() > 0) $this->daAffectationService->traitementDaParentLines($allDaPonctuel, $daParent, DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL);

        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'L\'affectation a été enregistrée']);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }
}
