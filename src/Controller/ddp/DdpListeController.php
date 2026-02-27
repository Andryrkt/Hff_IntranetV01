<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Form\ddp\DdpSearchType;
use App\Entity\admin\ddp\DdpSearch;
use App\Entity\ddp\DemandePaiement;
use App\Constants\admin\ApplicationConstant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ddp\DemandePaiementRepository;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DdpListeController extends Controller
{
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
        // Agences Services autorisés sur le DDP
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_DDP);

        $form = $this->getFormFactory()->createBuilder(DdpSearchType::class, $this->ddpSearch, [
            'method' => 'GET',
            'agenceServiceAutorises' => $agenceServiceAutorises
        ])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->ddpSearch =  $form->getdata();
        }

        $this->gererAgenceService($this->ddpSearch, $agenceServiceAutorises);

        $data = $this->demandePaiementRepository->findDemandePaiement($this->ddpSearch, $agenceServiceAutorises);
        /** suppression de ssession page_loadede  */
        if ($this->getSessionService()->has('page_loaded')) {
            $this->getSessionService()->remove('page_loaded');
        }


        return $this->render('ddp/demandePaiementList.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }

    private function gererAgenceService(DdpSearch $ddpSearch, array $agenceServiceAutorises): void
    {
        // Changer le serviceDebiteur
        if ($ddpSearch->getService()) {
            $ligneId = $ddpSearch->getService();
            if ($ligneId && isset($agenceServiceAutorises[$ligneId])) {
                $ddpSearch->setService($agenceServiceAutorises[$ligneId]['service_code']);
            }
        }
    }
}
