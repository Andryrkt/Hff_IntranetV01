<?php

namespace App\Factory\ddp;

use App\Constants\da\TypeDaConstants;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\da\DaSoumissionFacBl;
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
        $DaSoumissionFacBlRepository = $this->em->getRepository(DaSoumissionFacBl::class);
        $agenceRepository = $this->em->getRepository(Agence::class);
        $serviceRepository = $this->em->getRepository(Service::class);
        $infoDa = $DaSoumissionFacBlRepository->getInfoDa($numCdeDa);
        $ddpModel  = new DemandePaiementModel();
        $codeAgenceServiceIps = $ddpModel->getCodeAgenceService($infoDa['numeroOR']);

        $dto = new DemandePaiementDto();
        $dto->typeDemande = $this->em->getRepository(TypeDemande::class)->find($typeDdp);
        $dto->numeroFacture = [trim($infoDa['NumeroFactureFournisseur'])];
        $dto->numeroCommande = [$numCdeDa];
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
        $dto->debiteur = $debiteur;
        $dto->typeDa = $typeDa;


        return $dto;
    }
}
