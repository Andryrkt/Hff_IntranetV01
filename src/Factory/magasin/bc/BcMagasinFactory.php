<?php

namespace App\Factory\magasin\bc;

use App\Entity\admin\utilisateur\User;
use App\Entity\magasin\bc\BcMagasin;
use App\Model\magasin\bc\BcMagasinDto;

class BcMagasinFactory
{
    /**
     * Crée une entité BcMagasin à partir d'un BcMagasinDto.
     *
     * @param BcMagasinDto $dto
     * @param User $user L'utilisateur courant
     * @return BcMagasin
     */
    public function createFromDto(BcMagasinDto $dto, User $user): BcMagasin
    {
        $bcMagasin = new BcMagasin();

        // Convertit un montant formaté (ex: "1 234,56") en float
        $montantBcFloat = (float)str_replace(',', '.', str_replace(' ', '', $dto->montantBc ?? '0'));

        return $bcMagasin->setNumeroDevis($dto->numeroDevis)
            ->setNumeroBc($dto->numeroBc)
            ->setMontantDevis(0.00)
            ->setMontantBc($montantBcFloat)
            ->setNumeroVersion(1)
            ->setStatutBc('Soumis à validation')
            ->setObservation($dto->observation)
            ->setUtilisateur($user->getNomUtilisateur());
    }
}
