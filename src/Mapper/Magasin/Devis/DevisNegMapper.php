<?php

namespace App\Mapper\Magasin\Devis;

use App\Constants\Magasin\Devis\StatutDevisNegContant;
use App\Dto\Magasin\Devis\DevisNegDto;


class DevisNegMapper
{
    public function map(array $data, ?callable $urlGenerator = null, $dwBcClientNegoceRepository = null): array
    {
        return array_map(function ($item) use ($urlGenerator, $dwBcClientNegoceRepository) {
            $dto = new DevisNegDto();
            $dto->statutDw = $item['statut_dw'] ?? StatutDevisNegContant::A_TRAITER;
            $dto->statutBc = $item['statut_bc'] ?? '';
            $dto->numeroDevis = $item['numero_devis'] ?? '';
            $dto->dateCreation = $item['date_creation'] ?? '';
            $dto->emetteur = $item['emetteur'] ?? '';
            $dto->client = $item['client'] ?? '';
            $dto->referenceClient = $item['reference_client'] ?? '';
            $dto->montantDevis = (float)($item['montant_devis'] ?? 0.00);
            $dto->dateEnvoiDevisAuClient = $item['date_envoye_devis_au_client'] ?? null;
            $dto->stopProgressionGlobal = (int)($item['stop_progression_global'] ?? 0);
            $dto->motifStopGlobal = $item['motif_stop_global'] ?? null;
            $dto->positionIps = $item['position_ips'] ?? '';
            if ($dwBcClientNegoceRepository) {
                $bccInfo = $dwBcClientNegoceRepository->findLastValidatedBcc($dto->numeroDevis) ?? null;

                if ($bccInfo) {
                    $dto->numeroPo = $bccInfo['numeroBccNeg'];
                    $dto->urlPo = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $bccInfo['path'];
                } else {
                    $dto->numeroPo = '';
                    $dto->urlPo = '';
                }
            }
            $dto->statutRelance1 = $item['statut_relance_1'] ?? null;
            $dto->statutRelance2 = $item['statut_relance_2'] ?? null;
            $dto->statutRelance3 = $item['statut_relance_3'] ?? null;

            $dto->utilisateurCreateurDevis = $item['utilisateur_createur_devis'] ?? '';
            $dto->soumisPar = $item['soumis_par'] ?? '';
            $dto->devise = $item['devise'] ?? '';
            $dto->constructeur = $item['constructeur'] ?? '';

            // Ajout des styles pour le rendu JS
            $dto->styleStatutDw = $dto->styleStatutDw();
            $dto->styleStatutBc = $dto->styleStatutBc();
            $dto->styleStatutPR1 = $dto->styleStatutPR1();
            $dto->styleStatutPR2 = $dto->styleStatutPR2();
            $dto->styleStatutPR3 = $dto->styleStatutPR3();

            // Génération des URLs si un générateur est fourni
            if ($urlGenerator) {
                $dto->url = $urlGenerator($dto);
            }

            //Blockage de soumission
            // $dto->pointagedevis = in_array($dto->statutDw, [StatutDevisNegContant::PRIX_VALIDER_TANA, StatutDevisNegContant::PRIX_MODIFIER_TANA, StatutDevisNegContant::VALIDE_AGENCE]);

            return $dto;
        }, $data);
    }
}
