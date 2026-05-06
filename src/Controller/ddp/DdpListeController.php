<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Dto\ddp\DdpSearchDto;
use App\Entity\ddp\DemandePaiement;
use App\Form\ddp\DdpSearchType;
use App\Mapper\ddp\DemandePaiementMapper;
use App\Repository\ddp\DemandePaiementRepository;
use App\Service\da\FileCheckerService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DdpListeController extends Controller
{
    private DemandePaiementRepository $demandePaiementRepository;
    public function __construct()
    {
        parent::__construct();
        $this->demandePaiementRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
    }

    /**
     * @Route("/liste", name="ddp_liste")
     *
     * @return void
     */
    public function ddpListe(Request $request)
    {
        // creation et traitment de formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DdpSearchType::class, new DdpSearchDto(), [
            'method' => 'GET'
        ])->getForm();
        $criteria = $this->traitementFormulaire($form, $request);

        // recupération des données dans la table demande_paiement
        $ddps = $this->demandePaiementRepository->findDemandePaiement($criteria, $this->getSecurityService()->estFinance());

        // transforme en DTO
        $dto = DemandePaiementMapper::mapInverse($ddps);

        /** suppression de ssession page_loadede  */
        if ($this->getSessionService()->has('page_loaded')) {
            $this->getSessionService()->remove('page_loaded');
        }

        // chemin fichier BAP
        $fileCheckerService = new FileCheckerService($_ENV['BASE_PATH_FICHIER']);

        return $this->render('ddp/demandePaiementList.html.twig', [
            'dto' => $dto,
            'form' => $form->createView(),
            'fileCheckerService' => $fileCheckerService,
        ]);
    }

    public function traitementFormulaire(FormInterface $form, Request $request): DdpSearchDto
    {
        $form->handleRequest($request);
        $criteria = new DdpSearchDto();
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
        }

        return $criteria;
    }
}
