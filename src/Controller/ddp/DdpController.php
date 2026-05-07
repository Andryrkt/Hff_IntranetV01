<?php

namespace App\Controller\ddp;

use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Controller\Controller;
use App\Dto\ddp\DdpDto;
use App\Factory\ddp\DdpFactory;
use App\Form\ddp\DdpType;
use App\Service\ddp\CommandeLivraisonService;
use App\Service\ddp\DemandePaiementCommandeService;
use App\Service\ddp\Magasin\DdpDocService;
use App\Service\ddp\Magasin\DdpLigneService;
use App\Service\ddp\Magasin\DdpService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ddp")
 */
class DdpController extends Controller
{
    private DdpFactory $ddpFactory;
    private DdpService $ddpService;
    private DdpLigneService $ddpLigneService;
    private DdpDocService $ddpDocService;


    public function __construct(
        DdpFactory $ddpFactory,
        DdpService $ddpService,
        DdpLigneService $ddpLigneService,
        DdpDocService $ddpDocService
    ) {
        parent::__construct();
        $this->ddpFactory = $ddpFactory;
        $this->ddpService = $ddpService;
        $this->ddpLigneService = $ddpLigneService;
        $this->ddpDocService = $ddpDocService;
    }

    /**
     * @Route("/new/{typeDdp}", name="new_ddp")
     */
    public function new(int $typeDdp, Request $request)
    {
        // initialisation DTO
        $dto = $this->ddpFactory->initialisation($typeDdp);
        //Creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DdpType::class, $dto)->getForm();
        // Traitement du formulaire
        $this->traitementDuFormulaire($form,  $request);
        return $this->render('ddp/magasin/new.html.twig', [
            'form' => $form->createView(),
            'type_ddp' => $typeDdp
        ]);
    }

    private function traitementDuFormulaire(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DdpDto $dto */
            $dto = $form->getData();

            $dto = $this->ddpFactory->apresSoumission($form, $dto);

            // Enregistrement dans BD
            $this->enregistrementSurBd($dto);
            // TODO: Generation page de garde
            // TODO: fusion de toutes les fichiers
            // TODO: envoie dans DOCUWARE
            // TODO: Historisation
        }
    }

    private function enregistrementSurBd(DdpDto $dto): void
    {
        // enregistrement dans la table deamnde_paiement
        $ddp = $this->ddpService->createDdp($dto);
        // enregistrement dans la table demande_paiement_ligne
        $this->ddpLigneService->createLignesFromDto($dto);
        // enregistrement dans la table doc_demande_paiement
        $this->ddpDocService->createDocDdp($dto);
        // enregistrement dans la table demande_paiement_commande
        $demandePaiementCommandeService = new DemandePaiementCommandeService($this->getEntityManager());
        $demandePaiementCommandeService->createDdpCommande($dto, $ddp);
        // enregistrement dans la table commande_livraison (ce n'est pas utile pour le demande de paiement à l'avance)
        if ($dto->typeDdp->getId() !== TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE) {
            $commandeLivraisonService = new CommandeLivraisonService($this->getEntityManager());
            $commandeLivraisonService->createCommandeLivraison($dto, $ddp);
        }
    }
}
