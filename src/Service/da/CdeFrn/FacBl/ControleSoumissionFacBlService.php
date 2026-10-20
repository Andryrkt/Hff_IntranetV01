<?php

namespace App\Service\da\CdeFrn\FacBl;

use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\da\DemandeAppro;
use App\Model\da\DaSoumissionFacBlModel;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * Après chaque soumission de facture / BL, contrôle que est_facture_bl_soumis a bien été mis à jour
 * sur les lignes da_afficher, et enregistre le résultat dans le suivi des opérations
 * (étape "Contrôle mise à jour DA") : OK, ou ALERTE avec la raison de l'anomalie.
 */
class ControleSoumissionFacBlService
{
    public const LIBELLE_ETAPE = 'Contrôle mise à jour DA (facture / BL)';

    private const LIBELLES_TYPE_DA = [
        DemandeAppro::TYPE_DA_AVEC_DIT         => 'DA avec DIT',
        DemandeAppro::TYPE_DA_DIRECT           => 'DA directe',
        DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => 'DA réappro mensuel',
        DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'DA réappro ponctuel',
        DemandeAppro::TYPE_DA_PARENT           => 'DA parent',
    ];

    private DaSoumissionFacBlModel $daSoumissionFacBlModel;

    public function __construct(DaSoumissionFacBlModel $daSoumissionFacBlModel)
    {
        $this->daSoumissionFacBlModel = $daSoumissionFacBlModel;
    }

    /**
     * Enregistre le contrôle dans le suivi. Une erreur est seulement journalisée pour ne pas bloquer la soumission.
     *
     * @param DaSoumissionFacBlDto $dto dto de la soumission (après traitement)
     */
    public function controler(DaSoumissionFacBlDto $dto): void
    {
        try {
            $bilan              = $dto->bilanMajDaAfficher;
            $nbArticlesLivres   = (int) ($bilan['nbArticlesLivres'] ?? 0);
            $nbLignesMaj        = (int) ($bilan['nbLignesMaj'] ?? 0);
            $version            = $bilan['numeroVersionDaAfficher'] ?? '?';
            $articlesNonTrouves = $bilan['articlesNonTrouves'] ?? [];
            $estAnomalie        = $nbLignesMaj === 0 || !empty($articlesNonTrouves);

            $message = $estAnomalie
                ? $this->raisonAnomalieOuErreur($dto, $bilan)
                : "$nbArticlesLivres article(s) livré(s), $nbLignesMaj ligne(s) da_afficher mise(s) à jour (version $version)";

            $detail = implode("\n", array_filter([
                'Type : ' . ($dto->typeDdp && $dto->typeDdp !== 'aucun' ? $dto->typeDdp : 'facbl'),
                'N° DA : ' . $dto->numeroDemandeAppro . ' (version ' . $version . ')',
                'N° CDE : ' . $dto->numeroCde,
                'N° livraison : ' . (is_array($dto->numLiv) ? implode(', ', $dto->numLiv) : (string) $dto->numLiv),
                'N° OR : ' . ($dto->numeroOR ?: '-'),
                'Montant : ' . ($dto->montantBlFacture !== null ? number_format((float) $dto->montantBlFacture, 2, ',', ' ') : '-'),
                "Articles livrés : $nbArticlesLivres · lignes da_afficher mises à jour : $nbLignesMaj",
                empty($articlesNonTrouves) ? null : "Articles non trouvés dans da_afficher :\n" . implode("\n", $articlesNonTrouves),
            ]));

            SuiviOperationService::controle(self::LIBELLE_ETAPE, $estAnomalie, $message, $detail);
        } catch (\Throwable $e) {
            error_log("[ControleSoumissionFacBl] Echec du contrôle de la mise à jour DA : " . $e->getMessage());
        }
    }

    /**
     * Le diagnostic interroge IPS : s'il échoue, le contrôle est quand même enregistré avec l'erreur
     */
    private function raisonAnomalieOuErreur(DaSoumissionFacBlDto $dto, array $bilan): string
    {
        try {
            return $this->determinerRaisonAnomalie($dto, $bilan);
        } catch (\Throwable $e) {
            return "Diagnostic impossible : " . $e->getMessage();
        }
    }

