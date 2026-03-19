<?php

namespace App\Mapper\Magasin\Devis;

use App\Dto\Magasin\Devis\DevisNegDto;


class DevisNegMapper
{
    public function map(array $data): array
    {
        return array_map(function ($item) {
            $dto = new DevisNegDto();
            $dto->statutDw = $item['statut_dw'] ?? '';
            $dto->statutBc = $item['statut_bc'] ?? '';
            $dto->numeroDevis = $item['numero_devis'] ?? '';
            $dto->dateCreation = $item['date_creation'] ?? '';
            $dto->emetteur = $item['emetteur'] ?? '';
            $dto->client = $item['client'] ?? '';
            $dto->referenceClient = $item['reference_client'] ?? '';
            $dto->montantDevis = (float)$item['montant_devis'] ?? 0.00;
            $dto->dateEnvoiDevisAuClient = $item['date_envoye_devis_au_client'] ?? null;
            $dto->positionIps = $item['position_ips'] ?? '';
            $dto->utilisateurCreateurDevis = $item['utilisateur_createur_devis'] ?? '';
            $dto->soumisPar = $item['soumis_par'] ?? '';

            // =====================================
            $dto->devise = $item['devise'] ?? '';
            $dto->constructeur = $item['constructeur'] ?? '';

            // $dto->numeroVersion =  0;
            // $dto->nombreLignes = $item['nombre_lignes'] ?? 0;
            // $dto->typeSoumission = $item['type_soumission'] ?? '';
            // $dto->dateMajStatut = $item['date_maj_statut'] ?? null;
            // $dto->cat = $item['cat'] ?? false;
            // $dto->nonCat = $item['nonCat'] ?? false;
            // $dto->nomFichier = $item['nomFichier'] ?? '';
            // $dto->sommeNumeroLignes = $item['somme_numero_lignes'] ?? 0;
            // $dto->datePointage = $item['date_pointage'] ?? null;
            // $dto->tacheValidateur = $item['tache_validateur'] ?? null;
            // $dto->estValidationPm = $item['est_validation_pm'] ?? false;
            // $dto->relance = $item['relance'] ?? '';
            // $dto->dateBc = $item['date_bc'] ?? null;
            // $dto->observation = $item['observation'] ?? null;

            return $dto;
        }, $data);
    }
}
