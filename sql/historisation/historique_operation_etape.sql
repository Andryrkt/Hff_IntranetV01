-- ============================================================
-- Table : historique_operation_etape (SQL Server)
-- Suivi étape par étape des soumissions et enregistrements (DIT, OR, RI,
-- facture, devis, DOM, BADM, mutation, casier, DA, DDP, magasin, POL, BL,
-- DDD, tickets...). Une ligne par étape, toutes les étapes d'une même
-- opération partagent le même id_suivi.
-- (alimentée par SuiviOperationService)
--
-- statut : EN_COURS   -> étape commencée mais jamais terminée (arrêt brutal du script)
--          SUCCES     -> étape terminée sans erreur
--          ECHEC      -> exception, copie DocuWare échouée ou message de blocage
--          INTERROMPU -> le script s'est arrêté (redirection / exit) pendant l'étape
-- ============================================================
CREATE TABLE
    historique_operation_etape (
        id INT IDENTITY (1, 1) NOT NULL,
        id_suivi VARCHAR(36) NOT NULL,
        id_historique_operation INT NULL,
        type_operation VARCHAR(50) NOT NULL,
        numero_document VARCHAR(50) NULL,
        ordre INT NOT NULL,
        etape VARCHAR(100) NOT NULL,
        statut VARCHAR(20) NOT NULL,
        message NVARCHAR(1000) NULL,
        detail_erreur NVARCHAR(MAX) NULL,
        date_debut DATETIME2 NOT NULL,
        date_fin DATETIME2 NULL,
        duree_ms INT NULL,
        utilisateur VARCHAR(100) NULL,
        url VARCHAR(500) NULL,
        CONSTRAINT PK_historique_operation_etape PRIMARY KEY (id)
    );

CREATE INDEX IDX_historique_operation_etape_suivi ON historique_operation_etape (id_suivi, ordre);

CREATE INDEX IDX_historique_operation_etape_date ON historique_operation_etape (date_debut);

CREATE INDEX IDX_historique_operation_etape_numero ON historique_operation_etape (numero_document);

CREATE INDEX IDX_historique_operation_etape_historique ON historique_operation_etape (id_historique_operation);
