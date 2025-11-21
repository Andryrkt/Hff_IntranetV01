<?php

namespace App\Factory\magasin\bc;

use App\Entity\magasin\bc\BcMagasin;
use App\Entity\admin\utilisateur\User;
use App\Model\magasin\bc\BcMagasinDto;
use App\Service\autres\VersionService;
use Doctrine\ORM\EntityManagerInterface;

class BcMagasinFactory
{

    /**
     * Crée une entité BcMagasin à partir d'un BcMagasinDto.
     *
     * @param BcMagasinDto $dto
     * @param User $user L'utilisateur courant
     * @return BcMagasin
     */
    public function createFromDto(BcMagasinDto $dto, User $user, float $montantDevis = 0.00, int $numeroVersionMax): BcMagasin
    {
        $bcMagasin = new BcMagasin();


        // Convertit un montant formaté (ex: "1 234,56") en float
        $montantBcFloat = (float)str_replace(',', '.', str_replace(' ', '', $dto->montantBc ?? '0'));

        return $bcMagasin->setNumeroDevis($dto->numeroDevis)
            ->setNumeroBc($dto->numeroBc)
            ->setDateBc($dto->dateBc)
            ->setMontantDevis($montantDevis)
            ->setMontantBc($montantBcFloat)
            ->setNumeroVersion(VersionService::autoIncrement($numeroVersionMax))
            ->setStatutBc(BcMagasin::STATUT_SOUMIS_VALIDATION)
            ->setObservation($dto->observation)
            ->setUtilisateur($user->getNomUtilisateur());
    }
}
