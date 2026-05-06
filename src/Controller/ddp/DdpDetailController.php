<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\ddp\DemandePaiement;
use App\Repository\ddp\DemandePaiementRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DdpDetailController extends Controller
{
    private DemandePaiementRepository $demandePaiementRepository;

    public function __construct()
    {
        parent::__construct();
        $this->demandePaiementRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
    }

    /**
     * @Route("/detail/{numeroDdp}", name="ddp_detail")
     *
     * @return void
     */
    public function ddpDetail(string $numeroDdp)
    {
        $demandePaiement = $this->demandePaiementRepository->findOneBy(['numeroDdp' => $numeroDdp]);

        return $this->render('ddp/detail.html.twig', [
            'data' => $this->prepareForDisplay($demandePaiement),
            'fichiers' => [],
        ]);
    }

    private function prepareForDisplay(DemandePaiement $demandePaiement): array
    {
        $data = [];

        /** @var Agence|null  */
        $agenceDebiteur = $this->getEntityManager()->getRepository(Agence::class)->findOneBy(['codeAgence' => $demandePaiement->getAgenceDebiter()]);

        /** @var Service|null */
        $serviceDebiteur = $this->getEntityManager()->getRepository(Service::class)->findOneBy(['codeService' => $demandePaiement->getServiceDebiter()]);

        return [
            'typeDemande'     => $demandePaiement->getTypeDemandeId() ? $demandePaiement->getTypeDemandeId()->getLibelle() : "-",
            'numDdp'          => $demandePaiement->getNumeroDdp(),
            'numDemandeAppro' => $demandePaiement->getNumeroDemandeAppro() ?? "-",
            'demandeur'       => $demandePaiement->getDemandeur() ?? "-",
            'mailDemandeur'   => $demandePaiement->getAdresseMailDemandeur() ?? "-",
            'motif'           => $demandePaiement->getMotif() ?? "-",
            'contact'         => $demandePaiement->getContact() ?? "-",
            'numFournisseur'  => $demandePaiement->getNumeroFournisseur() ?? "-",
            'nomFournisseur'  => $demandePaiement->getBeneficiaire() ?? "-",
            'ribFournisseur'  => $demandePaiement->getRibFournisseur() ?? "-",
            'agenceDebiteur'  => $agenceDebiteur ? ($agenceDebiteur->getCodeAgence() . ' ' . $agenceDebiteur->getLibelleAgence()) : "-",
            'serviceDebiteur' => $serviceDebiteur ? ($serviceDebiteur->getCodeService() . ' ' . $serviceDebiteur->getLibelleService()) : "-",
            'modePaiement'    => $demandePaiement->getModePaiement() ?? "-",
            'numCommande'     => $demandePaiement->getNumeroCommande() ?? "-",
            'montant'         => $demandePaiement->getMontantAPayers() ? number_format((float) $demandePaiement->getMontantAPayers(), 2, ',', ' ') . ' ' . $demandePaiement->getDevise() : "-",
            'numFacture'      => $demandePaiement->getNumeroFacture() ?? "-",
        ];
    }
}