    /**
     * Explique pourquoi est_facture_bl_soumis n'a pas été mis à jour sur toutes les lignes da_afficher
     */
    private function determinerRaisonAnomalie(DaSoumissionFacBlDto $dto, array $bilan): string
    {
        $numLiv      = is_array($dto->numLiv) ? implode(', ', $dto->numLiv) : (string) $dto->numLiv;
        $numCde      = (string) $dto->numeroCde;
        $codeSociete = (string) $dto->codeSociete;
        $version     = $bilan['numeroVersionDaAfficher'] ?? '?';
        $source      = $bilan['sourceArticles'] ?? null;
        $typeDa      = $bilan['typeDa'] ?? null;
        $libelleType = $typeDa !== null ? (self::LIBELLES_TYPE_DA[(int) $typeDa] ?? "type $typeDa") : 'type inconnu';

        // des articles IPS ont été trouvés, mais pas les lignes da_afficher correspondantes
        if (!empty($bilan['articlesNonTrouves'])) {
            return count($bilan['articlesNonTrouves']) . " article(s) livré(s) dans IPS sans ligne correspondante dans da_afficher "
                . "(DA {$dto->numeroDemandeAppro}, version $version, CDE $numCde) : la recherche compare la référence ET la désignation, "
                . "vérifier si la désignation a été modifiée dans la DA.";
        }

        // DDPL : toutes les lignes de la commande sont mises à jour, sans passer par IPS
        if ($source === 'da_afficher') {
            return "Aucune ligne da_afficher pour la DA {$dto->numeroDemandeAppro}, version $version, CDE $numCde, société $codeSociete.";
        }

        if ((int) ($bilan['nbArticlesLivres'] ?? 0) > 0) {
            return "Article(s) livré(s) trouvé(s) dans IPS mais aucune ligne da_afficher mise à jour (DA {$dto->numeroDemandeAppro}, version $version).";
        }

        // aucun article livré retrouvé dans IPS : diagnostic détaillé de la livraison
        $diag = $this->daSoumissionFacBlModel->getDiagnosticLivraison($numLiv, $numCde, $codeSociete);
        $nbLiv        = (int) ($diag['nb_lignes_livraison'] ?? 0);
        $nbLivSociete = (int) ($diag['nb_lignes_livraison_societe'] ?? 0);
        $nbLivCde     = (int) ($diag['nb_lignes_livraison_cde'] ?? 0);
        $nbOr         = (int) ($diag['nb_lignes_or'] ?? 0);
        $nbCde        = (int) ($diag['nb_lignes_cde'] ?? 0);
        $livres       = implode(', ', $diag['articles_livres'] ?? []) ?: 'aucun';
        $or           = implode(', ', $diag['articles_or'] ?? []) ?: 'aucun';
        $prefixe      = "Aucun article livré retrouvé dans IPS ($libelleType, requête $source) : ";

        if ($nbLiv === 0) {
            return $prefixe . "la livraison $numLiv n'a aucune ligne dans frn_llf.";
        }
        if ($nbLivSociete === 0) {
            return $prefixe . "la livraison $numLiv a $nbLiv ligne(s) dans frn_llf mais aucune pour la société $codeSociete.";
        }
        if ($nbLivCde === 0) {
            return $prefixe . "la livraison $numLiv n'est pas rattachée à la commande $numCde dans frn_llf.";
        }
        if ($source === 'sav_lor') {
            // à la réception, IPS remplace slor_numcf (n° de commande) par le n° de livraison
            return $prefixe . "aucune ligne d'OR (sav_lor) n'a slor_numcf = $numLiv : les lignes de la livraison ($livres) "
                . "ne sont pas réceptionnées dans l'OR, ou la DA n'est pas de type $libelleType ($nbCde ligne(s) dans frn_cdl pour la commande $numCde).";
        }

        return $prefixe . "les lignes livrées ($livres) ne correspondent à aucune ligne de commande frn_cdl "
            . "($nbCde ligne(s)) sur n° ligne, référence, constructeur et succursale.";
    }
}
