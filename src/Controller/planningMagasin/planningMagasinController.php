<?php

namespace App\Controller\planningMagasin;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\planningMagasin\PlanningMagasinSearch;
use App\Model\planningMagasin\PlanningMagasinModel;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Traits\AutorisationTrait;
use App\Form\planningMagasin\PlanningMagasinSearchType;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/magasin")
 */
class planningMagasinController extends Controller
{

    use AutorisationTrait;
    private PlanningMagasinModel $planningMagasinModel;
    private PlanningMagasinSearch $planningMagasinSearch;

    public function __construct()
    {
        parent::__construct();
        $this->planningMagasinModel = new PlanningMagasinModel();
        $this->planningMagasinSearch = new PlanningMagasinSearch();
    }
    /**
     * @Route("/Planning", name = "interface_planningMag")
     */
    public function headPlanning(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_REP);
        /** FIN AUtorisation acées */
        //initialisation
        $this->planningMagasinSearch
            ->setAnnee(date('Y'))
            ->setFacture('ENCOURS')
            ->setPlan('PLANIFIE')
            ->setInterneExterne('TOUS')
            ->setTypeLigne('TOUETS')
            ->setMonths(3)
        ;
        $form = $this->getFormFactory()->createBuilder(
            PlanningMagasinSearchType::class,
            $this->planningMagasinSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->planningMagasinSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getdata());
            $criteria =  $form->getdata();
        }
        return $this->render('planningMagasin/planning.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
