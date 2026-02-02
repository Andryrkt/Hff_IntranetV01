<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Form\ddp\DdpSearchType;
use App\Entity\admin\Application;
use App\Entity\admin\ddp\ddpSearch;
use App\Entity\ddp\DemandePaiement;
use App\Entity\ddp\DemandePaiementLigne;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ddp\DemandePaiementRepository;
use App\Repository\ddp\DemandePaiementLigneRepository;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DdpListeController extends Controller
{
    use AutorisationTrait;

    private DemandePaiementRepository $demandePaiementRepository;
    private DdpSearch $ddpSearch;
    public function __construct()
    {
        parent::__construct();
        $this->demandePaiementRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
        $this->ddpSearch = new DdpSearch();
    }

    /**
     * @Route("/liste", name="ddp_liste")
     *
     * @return void
     */
    public function ddpListe(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DDP);
        /** FIN AUtorisation acées */

        $form = $this->getFormFactory()->createBuilder(DdpSearchType::class, $this->ddpSearch, [
            'method' => 'GET',
        ])->getForm();
        $form->handleRequest($request);
        $criteria = $this->ddpSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
            // dd($criteria);
        }
        // $data = $this->demandePaiementRepository->findBy([], ['dateCreation' => 'DESC']);
        $data = $this->demandePaiementRepository->findDemandePaiement($criteria, $this->getUser());
        /** suppression de ssession page_loadede  */
        if ($this->getSessionService()->has('page_loaded')) {
            $this->getSessionService()->remove('page_loaded');
        }


        return $this->render('ddp/demandePaiementList.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }
}
