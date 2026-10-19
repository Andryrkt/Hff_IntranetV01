-- ============================================================
-- demande_paiement : ajout du montant de la facture proforma
-- (saisi dans le formulaire de demande de paiement magasin).
-- Sans effet si la colonne existe déjà.
-- ============================================================
IF COL_LENGTH ('demande_paiement', 'montant_facture_proforma') IS NULL
    ALTER TABLE demande_paiement ADD montant_facture_proforma FLOAT NULL;
