<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Entity\ddp\DemandePaiement;
use App\Entity\ddp\DemandePaiementLigne;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ddp\DemandePaiementRepository;
use App\Repository\ddp\DemandePaiementLigneRepository;

class DdpListeController extends Controller
{
    private DemandePaiementRepository $demandePaiementRepository;

    public function __construct()
    {
        parent::__construct();

        $this->demandePaiementRepository = self::$em->getRepository(DemandePaiement::class);
    }

    /**
     * @Route("/ddp/liste-ddp", name="ddp_liste")
     *
     * @return void
     */
    public function ddpListe()
    {

        $data = $this->demandePaiementRepository->findBy([], ['dateCreation' => 'DESC']);
        
        /** suppression de ssession page_loadede  */
        if($this->sessionService->has('page_loaded')){
            $this->sessionService->remove('page_loaded');
        }
        

        self::$twig->display('ddp/demandePaiementList.html.twig', [
            'data' => $data
        ]);
    }
}