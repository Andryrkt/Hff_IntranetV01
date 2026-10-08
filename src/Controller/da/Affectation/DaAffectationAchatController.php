<?php

namespace App\Controller\da\Affectation;

use App\Service\da\DaService;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Form\da\DaAffectationType;
use App\Entity\da\DemandeApproParent;
use App\Service\da\DaAfficherService;
use App\Constants\da\StatutDaConstant;
use App\Entity\da\DemandeApproParentLine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\affectation\DaAffectationTrait;

/** @Route("/demande-appro") */
class DaAffectationAchatController extends Controller
{
    use DaAffectationTrait;

    private DaService $daService;
    private DaAfficherService $daAfficherService;

    public function __construct(DaService $daService, DaAfficherService $daAfficherService)
    {
        $this->daService         = $daService;
        $this->daAfficherService = $daAfficherService;

        $this->initDaAffectationTrait();
    }

    /**
     * @Route("/affectation-achat/{id}", name="da_affectation_achat")
     */
    public function affectationDaAchat($id, Request $request)
    {
        /** @var DemandeApproParent $daParent */
        $daParent = $this->demandeApproParentRepository->find($id);

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
            $this->daService->insertionObservation($daParent->getNumeroDemandeAppro(), $motif, $this->getUserName());

            // Ajout des données dans la table DaAfficher
            $this->daAfficherService->generateDaAfficherOnCreationDaParent($daParent, false);

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'La transmission de la DA a été effectuée']);
            $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
        }
    }

    private function traitementSubdivisionDA(DemandeApproParent $daParent)
    {

        $daParentLines = $daParent->getDemandeApproParentLines();
        $allDaDirect = $daParentLines->filter(function (DemandeApproParentLine $dapl) {
            return !$dapl->getArticleStocke();
        });
        $allDaPonctuel = $daParentLines->filter(function (DemandeApproParentLine $dapl) {
            return $dapl->getArticleStocke();
        });

        // traitement des DA direct
        if ($allDaDirect->count() > 0) $this->traitementDaParentLines($allDaDirect, $daParent, DemandeAppro::TYPE_DA_DIRECT);

        // traitement des DA ponctuel
        if ($allDaPonctuel->count() > 0) $this->traitementDaParentLines($allDaPonctuel, $daParent, DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL);

        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'L\'affectation a été enregistrée']);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }
}
