<?php

namespace App\Controller\Traits\da;

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
            $keyId = implode('_', array_map('trim', [$dal->getArtConstp(), $dal->getArtRefp(), $dal->getArtDesi(), $dal->getQteDem()]));
            /** @var iterable<DemandeApproLR> $dalrs la liste des DALR dans DAL */
            $dalrs       = $dal->getDemandeApproLR();
            if ($dalrs->isEmpty()) {
                $fournisseur = $dal->getNomFournisseur();
                $prix        = $this->formatPrix($dal->getPrixUnitaire());
                $fournisseurs[$fournisseur][$keyId] = [
                    'prix'  => $prix,
                    'choix' => true,
                ];
            } else {
                foreach ($dalrs as $dalr) {
                    $frnDalr = $dalr->getNomFournisseur();
                    $prix    = $this->formatPrix($dalr->getPrixUnitaire());
                    $fournisseurs[$frnDalr][$keyId] = [
                        'prix'  => $prix,
                        'choix' => $dalr->getChoix(),
                    ];
                }
            }
        }
        return $fournisseurs;
    }

    private function formatPrix($prix): string
    {
        if (is_numeric($prix)) return $prix == 0 ? '' : number_format((float) $prix, 2, ',', ' ');
        return '0,00'; // Retourner un montant par défaut si ce n'est pas un nombre
    }
}
