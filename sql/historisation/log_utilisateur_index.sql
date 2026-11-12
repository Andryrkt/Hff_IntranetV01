-- Historique des consultations de pages (Administration > Historique > Consultation de pages) :
-- index sur la date pour le tableau de bord et la recherche par période (table de plus de 600 000 lignes, sans index).
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_Log_utilisateur_date' AND object_id = OBJECT_ID('Log_utilisateur'))
    CREATE INDEX IX_Log_utilisateur_date
        ON Log_utilisateur (date_heure_consultation)
        INCLUDE (utilisateur, nom_page, machine_utilisateur);
