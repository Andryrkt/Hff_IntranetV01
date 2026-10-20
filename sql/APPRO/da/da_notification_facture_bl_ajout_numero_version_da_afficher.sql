-- ============================================================
-- da_notification_facture_bl : ajout du numéro de version des lignes da_afficher
-- mises à jour (est_facture_bl_soumis) lors de la soumission facture / BL.
-- À exécuter sur les bases où la table a été créée avant l'ajout de la colonne.
-- Sans effet si la colonne existe déjà.
-- ============================================================
IF COL_LENGTH ('da_notification_facture_bl', 'numero_version_da_afficher') IS NULL
    ALTER TABLE da_notification_facture_bl ADD numero_version_da_afficher INT NULL;
