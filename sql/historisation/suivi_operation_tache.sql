-- ============================================================
-- Table : suivi_operation_tache (SQL Server)
-- Tâches de correction issues de la page admin "Suivi des opérations".
-- Toute opération dont le résultat n'est pas OK (ECHEC, INTERROMPU, ALERTE)
-- crée ou met à jour une tâche. Les opérations qui cassent pour la même raison
-- sont regroupées dans une seule tâche (même signature : type d'opération +
-- étape + message sans les numéros, chemins ni valeurs saisies).
-- (alimentée par SuiviOperationService via TacheSuiviOperation)
--
-- statut : A_TRIER  -> nouvelle tâche, effort et priorité pas encore choisis
--          A_FAIRE  -> triée, en attente de traitement
--          EN_COURS -> correction en cours
--          RESOLU   -> corrigée (repasse en A_FAIRE + est_regression si le problème revient)
--          IGNORE   -> pas un bug (erreur de saisie, cas normal...) : le compteur continue sans réouverture
--
-- effort : RAPIDE (moins de 30 min) / MOYEN (une demi-journée à une journée) / LONG (plusieurs jours)
-- priorite : HAUTE / NORMALE / BASSE (HAUTE à la création pour un ECHEC, NORMALE sinon)
-- ============================================================
CREATE TABLE
    suivi_operation_tache (
        id INT IDENTITY (1, 1) NOT NULL,
        signature CHAR(40) NOT NULL,
        type_operation VARCHAR(50) NOT NULL,
        etape VARCHAR(100) NOT NULL,
        statut_operation VARCHAR(20) NOT NULL,
        message_type NVARCHAR(500) NULL,
        dernier_message NVARCHAR(1000) NULL,
        nb_occurrences INT NOT NULL CONSTRAINT DF_suivi_operation_tache_nb DEFAULT 1,
        premiere_occurrence DATETIME2 NOT NULL,
        derniere_occurrence DATETIME2 NOT NULL,
        dernier_id_suivi VARCHAR(36) NOT NULL,
        dernier_numero_document VARCHAR(50) NULL,
        statut VARCHAR(20) NOT NULL CONSTRAINT DF_suivi_operation_tache_statut DEFAULT 'A_TRIER',
        effort VARCHAR(10) NULL,
        priorite VARCHAR(10) NOT NULL CONSTRAINT DF_suivi_operation_tache_priorite DEFAULT 'NORMALE',
        est_regression BIT NOT NULL CONSTRAINT DF_suivi_operation_tache_regression DEFAULT 0,
        assigne_a VARCHAR(100) NULL,
        echeance DATE NULL,
        cause NVARCHAR(MAX) NULL,
        correction NVARCHAR(500) NULL,
        date_resolution DATETIME2 NULL,
        resolu_par VARCHAR(100) NULL,
        date_modification DATETIME2 NULL,
        modifie_par VARCHAR(100) NULL,
        CONSTRAINT PK_suivi_operation_tache PRIMARY KEY (id)
    );

CREATE UNIQUE INDEX UX_suivi_operation_tache_signature ON suivi_operation_tache (signature);

CREATE INDEX IDX_suivi_operation_tache_statut ON suivi_operation_tache (statut, priorite, derniere_occurrence);
