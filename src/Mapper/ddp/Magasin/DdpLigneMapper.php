<?php

namespace App\Mapper\ddp\Magasin;

use App\Dto\ddp\DdpDto;
use App\Entity\ddp\DemandePaiementLigne;

class DdpLigneMapper
{
    /**
     * Une ligne par couple commande/facture : une commande liée à plusieurs factures donne plusieurs lignes.
     *
     * @param DdpDto $dto
     */
    public static function map(DdpDto $dto): array
    {
        $lignes = [];
        $numeroLigne = 0;

        foreach (self::couplesCommandeFacture($dto) as [$numCde, $numFac]) {
            $ligne = new DemandePaiementLigne();
            $ligne->setNumeroDdp($dto->numeroDdp)
                ->setNumeroLigne(++$numeroLigne)
                ->setNumeroCommande($numCde)
                ->setNumeroFacture($numFac)
                ->setMontantFacture($dto->montantAPayer())
                ->setNumeroVersion(1)
                ->setRatioMontantPayer($dto->ratioMontantPayer)
                ->setMontantCommandeAPayer($dto->montantCommande($numCde !== '' ? $numCde : null));

            $lignes[] = $ligne;
        }

        return $lignes;
    }

    /**
     * @return array<int, array{0: string, 1: string}> [numeroCommande, numeroFacture]
     */
    private static function couplesCommandeFacture(DdpDto $dto): array
    {
        $commandes = is_array($dto->numeroCommande) ? $dto->numeroCommande : [$dto->numeroCommande];
        if (empty($commandes)) {
            $commandes = [''];
        }

        // factures de chaque commande, selon la requête utilisée dans le formulaire
        $facturesParCommande = [];
        foreach ($dto->lignesFactures as $ligneFacture) {
            $numCde = trim((string) $ligneFacture['Numero_PO']);
            $numFac = trim((string) $ligneFacture['Numero_Facture']);
            $facturesParCommande[$numCde][$numFac] = $numFac;
        }

        $couples = [];
        foreach ($commandes as $i => $numCde) {
            $numCde = (string) $numCde;
            // sans couples connus (ex. avant arrivage) : ancien appariement par position
            $factures = $facturesParCommande[trim($numCde)] ?? [$dto->numeroFacture[$i] ?? '-'];
            foreach ($factures as $numFac) {
                $couples[] = [$numCde, $numFac];
            }
        }

        return $couples;
    }
}
