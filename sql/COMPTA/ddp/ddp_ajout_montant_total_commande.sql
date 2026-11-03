-- ============================================================
-- Montant total de la commande (IPS : frn_cde.fcde_mtn), enregistré
-- par commande lors d'une demande de paiement magasin à l'avance.
-- À exécuter après ddp_ajout_montant_commande_a_payer.sql.
-- Sans effet si la colonne existe déjà.
-- ============================================================
IF COL_LENGTH ('demande_paiement_ligne', 'montant_total_commande') IS NULL
    ALTER TABLE demande_paiement_ligne ADD montant_total_commande FLOAT NULL;

IF COL_LENGTH ('demande_paiement_commande', 'montant_total_commande') IS NULL
    ALTER TABLE demande_paiement_commande ADD montant_total_commande FLOAT NULL;
