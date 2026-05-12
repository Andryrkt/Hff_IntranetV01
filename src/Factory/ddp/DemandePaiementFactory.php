<?php

namespace App\Factory\ddp;

use App\Constants\da\TypeDaConstants;
use App\Constants\ddp\StatutConstants;
use App\Dto\Da\ListeCdeFrn\DaDdpaDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\admin\Agence;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\admin\Service;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSoumissionBc;
use App\Entity\ddp\DemandePaiement;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlMapper;
use App\Mapper\ddp\DdpRecapMapper;
use App\Mapper\ddp\DemandePaiementMapper;
use App\Model\da\DaSoumissionFacBlDdpaModel;
use App\Model\ddp\DemandePaiementModel;
use App\Service\autres\AutoIncDecService;
use App\Service\da\NumeroGenerateurService;
use App\Service\ddp\DdpFinancialService;
use App\Service\ddp\DocDemandePaiementService;
use App\Service\security\SecurityService;
use Doctrine\ORM\EntityManagerInterface;

class DemandePaiementFactory
{
    private EntityManagerInterface $em;
    private DemandePaiementModel $ddpModel;
    private DocDemandePaiementService $docDemandePaiementService;
    private DaSoumissionFacBlDdpaModel $daSoumissionFacBlDdpaModel;
    private DdpFinancialService $financialService;
    private NumeroGenerateurService $numeroGenerateur;
    private SecurityService $securityService;

    public function __construct(
        EntityManagerInterface $em,
        DemandePaiementModel $ddpModel,
        DocDemandePaiementService $docDemandePaiementService,
        DaSoumissionFacBlDdpaModel $daSoumissionFacBlDdpaModel,
        DdpFinancialService $financialService,
        NumeroGenerateurService $numeroGenerateur
    ) {
        global $container;
        $this->securityService = $container->get('security.service');
        $this->em = $em;
        $this->ddpModel = $ddpModel;
        $this->docDemandePaiementService = $docDemandePaiementService;
        $this->daSoumissionFacBlDdpaModel = $daSoumissionFacBlDdpaModel;
        $this->financialService = $financialService;
        $this->numeroGenerateur = $numeroGenerateur;
    }

    public function load(
        int $typeDdp,
        ?int $numCdeDa,
        ?int $typeDa,
        int $numeroVersionBc = 0,
        $sessionService
    ): DemandePaiementDto {
        $dto = new DemandePaiementDto();
        $infoDa = $this->em->getRepository(DaAfficher::class)->getInfoDa($numCdeDa);
        if (empty($infoDa)) {
            throw new \Exception("Aucune information de demande d'approvisionnement trouvée pour le numero commande $numCdeDa");
        }
        $this->hydrateBaseInfo($dto, $typeDdp, $numCdeDa, $typeDa, $infoDa);
        $this->hydrateDaInfo($dto, $typeDa, $numeroVersionBc, $sessionService, $infoDa);
        $this->hydrateGeneralInfo($dto);
        $this->hydrateFournisseurInfo($dto, $infoDa);
        $this->hydrateFinancialData($dto);
        $dto->dernierStatutDdp = $this->recupDernierStatutDdp($dto);
        $dto->estRegule = $dto->montantAregulariser <= 0.0 && !in_array($dto->dernierStatutDdp, StatutConstants::REFUSES_DDP);
        $this->ddpRecap($dto);

        return $dto;
    }

    public function recupDernierStatutDdp(DemandePaiementDto $dto): ?string
    {
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        return  $ddpRepository->getDernierStatutDddp($dto->numeroCommande, $dto->numeroDemandeAppro);
    }

    private function ddpRecap(DemandePaiementDto $dto)
    {
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        $ddpList = $ddpRepository->findBy([
            'numeroCommande' => $dto->numeroCommande,
            'numeroDemandeAppro' => $dto->numeroDemandeAppro,
            'codeSociete' => $dto->codeSociete,
        ]);

        $totalMontantCommande = $this->financialService->recuperationMontantTotalCommande($dto->numeroCommande, $dto->codeSociete);
        /** @var DemandePaiementDto[] $demandePaiementDto */
        $demandePaiementDto = DemandePaiementMapper::mapInverse($ddpList);
        $dto->ddpRecap = DdpRecapMapper::map($demandePaiementDto, $totalMontantCommande);
    }

    private function hydrateBaseInfo(DemandePaiementDto $dto, int $typeDdp, ?int $numCdeDa, ?int $typeDa, array $infoDa): void
    {
        $dto->typeDemande = $this->em->getRepository(TypeDemande::class)->find($typeDdp);
        $dto->numeroFacture = null;
        $dto->numeroFactureIps = null;
        $dto->numeroCommande = $numCdeDa;
        $dto->debiteur = $this->getDebiteur($typeDa, $infoDa);
        $dto->codeSociete = $this->securityService->getCodeSocieteUser();
    }

