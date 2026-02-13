<?php

namespace App\Factory\da\CdeFrnDto;


use App\Entity\da\DaSoumissionFacBl;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\autres\AutoIncDecService;
use App\Model\da\DaSoumissionFacBlDdpaModel;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDdpaDto;

class DaSoumissionFacBlDdpaFactory
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private EntityManagerInterface $em;

    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private DaSoumissionFacBlDdpaModel $daSoumissionFacBlDdpaModel;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->daSoumissionFacBlRepository = $em->getRepository(DaSoumissionFacBl::class);
        $this->daSoumissionFacBlDdpaModel = new DaSoumissionFacBlDdpaModel();
    }
    public function initialisation($numCde, string $utilisateur): DaSoumissionFacBlDdpaDto
    {
        $dto = new DaSoumissionFacBlDdpaDto();
        $dto->numeroCde = $numCde;
        $dto->utilisateur = $utilisateur;
        $dto->numeroVersion = $this->getNumeroVersion($numCde);
        $dto->totalMontantCommande = $this->getTotalMontantCommande($numCde);

        return $dto;
    }

    private function getNumeroVersion($numCde): int
    {
        $numeroVersionMax = $this->daSoumissionFacBlRepository->getNumeroVersionMax($numCde);

        return AutoIncDecService::autoIncrement($numeroVersionMax);
    }

    private function getTotalMontantCommande($numCde): float
    {
        $totalMontantCommande = $this->daSoumissionFacBlDdpaModel->getTotalMontantCommande($numCde);
        if ($totalMontantCommande) return (float)$totalMontantCommande[0];

        return 0;
    }
}
