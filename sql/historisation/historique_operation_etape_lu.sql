-- ============================================================
-- Table : historique_operation_etape (SQL Server)
-- Marquage "lu / non lu" des opérations en problème (résultat autre que OK)
-- depuis la page admin "Suivi des opérations".
-- Le marquage porte sur toutes les étapes d'un même id_suivi.
-- ============================================================
ALTER TABLE historique_operation_etape
ADD est_lu BIT NOT NULL CONSTRAINT DF_historique_operation_etape_est_lu DEFAULT 0,
    lu_par VARCHAR(100) NULL,
    date_lecture DATETIME2 NULL;
