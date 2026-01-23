<?php

namespace App\Controller\da\Affectation;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\affectation\DaAffectationTrait;
use App\Entity\admin\Application;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproParent;
use App\Entity\da\DemandeApproParentLine;
use App\Form\da\DaAffectationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/demande-appro") */
class DaAffectationAchatController extends Controller
{
    use AutorisationTrait, DaAffectationTrait;


    public function __construct()
    {
        parent::__construct();

        $this->initDaAffectationTrait();
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

        //========================================== Traitement du formulaire en général ===================================================//
        $this->traitementFormulaire($form, $request, $daParent);
        //==================================================================================================================================//

        return $this->render("da/affectation-da.html.twig", [
            'form'               => $form->createView(),
            'demandeApproParent' => $daParent,
        ]);
    }

    private function traitementFormulaire($form, $request, $daParent)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeApproParent $daParent */
            $daParent = $form->getData();
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

            $this->getEntityManager()->flush();

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'L\'affectation a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }
}
