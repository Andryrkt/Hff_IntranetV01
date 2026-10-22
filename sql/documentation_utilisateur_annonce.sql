-- ============================================================
-- documentation_utilisateur : date de la dernière mise à jour annoncée
-- aux utilisateurs (bandeau sur l'intranet). NULL = jamais annoncée.
-- DATETIME2(6) comme date_modification : Doctrine envoie les microsecondes.
-- À exécuter après documentation_utilisateur_rubrique_publication.sql. Rejouable.
-- ============================================================
IF COL_LENGTH ('documentation_utilisateur', 'date_annonce') IS NULL
    ALTER TABLE documentation_utilisateur ADD date_annonce DATETIME2 (6) NULL;
ELSE
    -- première version du script : colonne créée en DATETIME
    ALTER TABLE documentation_utilisateur ALTER COLUMN date_annonce DATETIME2 (6) NULL;
GO
