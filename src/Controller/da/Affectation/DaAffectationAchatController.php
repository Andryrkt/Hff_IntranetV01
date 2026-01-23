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
            // $this->getEntityManager()->flush();
            // $this->addFlash('success', 'La demande a été affectée avec succès');
            // return $this->redirectToRoute('da_affectation_achat', ['id' => $daParent->getId()]);
        }
    }
}
