-- ============================================================
-- Montant du commande à payer saisi par commande lors d'une
-- demande de paiement magasin (après arrivage).
-- Renomme montant_total_commande si le premier script a déjà été
-- exécuté, sinon crée la colonne. Sans effet si elle existe déjà.
-- ============================================================
IF COL_LENGTH ('demande_paiement_ligne', 'montant_total_commande') IS NOT NULL
    EXEC sp_rename 'demande_paiement_ligne.montant_total_commande', 'montant_commande_a_payer', 'COLUMN'
ELSE IF COL_LENGTH ('demande_paiement_ligne', 'montant_commande_a_payer') IS NULL
    ALTER TABLE demande_paiement_ligne ADD montant_commande_a_payer FLOAT NULL;

IF COL_LENGTH ('demande_paiement_commande', 'montant_total_commande') IS NOT NULL
    EXEC sp_rename 'demande_paiement_commande.montant_total_commande', 'montant_commande_a_payer', 'COLUMN'
ELSE IF COL_LENGTH ('demande_paiement_commande', 'montant_commande_a_payer') IS NULL
    ALTER TABLE demande_paiement_commande ADD montant_commande_a_payer FLOAT NULL;

IF COL_LENGTH ('commande_livraison', 'montant_total_commande') IS NOT NULL
    EXEC sp_rename 'commande_livraison.montant_total_commande', 'montant_commande_a_payer', 'COLUMN'
ELSE IF COL_LENGTH ('commande_livraison', 'montant_commande_a_payer') IS NULL
    ALTER TABLE commande_livraison ADD montant_commande_a_payer FLOAT NULL;
