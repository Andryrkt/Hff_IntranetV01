<?php

namespace App\Controller\Traits\magasin\devis;

use App\Service\autres\VersionService;
use App\Entity\magasin\devis\DevisMagasin;

trait DevisMagasinTrait
{

    /**
     * Récupère les informations du devis dans IPS
     * 
     * @param string $numeroDevis Le numéro de devis
     * @return array Les informations du devis
     */
    public function getInfoDevisIps(string $numeroDevis): array
    {
        $devisIps = $this->listeDevisMagasinModel->getInfoDev($numeroDevis);

        if (empty($devisIps)) {
            //message d'erreur
            $message = "Aucune information trouvé dans IPS pour le devis numero : " . $numeroDevis;
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
        }

        return reset($devisIps);
    }

    /**
     * Récupère les nouveaux nombres de lignes et le nouveau montant total du devis
     * 
     * @param array $firstDevisIps Les informations du devis
     * @return array [$newSumOfLines, $newSumOfMontant]
     */
    public function newSumOfLinesAndAmount(array $firstDevisIps): array
    {
        $newSumOfLines = (int)$firstDevisIps['somme_numero_lignes'];
        $newSumOfMontant = (float)$firstDevisIps['montant_total'];
        return [$newSumOfLines, $newSumOfMontant];
    }

    
    private function ajoutInfoIpsDansDevisMagasin(DevisMagasin $devisMagasin, array $firstDevisIps, string $numeroVersion, string $nomFichier, string $typeSoumission): void
    {
        $suffixConstructeur = $this->listeDevisMagasinModel->constructeurPieceMagasin($devisMagasin->getNumeroDevis());

        $devisMagasin
            ->setNumeroDevis($devisMagasin->getNumeroDevis())
            ->setMontantDevis($firstDevisIps['montant_total'])
            ->setDevise($firstDevisIps['devise'])
            ->setSommeNumeroLignes($firstDevisIps['somme_numero_lignes'])
            ->setUtilisateur($this->getUser()->getNomUtilisateur())
            ->setNumeroVersion(VersionService::autoIncrement($numeroVersion))
            ->setStatutDw($typeSoumission == 'VP' ? DevisMagasin::STATUT_PRIX_A_CONFIRMER : DevisMagasin::STATUT_A_VALIDER_CHEF_AGENCE)
            ->setTypeSoumission($typeSoumission)
            ->setCat($suffixConstructeur === 'C' || $suffixConstructeur === 'CP' ? true : false)
            ->setNonCat($suffixConstructeur === 'P' || $suffixConstructeur === 'CP' ? true : false)
            ->setNomFichier((string)$nomFichier)
        ;
    }
}
