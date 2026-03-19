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
            $dto->numeroPo = null;
            $dto->urlPo = null;
            $dto->utilisateurCreateurDevis = $item['utilisateur_createur_devis'] ?? '';
            $dto->soumisPar = $item['soumis_par'] ?? '';

            // =====================================
            $dto->devise = $item['devise'] ?? '';
            $dto->constructeur = $item['constructeur'] ?? '';

            // Url 
            // $url = [
            //     "verificationPrix" => $this->getUrlGenerator()->generate('devis_magasin_soumission_verification_prix', ['numeroDevis' => $numeroDevis]),
            //     "validationDevis"  => $this->getUrlGenerator()->generate('devis_magasin_soumission_validation_devis', ['numeroDevis' => $numeroDevis, 'codeAgenceService' => $emetteur]),
            //     "soumissionBC"     => $this->getUrlGenerator()->generate('bc_magasin_soumission', ['numeroDevis' => $numeroDevis]),
            // ];
            // $dto->url = $url;

            // // Blocage
            // $pointageDevis = in_array($dto->statutDw, [DevisMagasin::STATUT_PRIX_VALIDER_TANA, DevisMagasin::STATUT_PRIX_MODIFIER_TANA, DevisMagasin::STATUT_VALIDE_AGENCE]);
            // $relanceClient = $dto->statutDw === DevisMagasin::STATUT_ENVOYER_CLIENT && $dto->statutBc ===  BcMagasin::STATUT_EN_ATTENTE_BC && in_array(PointageRelanceStatutConstant::POINTAGE_RELANCE_A_RELANCER, [$dto->statutRelance1, $dto->statutRelance2, $dto->statutRelance3]) && !$dto->getStopRelance();
            // $dto->pointagedevis = $pointageDevis;
            // $dto->relanceClient = $relanceClient;

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
