<?php

namespace App\Factory\da\CdeFrnDto;

use App\Constants\da\StatutBcConstant;
use App\Dto\Da\ListeCdeFrn\DaSoumissionBcDto;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeAppro;
use App\Model\da\DaModel;
use Doctrine\ORM\EntityManagerInterface;

class DaSoumissionBcFactory
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function init(string $numeroCde, string $numDa, int $numOr, int $typeDa, string $codeSociete): DaSoumissionBcDto
    {
        $dto = new DaSoumissionBcDto();
        $dto->numeroCde = $numeroCde;
        $dto->numeroDemandeAppro = $numDa;
        $dto->numeroOr = $numOr;
        $dto->typeDa = $typeDa;
        $dto->codeSociete = $codeSociete;
        $dto->montantBcIps = $this->getMontantBcIps($dto);

        return $dto;
    }

    public function apresSoumission(DaSoumissionBcDto $dto, string $utilisateur, string $nomPdfFusionner)
    {
        $dto->numeroDemandeDit = $this->getNumeroDit($dto);
        $dto->montantBc = $this->getMontantBc($dto);

        $dto->statut = StatutBcConstant::STATUT_SOUMISSION;
        $dto->utilisateur = $utilisateur;
        $dto->numeroVersion = $this->getNumeroVersion($dto);
        $dto->pieceJoint1 = $nomPdfFusionner;

        return $dto;
    }

    private function getNumeroDit(DaSoumissionBcDto $dto)
    {
        $demandeApproRepository = $this->entityManager->getRepository(DemandeAppro::class);
        return $demandeApproRepository->getNumDitDa($dto->numeroDemandeAppro, $dto->codeSociete);
    }

    private function getMontantBcIps(DaSoumissionBcDto $dto): float
    {
        $daModel = new DaModel();
        return $daModel->getMontantBcDaDirect($dto->numeroCde, $dto->codeSociete);
    }

    private function getMontantBc(DaSoumissionBcDto $dto): ?float
    {
        $daSoumissionBcRepository = $this->entityManager->getRepository(DaSoumissionBc::class);
        return $daSoumissionBcRepository->getMontantBc($dto->numeroCde, $dto->codeSociete);
    }

    private function getNumeroVersion(DaSoumissionBcDto $dto): int
    {
        $daSoumissionBcRepository = $this->entityManager->getRepository(DaSoumissionBc::class);
        return $this->autoIncrement($daSoumissionBcRepository->getNumeroVersionMax($dto->numeroCde, $dto->codeSociete));;
    }

    private function autoIncrement(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return (int)$num + 1;
    }
}
