<?php

namespace App\Controller\Traits\da;

use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;

trait PrixFournisseurTrait
{
    /**
     * Gérer la liste des fournisseurs et prix correspondant à partir des DAL avec clé unique (cst_ref_designation_qteDem)
     * 
     * @param iterable<DemandeApproL> $dals la liste des DAL à afficher
     * 
     * @return array le tableau de fournisseurs avec prix
     */
    private function gererPrixFournisseurs(iterable $dals): array
    {
        $fournisseurs = [];
        foreach ($dals as $dal) {
            $cst   = $dal->getArtConstp();
            $ref   = $dal->getArtRefp();
            $desi  = $dal->getArtDesi();
            $qte   = $dal->getQteDem();
            $keyId = implode('_', array_map('trim', [$cst, $ref, $desi, $qte]));
            /** @var iterable<DemandeApproLR> $dalrs la liste des DALR dans DAL */
            $dalrs       = $dal->getDemandeApproLR();
            if ($dalrs->isEmpty()) {
                $fournisseur = $dal->getNomFournisseur();
                $prix        = $dal->getPrixUnitaire() ? $this->formatPrix($dal->getPrixUnitaire()) : "-";
                $montant     = $prix === "-" ? 0 : $dal->getPrixUnitaire() * $qte;
                $fournisseurs[$fournisseur][$keyId] = [
                    'prix'    => $prix,
                    'montant' => $montant,
                    'choix'   => true,
                ];
            } else {
                foreach ($dalrs as $dalr) {
                    $frnDalr = $dalr->getNomFournisseur();
                    $prix    = $dalr->getPrixUnitaire() ? $this->formatPrix($dalr->getPrixUnitaire()) : "-";
                    $montant = $prix === "-" ? 0 : $dalr->getPrixUnitaire() * $qte;
                    $choix   = $dalr->getChoix();

                    if ($choix || !isset($fournisseurs[$frnDalr][$keyId])) {
                        $fournisseurs[$frnDalr][$keyId] = [
                            'prix'    => $prix,
                            'montant' => $montant,
                            'choix'   => $choix,
                        ];
                    }
                }
            }
        }
        return $fournisseurs;
    }

    private function formatPrix(string $prix): string
    {
        if (is_numeric($prix)) return $prix == 0 ? '' : number_format((float) $prix, 2, ',', ' ');
        return '0,00'; // Retourner un montant par défaut si ce n'est pas un nombre
    }
}
