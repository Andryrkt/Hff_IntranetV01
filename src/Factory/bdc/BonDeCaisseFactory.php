<?php

namespace App\Factory\bdc;

use App\Dto\bdc\BonDeCaisseDto;
use App\Entity\bdc\BonDeCaisse;

class BonDeCaisseFactory
{
    public function createFromEntity(BonDeCaisse $bonDeCaisse): BonDeCaisseDto
    {
        $dto = new BonDeCaisseDto();

        $dto->id = $bonDeCaisse->getId();
        $dto->typeDemande = $bonDeCaisse->getTypeDemande();
        $dto->numeroDemande = $bonDeCaisse->getNumeroDemande();
        $dto->dateDemande = $bonDeCaisse->getDateDemande();
        $dto->caisseRetrait = $bonDeCaisse->getCaisseRetrait();
        $dto->typePaiement = $bonDeCaisse->getTypePaiement();
        $dto->agenceDebiteur = $bonDeCaisse->getAgenceDebiteur();
        $dto->serviceDebiteur = $bonDeCaisse->getServiceDebiteur();
        $dto->agenceEmetteur = $bonDeCaisse->getAgenceEmetteur();
        $dto->serviceEmetteur = $bonDeCaisse->getServiceEmetteur();
        $dto->retraitLie = $bonDeCaisse->getRetraitLie();
        $dto->matricule = $bonDeCaisse->getMatricule();
        $dto->adresseMailDemandeur = $bonDeCaisse->getAdresseMailDemandeur();
        $dto->motifDemande = $bonDeCaisse->getMotifDemande();
        $dto->montantPayer = $bonDeCaisse->getMontantPayer();
        $dto->devise = $bonDeCaisse->getDevise();
        $dto->statutDemande = $bonDeCaisse->getStatutDemande();
        $dto->dateStatut = $bonDeCaisse->getDateStatut();
        $dto->pdfDemande = $bonDeCaisse->getPdfDemande();
        $dto->nomValidateurFinal = $bonDeCaisse->getNomValidateurFinal();

        return $dto;
    }
    
    public function createFromEntities(iterable $entities): array
    {
        $dtos = [];
        foreach ($entities as $entity) {
            $dtos[] = $this->createFromEntity($entity);
        }
        return $dtos;
    }
}
