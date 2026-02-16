<?php

namespace App\Factory\da\CdeFrnDto;

use App\Dto\Da\ListeCdeFrn\DaDdpaDto;
use App\Dto\Da\ListeCdeFrn\DaSituationReceptionDto;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDdpaDto;
use App\Entity\da\DaSoumissionFacBl;
use App\Entity\da\DemandeAppro;
use App\Entity\ddp\DemandePaiement;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlDdpaMapper;
use App\Model\da\DaSoumissionFacBlDdpaModel;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Repository\da\DemandeApproRepository;
use App\Service\autres\AutoIncDecService;
use Doctrine\ORM\EntityManagerInterface;

class DaSoumissionFacBlDdpaFactory
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private EntityManagerInterface $em;

    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private DaSoumissionFacBlDdpaModel $daSoumissionFacBlDdpaModel;
    private DemandeApproRepository $demandeApproRepository;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->daSoumissionFacBlRepository = $em->getRepository(DaSoumissionFacBl::class);
        $this->daSoumissionFacBlDdpaModel = new DaSoumissionFacBlDdpaModel();
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
    }

    public function initialisation($numCde, $numDa, $numOR,  string $utilisateur): DaSoumissionFacBlDdpaDto
    {
        $dto = new DaSoumissionFacBlDdpaDto();
        $dto->numeroCde = $numCde;
        $dto->numeroDemandeAppro = $numDa;
        $dto->numeroOR = $numOR;
        $dto->numeroDemandeDit = $this->getNumeroDit($numDa);
        $dto->utilisateur = $utilisateur;
        $dto->numeroVersion = $this->getNumeroVersion($numCde);
        $dto->totalMontantCommande = $this->getTotalMontantCommande($numCde);

        // recuperation des demandes de paiement déjà payer
        $this->getDdpa($numCde, $dto);

        $this->getMontant($numCde, $dto);

        // recupération des informations de commande
        $this->getReception($numCde, $dto);

        return $dto;
    }
    private function getNumeroDit(string $numDa): ?string
    {
        return $this->demandeApproRepository->getNumDitDa($numDa);
    }

    public function enrichissementDtoApresSoumission(DaSoumissionFacBlDdpaDto $dto, $nomPdfFusionner = null)
    {
        if (empty($nomPdfFusionner)) return;

        $dto->pieceJoint1 = $nomPdfFusionner;

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

    public function getReception(int $numCde, $dto)
    {
        $articleCdes = $this->daSoumissionFacBlDdpaModel->getArticleCde($numCde);

        foreach ($articleCdes as $articleCde) {
            $situRecepDto = new DaSituationReceptionDto();
            $dto->receptions[] = DaSoumissionFacBlDdpaMapper::mapReception($situRecepDto, $articleCde);
        }
    }

    public function getDdpa(int $numCde, DaSoumissionFacBlDdpaDto $dto)
    {
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($numCde);

        $runningCumul = 0; // Variable pour maintenir le total cumulé

        foreach ($ddps as  $ddp) {
            // Crée un nouveau DTO pour chaque élément afin d'avoir des objets distincts
            $ddpaDto = new DaDdpaDto();

            // Copie les propriétés nécessaires du DTO initial qui sont communes à tous les éléments
            $ddpaDto->totalMontantCommande = $dto->totalMontantCommande;

            // Mappe l'entité vers le nouveau DTO (le mapper ne s'occupe plus du cumul)
            DaSoumissionFacBlDdpaMapper::mapDdp($ddpaDto, $ddp);

            // Calcule et définit la valeur cumulative ici dans la logique du contrôleur
            $runningCumul += $ddpaDto->ratio;
            $ddpaDto->cumul = $runningCumul;

            $dto->daDdpa[] = $ddpaDto;
        }

        return $dto;
    }

    public function getMontant(int $numCde, DaSoumissionFacBlDdpaDto $dto)
    {
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($numCde);

        $totalMontantPayer = $this->getTotalPayer($ddps);
        $ratioTotalPayer = ($totalMontantPayer / $dto->totalMontantCommande) * 100;
        $montantAregulariser = $dto->totalMontantCommande - $totalMontantPayer;
        $ratioMontantARegul = ($montantAregulariser /  $dto->totalMontantCommande) * 100;

        $dto = DaSoumissionFacBlDdpaMapper::mapTotalPayer($dto, $totalMontantPayer, $ratioTotalPayer, $montantAregulariser, $ratioMontantARegul);

        return $dto;
    }

    private function getTotalPayer(array $ddps): float
    {
        $montantpayer = 0;

        foreach ($ddps as $item) {
            $montantpayer = $montantpayer + $item->getMontantAPayers();
        }

        return $montantpayer;
    }
}
