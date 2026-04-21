<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Dto\ddp\DdpSearchDto;
use App\Entity\admin\Application;
use App\Entity\ddp\DemandePaiement;
use App\Form\ddp\DdpSearchType;
use App\Mapper\ddp\DemandePaiementMapper;
use App\Repository\ddp\DemandePaiementRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DdpListeController extends Controller
{
    use AutorisationTrait;

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
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DDP);
        /** FIN AUtorisation acées */

        // creation et traitment de formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DdpSearchType::class, new DdpSearchDto(), [
            'method' => 'GET',
        ])->getForm();
        $criteria = $this->traitementFormulaire($form, $request);

        // recupération des données dans la table demande_paiement
        $ddps = $this->demandePaiementRepository->findDemandePaiement($criteria, $this->getUser());

        // transforme en DTO
        $dto = DemandePaiementMapper::mapInverse($ddps);

        /** suppression de ssession page_loadede  */
        if ($this->getSessionService()->has('page_loaded')) {
            $this->getSessionService()->remove('page_loaded');
        }


        return $this->render('ddp/demandePaiementList.html.twig', [
            'dto' => $dto,
            'form' => $form->createView(),
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
