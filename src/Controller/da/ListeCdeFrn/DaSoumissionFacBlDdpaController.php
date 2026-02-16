<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Controller\Controller;
use App\Entity\ddp\DemandePaiement;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\da\daCdeFrn\DaSoumissionFacBlDdpaType;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDdpaDto;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlDdpaMapper;
use App\Factory\da\CdeFrnDto\DaSoumissionFacBlDdpaFactory;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionFacBlDdpaController extends Controller
{
    private DaSoumissionFacBlDdpaFactory $daSoumissionFacBlDdpaFactory;

    public function __construct()
    {
        $this->daSoumissionFacBlDdpaFactory = new DaSoumissionFacBlDdpaFactory($this->getEntityManager());
    }

    /**
     * @Route("/soumission-facbl-ddpa/{numCde}", name="da_soumission_facbl_ddpa")
     */
    public function index(int $numCde)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //initialisation 
        $dto = $this->daSoumissionFacBlDdpaFactory->initialisation($numCde, $this->getUserName());

        // creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DaSoumissionFacBlDdpaType::class, $dto, [
            'method'  => 'POST'
        ])->getForm();

        // recuperation des demandes de paiement déjà payer
        $ddpa = $this->getDdpa($numCde, $dto);

        $montant = $this->getMontant($numCde, $dto);

        return $this->render('da/soumissionFacBlDdpa.html.twig', [
            'form' => $form->createView(),
            'ddpa' => $ddpa,
            'montant' => $montant,
            'dto' => $dto
        ]);
    }

    public function getDdpa(int $numCde, DaSoumissionFacBlDdpaDto $dto)
    {
        $ddpRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($numCde);

        $ddpa = [];
        $runningCumul = 0; // Variable pour maintenir le total cumulé

        foreach ($ddps as  $ddp) {
            // Crée un nouveau DTO pour chaque élément afin d'avoir des objets distincts
            $itemDto = new DaSoumissionFacBlDdpaDto();

            // Copie les propriétés nécessaires du DTO initial qui sont communes à tous les éléments
            $itemDto->totalMontantCommande = $dto->totalMontantCommande;

            // Mappe l'entité vers le nouveau DTO (le mapper ne s'occupe plus du cumul)
            DaSoumissionFacBlDdpaMapper::mapDdp($itemDto, $ddp);

            // Calcule et définit la valeur cumulative ici dans la logique du contrôleur
            $runningCumul += $itemDto->ratio;
            $itemDto->cumul = $runningCumul;

            $ddpa[] = $itemDto;
        }

        return $ddpa;
    }

    public function getMontant(int $numCde, DaSoumissionFacBlDdpaDto $dto)
    {
        $montantpayer = 0;
        $ddpRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($numCde);
        foreach ($ddps as $item) {
            $montantpayer = $montantpayer + $item->getMontantAPayers();
        }

        $ratioTotalPayer = ($montantpayer / $dto->totalMontantCommande) * 100;

        $montantAregulariser = $dto->totalMontantCommande - $montantpayer;
        $ratioMontantARegul = ($montantAregulariser /  $dto->totalMontantCommande) * 100;

        $dto = DaSoumissionFacBlDdpaMapper::mapTotalPayer($dto, $montantpayer, $ratioTotalPayer, $montantAregulariser, $ratioMontantARegul);

        return $dto;
    }
}
