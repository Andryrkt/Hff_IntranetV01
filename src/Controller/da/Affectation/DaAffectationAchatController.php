<?php

namespace App\Controller\da\Affectation;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\admin\Application;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproParent;
use App\Entity\da\DemandeApproParentLine;
use App\Repository\da\DemandeApproParentRepository;
use App\Form\da\DaAffectationType;
use Doctrine\Common\Collections\ArrayCollection;
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

    private function traitementDaParentLines(ArrayCollection $daParentLines, DemandeApproParent $daParent, int $daType)
    {
        $demandeAppro = $this->createDemandeAppro($daParent, $daType);

        $numLigne = 0;
        /** @var DemandeApproParentLine $daParentLine */
        foreach ($daParentLines as $daParentLine) {
            $demandeApproLine = new DemandeApproL();

            $demandeApproLine
                ->duplicateDaParentLine($daParentLine)
                ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                ->setNumeroLigne(++$numLigne)
                ->setStatutDal($demandeAppro->getStatutDal())
                ->setEstValidee($demandeAppro->getEstValidee())
                ->setValidePar($demandeAppro->getValidePar())
            ;

            // ajouter dans la collection des DAL de la nouvelle DA
            $demandeAppro->addDAL($demandeApproLine);

            $this->getEntityManager()->persist($demandeApproLine);
        }
        $this->getEntityManager()->persist($demandeAppro);
    }

    private function createDemandeAppro(DemandeApproParent $daParent, int $daType)
    {
        $demandeAppro = new DemandeAppro();

        $prefix = [
            DemandeAppro::TYPE_DA_DIRECT           => 'DAPD',
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'DAPP',
        ];

        $statut = [
            DemandeAppro::TYPE_DA_DIRECT           => DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => DemandeAppro::STATUT_VALIDE,
        ];

        $numDa = str_replace('DAP', $prefix[$daType], $daParent->getNumeroDemandeAppro());

        $demandeAppro
            ->duplicateDaParent($daParent)
            ->setDaTypeId($daType)
            ->setNumeroDemandeAppro($numDa)
            ->setStatutDal($statut[$daType])
        ;

        if ($daType === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL) {
            $demandeAppro
                ->setEstValidee(true)
                ->setValidateur($this->getUser())
                ->setValidePar($this->getUser()->getNomUtilisateur())
            ;
        }
        return $demandeAppro;
    }
}
