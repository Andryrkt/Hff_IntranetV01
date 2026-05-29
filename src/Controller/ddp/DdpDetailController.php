<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\da\DemandeAppro;
use App\Entity\ddp\DemandePaiement;
use App\Repository\ddp\DemandePaiementRepository;
use App\Service\da\DocRattacheService;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DdpDetailController extends Controller
{
    private DemandePaiementRepository $demandePaiementRepository;
    private DocRattacheService $docRattacheService;

    public function __construct(DocRattacheService $docRattacheService)
    {
        parent::__construct();
        $this->demandePaiementRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
        $this->docRattacheService = $docRattacheService;
    }

    /**
     * @Route("/detail/{numeroDdp}", name="ddp_detail")
     *
     * @return void
     */
    public function ddpDetail(string $numeroDdp)
    {
        $demandePaiement = $this->demandePaiementRepository->findOneBy(['numeroDdp' => $numeroDdp]);
        $numDa = $demandePaiement->getNumeroDemandeAppro();
        $demandeAppro = $this->getEntityManager()->getRepository(DemandeAppro::class)->findOneBy(['numeroDemandeAppro' => $numDa]);

        return $this->render('ddp/detail.html.twig', [
            'data' => $this->prepareForDisplay($demandePaiement),
            'fichiers' => $this->docRattacheService->getAllAttachedFiles($demandeAppro),
        ]);
    }

    private function prepareForDisplay(DemandePaiement $demandePaiement): array
    {
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
