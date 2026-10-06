<?php

namespace App\Controller\da\ddp;

use App\Controller\Controller;
use App\Dto\Da\ddp\BapSearchDto;
use App\Entity\ddp\DemandePaiement;
use App\Form\da\ddp\BonApayerType;
use Doctrine\ORM\EntityManagerInterface;
use App\Mapper\ddp\DemandePaiementMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ddp\DemandePaiementRepository;

/**
 * @Route("/demande-appro")
 */
class BonApayerController extends Controller
{
    private DemandePaiementRepository $demandePaiementRepository;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->demandePaiementRepository = $em->getRepository(DemandePaiement::class);
    }

    /**
     * @Route("/consultation-facture", name="da_bon_a_payer" )
     */
    public function index(Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        // Création du formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(BonApayerType::class, null, ['method' => 'GET'])->getForm();

        // Traitement du formulaire de recherche
        $form->handleRequest($request);

        $criteria = new BapSearchDto();
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BapSearchDto $criteria */
            $criteria = $form->getData();
        }

        // Récupération des données dans la table demande_paiement
        $ddp = $this->demandePaiementRepository->findByConsultationFactureCriteria($criteria);
        // transformation en DTO (DemandePaiementDto)
        $dtos = DemandePaiementMapper::mapInverse($ddp);

        return $this->render('da/ddp/bon_a_payer.html.twig', [
            'dtos' => $dtos,
            'form' => $form->createView()
        ]);
    }
}
