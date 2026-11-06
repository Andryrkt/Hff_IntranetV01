-- ============================================================
-- Demande de paiement magasin (après arrivage) : séparation des montants.
-- montant_commande_receptionner : montant réceptionné saisi par commande.
-- montant_commande_a_payer : 0 si solde avance > 0 et réceptionné <= solde,
-- sinon réceptionné - solde avance.
-- Jusqu'ici montant_commande_a_payer contenait le réceptionné : à la création
-- de la colonne, il y est copié et montant_commande_a_payer est recalculé.
-- À exécuter après ddp_ajout_montant_commande_a_payer.sql et
-- demande_paiement_commande_ajout_solde_avance.sql.
-- Sans effet si la colonne existe déjà.
-- ============================================================
IF COL_LENGTH ('demande_paiement_commande', 'montant_commande_receptionner') IS NULL
BEGIN
    ALTER TABLE demande_paiement_commande ADD montant_commande_receptionner FLOAT NULL;
    EXEC ('UPDATE demande_paiement_commande
        SET montant_commande_receptionner = montant_commande_a_payer,
            montant_commande_a_payer = CASE
                WHEN ISNULL(solde_avance, 0) > 0 AND montant_commande_a_payer <= solde_avance THEN 0
                ELSE montant_commande_a_payer - ISNULL(solde_avance, 0)
            END');
END

IF COL_LENGTH ('demande_paiement_ligne', 'montant_commande_receptionner') IS NULL
BEGIN
    ALTER TABLE demande_paiement_ligne ADD montant_commande_receptionner FLOAT NULL;
    EXEC ('UPDATE l
        SET montant_commande_receptionner = l.montant_commande_a_payer,
            montant_commande_a_payer = CASE
                WHEN ISNULL(s.solde_avance, 0) > 0 AND l.montant_commande_a_payer <= s.solde_avance THEN 0
                ELSE l.montant_commande_a_payer - ISNULL(s.solde_avance, 0)
            END
        FROM demande_paiement_ligne l
        OUTER APPLY (
            SELECT TOP 1 c.solde_avance FROM demande_paiement_commande c
            WHERE c.numero_ddp = l.numero_demande_paiement AND c.numero_commande = l.numero_commande
        ) s');
END

IF COL_LENGTH ('commande_livraison', 'montant_commande_receptionner') IS NULL
BEGIN
    ALTER TABLE commande_livraison ADD montant_commande_receptionner FLOAT NULL;
    EXEC ('UPDATE l
        SET montant_commande_receptionner = l.montant_commande_a_payer,
            montant_commande_a_payer = CASE
                WHEN ISNULL(s.solde_avance, 0) > 0 AND l.montant_commande_a_payer <= s.solde_avance THEN 0
                ELSE l.montant_commande_a_payer - ISNULL(s.solde_avance, 0)
            END
        FROM commande_livraison l
        OUTER APPLY (
            SELECT TOP 1 c.solde_avance FROM demande_paiement_commande c
            WHERE c.demandePaiementId = l.demandePaiementId AND c.numero_commande = l.numero_commande
        ) s');
END
