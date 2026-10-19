-- ============================================================
-- da_notification_facture_bl : ajout de la raison de l'anomalie
-- (pourquoi est_facture_bl_soumis n'a pas été mis à jour sur toutes les lignes da_afficher).
-- À exécuter sur les bases où la table a été créée avant l'ajout de la colonne.
-- Sans effet si la colonne existe déjà.
-- ============================================================
IF COL_LENGTH ('da_notification_facture_bl', 'raison_anomalie') IS NULL
    ALTER TABLE da_notification_facture_bl ADD raison_anomalie VARCHAR(1000) NULL;
