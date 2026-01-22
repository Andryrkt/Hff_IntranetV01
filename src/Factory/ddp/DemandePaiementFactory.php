<?php

namespace App\Factory\ddp;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Entity\da\DaSoumissionFacBl;
use App\Constants\da\TypeDaConstants;
use App\Entity\admin\ddp\TypeDemande;
use App\Model\ddp\DemandePaiementModel;
use Doctrine\ORM\EntityManagerInterface;

class DemandePaiementFactory
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function load(int $typeDdp, ?int $numCdeDa, ?int $typeDa): DemandePaiementDto
    {
        $typeDemandeRepository = $this->em->getRepository(TypeDemande::class);
        $DaSoumissionFacBlRepository = $this->em->getRepository(DaSoumissionFacBl::class);
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        $ddpModel  = new DemandePaiementModel();

        $infoDa = $DaSoumissionFacBlRepository->getInfoDa($numCdeDa);
        $codeAgenceServiceIps = $ddpModel->getCodeAgenceService($infoDa['numeroOR']);

        $dto = new DemandePaiementDto();
        $dto->typeDemande = $typeDemandeRepository->find($typeDdp);
        $dto->numeroFacture = [trim($infoDa['NumeroFactureFournisseur'])];
        $dto->numeroCommande = [$numCdeDa];
        $dto->debiteur = $this->debiteur($typeDa, $infoDa, $codeAgenceServiceIps);
        $dto->typeDa = $typeDa;

        $dto->montantTotalCde = $ddpModel->getMontantTotalCde($numCdeDa);
        $dto->montantDejaPaye = $ddpRepository->getMontantDejaPayer($numCdeDa);
        $dto->montantRestantApayer = $dto->montantTotalCde - $dto->montantDejaPaye;
        $dto->montantAPayer = 0.00;
        $dto->poucentageAvance = (($dto->montantDejaPaye + $dto->montantAPayer) / $dto->montantTotalCde) * 100 . ' %';


        return $dto;
    }

    private function debiteur(int $typeDa, array $infoDa, array $codeAgenceServiceIps): array
    {
        $agenceRepository = $this->em->getRepository(Agence::class);
        $serviceRepository = $this->em->getRepository(Service::class);

        if ($typeDa === TypeDaConstants::TYPE_DA_AVEC_DIT) {
            $debiteur = [
                'agence' => $agenceRepository->findOneBy(['codeAgence' => $codeAgenceServiceIps[0]['code_agence']]),
                'service' => $serviceRepository->findOneBy(['codeService' => $codeAgenceServiceIps[0]['code_service']])
            ];
        } elseif ($typeDa === TypeDaConstants::TYPE_DA_DIRECT) {
            $debiteur = [
                'agence' => $agenceRepository->find($infoDa['agenceDebiteur']),
                'service' => $serviceRepository->find($infoDa['serviceDebiteur'])
            ];
        }

        return $debiteur;
    }
}
