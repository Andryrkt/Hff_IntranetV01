<?php

namespace App\Mapper\Magasin\Devis;

use App\Dto\Magasin\Devis\DevisNegDto;


class DevisNegMapper
{
    public function map(array $data): array
    {
        return array_map(function ($item) {
            $dto = new DevisNegDto();
            $dto->numeroDevis = $item['numero_devis'] ?? '';
            $dto->numeroVersion = $item['numero_version'] ?? 0;
            $dto->statutDw = $item['statut_dw'] ?? '';
            $dto->nombreLignes = $item['nombre_lignes'] ?? 0;
            $dto->montantDevis = $item['montant_devis'] ?? 0.00;
            $dto->devise = $item['devise'] ?? '';
            $dto->typeSoumission = $item['type_soumission'] ?? '';
            $dto->dateMajStatut = $item['date_maj_statut'] ?? null;
            $dto->utilisateur = $item['utilisateur'] ?? '';
            $dto->cat = $item['cat'] ?? false;
            $dto->nonCat = $item['nonCat'] ?? false;
            $dto->nomFichier = $item['nomFichier'] ?? '';
            $dto->dateEnvoiDevisAuClient = $item['date_envoi_devis_au_client'] ?? null;
            $dto->sommeNumeroLignes = $item['somme_numero_lignes'] ?? 0;
            $dto->datePointage = $item['date_pointage'] ?? null;
            $dto->tacheValidateur = $item['tache_validateur'] ?? null;
            $dto->estValidationPm = $item['est_validation_pm'] ?? false;
            $dto->statutBc = $item['statut_bc'] ?? '';
            $dto->relance = $item['relance'] ?? '';
            $dto->dateBc = $item['date_bc'] ?? null;
            $dto->observation = $item['observation'] ?? null;
            // Mappez les autres propriétés de manière similaire
            return $dto;
        }, $data);
    }
}
