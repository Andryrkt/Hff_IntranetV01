-- ============================================================
-- demande_paiement_commande : solde avance de la commande calculé
-- à la soumission d'une demande de paiement magasin après arrivage
-- (0 pour BAP et DDPR). Sans effet si la colonne existe déjà.
-- ============================================================
IF COL_LENGTH ('demande_paiement_commande', 'solde_avance') IS NULL
    ALTER TABLE demande_paiement_commande ADD solde_avance FLOAT NULL;
