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

        return $dto;
    }

    public function apresSoumission(DaSoumissionBcDto $dto, string $utilisateur, string $nomPdfFusionner)
    {
        $dto->numeroDemandeDit = $this->getNumeroDit($dto->numeroDemandeAppro, $dto->codeSociete);
        $dto->montantBc = $this->getMontantBc($dto->numeroCde, $dto->codeSociete);
        $dto->statut = StatutBcConstant::STATUT_SOUMISSION;
        $dto->utilisateur = $utilisateur;
        $dto->numeroVersion = $this->getNumeroVersion($dto->numeroCde, $dto->codeSociete);
        $dto->pieceJoint1 = $nomPdfFusionner;

        return $dto;
    }

    private function getNumeroDit($numDa, $codeSociete)
    {
        $demandeApproRepository = $this->entityManager->getRepository(DemandeAppro::class);
        return $demandeApproRepository->getNumDitDa($numDa, $codeSociete);
    }

    private function getMontantBc(string $numCde, string $codeSociete): float
    {
        $daModel = new DaModel();
        return $daModel->getMontantBcDaDirect($numCde, $codeSociete);
    }

    private function getNumeroVersion(string $numCde, string $codeSociete): int
    {
        $daSoumissionBcRepository = $this->entityManager->getRepository(DaSoumissionBc::class);
        return $this->autoIncrement($daSoumissionBcRepository->getNumeroVersionMax($numCde, $codeSociete));;
    }

    private function autoIncrement(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return (int)$num + 1;
    }
}