    private function hydrateDaInfo(DemandePaiementDto $dto, ?int $typeDa, int $numeroVersionBc = 0, $sessionService, array $infoDa): void
    {
        $dto->typeDa = $typeDa;
        $dto->numeroDa = $infoDa['numeroDemandeAppro'] ?? null;
        $dto->numeroDemandeAppro = $dto->numeroDa;

        $demandePaiementRepository = $this->em->getRepository(DemandePaiement::class);
        $dto->numeroSoumissionDdpDa = AutoIncDecService::autoIncrement(
            $demandePaiementRepository->getDernierNumeroSoumissionDdpDa($dto->numeroCommande, $dto->numeroDa)
        );

        $dto->ddpaDa = $sessionService->get('demande_paiement_a_l_avance')['ddpa'] ?? false;
        $dto->numeroVersionBc = $numeroVersionBc ?? $this->em->getRepository(DaSoumissionBc::class)->getNumeroVersionMax($dto->numeroCommande, $dto->codeSociete);
        $dto->nomPdfFusionnerBc = $sessionService->get('demande_paiement_a_l_avance')['nom_pdf'] ?? '';

        $dto->fichiersChoisis = $this->docDemandePaiementService->getFichiersDevisDa((int)$dto->numeroDa);
        $dto->appro = $typeDa !== null;
    }

    private function hydrateGeneralInfo(DemandePaiementDto $dto): void
    {
        $dto->demandeur = $this->securityService->getUserName();
        $dto->adresseMailDemandeur = $this->securityService->getUserEmail();
        $dto->ddpSoumissioncde = $dto->ddpaDa;
        $dto->statut = $dto->ddpSoumissioncde ? StatutConstants::DDPA_EN_ATTENTE_DE_VALIDATION_BC : StatutConstants::DDPA_A_TRANSMETTRE;
        $dto->numeroDdp = $this->numeroGenerateur->genererNumeroDdp();
        $dto->numeroVersion = 1;
        $dto->numeroDossierDouane = $this->docDemandePaiementService->recupNumDossierDouane($dto);
        $dto->dateDemande = new \DateTime();
    }

    private function hydrateFournisseurInfo(DemandePaiementDto $dto, array $infoDa): void
    {
        $numeroFournisseur = $infoDa['numeroFournisseur'] ?? null;
        if (empty($numeroFournisseur) || $numeroFournisseur === '-') {
            throw new \Exception("Aucun numéro fournisseur trouvé pour le numéro commande " . $dto->numeroCommande);
        }

        $infoFournisseur = $this->ddpModel->recupInfoPourDa($numeroFournisseur, $dto->numeroCommande);
        if (empty($infoFournisseur)) {
            throw new \Exception("Aucune information fournisseur trouvée pour le numéro commande " . $dto->numeroCommande);
        } else {
            $data = $infoFournisseur[0];
            $dto->numeroFournisseur = $data['num_fournisseur'];
            $dto->ribFournisseur = $data['rib_fournisseur'];
            $dto->ribFournisseurAncien = $data['rib_fournisseur'];
            $dto->cif = $data['cif'];
            $dto->beneficiaire = $data['nom_fournisseur'];
            $dto->modePaiement = $data['mode_paiement'];
            $dto->devise = $data['devise'];
        }
    }

    private function hydrateFinancialData(DemandePaiementDto $dto): void
    {
        $dto->totalMontantCommande = $this->financialService->recuperationMontantTotalCommande($dto->numeroCommande, $dto->codeSociete);

        [$montantDejaPaye, $ratioMontantDejaPaye, $montantAregulariser, $ratioMontantARegul] = $this->financialService->calculatePaymentRatios($dto);
        $dto->montantDejaPaye = $montantDejaPaye;
        $dto->ratioMontantDejaPaye = $ratioMontantDejaPaye;
        $dto->montantAregulariser = $montantAregulariser;
        $dto->ratioMontantARegul = $ratioMontantARegul;

        [$pourcentageAvance, $pourcentageAPayer] = $this->financialService->calculateGlobalFinancials($dto);
        $dto->pourcentageAvance = $pourcentageAvance;
        $dto->pourcentageAPayer = $pourcentageAPayer;

        $this->populateDdpaList($dto);
    }


    private function populateDdpaList(DemandePaiementDto $dto): void
    {
        $ddps = $this->em->getRepository(DemandePaiement::class)->getDdpSelonNumCde($dto->numeroCommande);

        $runningCumul = 0;

        foreach ($ddps as $ddp) {
            $ddpaDto = new DaDdpaDto();
            $ddpaDto->totalMontantCommande = $dto->totalMontantCommande;
            DaSoumissionFacBlMapper::mapDdp($ddpaDto, $ddp);

            $runningCumul += $ddpaDto->ratio;
            $ddpaDto->cumul = $runningCumul;

            $dto->daDdpa[] = $ddpaDto;
        }
    }


    private function getDebiteur(int $typeDa, array $infoDa): array
    {
        $agenceRepository = $this->em->getRepository(Agence::class);
        $serviceRepository = $this->em->getRepository(Service::class);

        if ($typeDa === TypeDaConstants::TYPE_DA_AVEC_DIT) {
            $codeAgenceServiceIps = $this->ddpModel->getCodeAgenceService($infoDa['numeroOr']);
            if (empty($codeAgenceServiceIps)) {
                throw new \Exception("Aucune information trouvée dans IPS pour le numero OR " . $infoDa['numeroOr']);
            }
            return [
                'agence' => $agenceRepository->findOneBy(['codeAgence' => $codeAgenceServiceIps[0]['code_agence']]),
                'service' => $serviceRepository->findOneBy(['codeService' => $codeAgenceServiceIps[0]['code_service']])
            ];
        }

        if ($typeDa === TypeDaConstants::TYPE_DA_DIRECT) {
            return [
                'agence' => $agenceRepository->find($infoDa['agenceDebiteur']),
                'service' => $serviceRepository->find($infoDa['serviceDebiteur'])
            ];
        }

        return [];
    }
}
