<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Entity\admin\ddp\ddpSearch;
use App\Entity\ddp\DemandePaiement;
use App\Entity\ddp\DemandePaiementLigne;
use App\Form\ddp\DdpSearchType;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ddp\DemandePaiementRepository;
use App\Repository\ddp\DemandePaiementLigneRepository;
use Symfony\Component\HttpFoundation\Request;

class DdpListeController extends Controller
{
    private DemandePaiementRepository $demandePaiementRepository;
    private DdpSearch $ddpSearch;
    public function __construct()
    {
        parent::__construct();
        $this->demandePaiementRepository = self::$em->getRepository(DemandePaiement::class);
        $this->ddpSearch = new DdpSearch();
    }

    /**
     * @Route("/ddp/liste-ddp", name="ddp_liste")
     *
     * @return void
     */
    public function ddpListe(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $form = self::$validator->createBuilder(DdpSearchType::class, $this->ddpSearch, [
            'method' => 'GET',
        ])->getForm();
        $form->handleRequest($request);
        $criteria = $this->ddpSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
            // dd($criteria);
        }
        // $data = $this->demandePaiementRepository->findBy([], ['dateCreation' => 'DESC']);
        $data = $this->demandePaiementRepository->findDemandePaiement($criteria);
        /** suppression de ssession page_loadede  */
        if ($this->sessionService->has('page_loaded')) {
            $this->sessionService->remove('page_loaded');
        }


        self::$twig->display('ddp/demandePaiementList.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }
}
