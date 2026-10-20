-- ============================================================
-- demande_paiement : ajout du montant total du commande
-- (saisi dans le formulaire de demande de paiement à l'avance ;
-- le montant facture proforma est enregistré dans montant_a_payer).
-- Sans effet si la colonne existe déjà.
-- ============================================================
IF COL_LENGTH ('demande_paiement', 'montant_total_commande') IS NULL
    ALTER TABLE demande_paiement ADD montant_total_commande FLOAT NULL;

-- ============================================================
-- demande_paiement : suppression de montant_facture_proforma
-- (remplacée : la proforma est dans montant_a_payer).
-- Sans effet si la colonne n'existe pas.
-- ============================================================
IF COL_LENGTH ('demande_paiement', 'montant_facture_proforma') IS NOT NULL
    ALTER TABLE demande_paiement DROP COLUMN montant_facture_proforma;
