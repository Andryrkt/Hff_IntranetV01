-- Trace des modifications de da_afficher (moulinette externe comprise) dans audit_modification_bdd.
-- Prérequis : audit_demande_intervention.sql (création de la table audit_modification_bdd). Script relançable.
IF OBJECT_ID('dbo.trg_audit_da_afficher', 'TR') IS NOT NULL
    DROP TRIGGER dbo.trg_audit_da_afficher;
GO

CREATE TRIGGER trg_audit_da_afficher
ON da_afficher
AFTER UPDATE
AS
BEGIN
    -- mêmes protections que trg_audit_demande_intervention : l'audit ne doit jamais annuler l'UPDATE d'origine
    SET NOCOUNT ON;
    SET XACT_ABORT OFF;

    IF NOT (UPDATE(statut_or) OR UPDATE(statut_cde) OR UPDATE(bc_envoyer_fournisseur)
        OR UPDATE(numero_version_or_maj_statut) OR UPDATE(est_facture_bl_soumis) OR UPDATE(date_maj_statut_or))
        RETURN;

    IF OBJECT_ID('dbo.audit_modification_bdd', 'U') IS NULL
        RETURN;

    BEGIN TRY
        -- une ligne d'audit par colonne réellement modifiée ; date au format 121 (aaaa-mm-jj hh:mi:ss.mmm), booléens en 0 / 1
        INSERT INTO dbo.audit_modification_bdd (nom_table, id_ligne, reference, colonne, ancienne_valeur, nouvelle_valeur)
        SELECT 'da_afficher', i.id, LEFT(i.numero_demande_appro, 50), v.colonne, v.ancienne, v.nouvelle
        FROM inserted i
            JOIN deleted d ON d.id = i.id
            CROSS APPLY (VALUES
                ('statut_or', LEFT(CONVERT(NVARCHAR(MAX), d.statut_or), 255), LEFT(CONVERT(NVARCHAR(MAX), i.statut_or), 255)),
                ('statut_cde', LEFT(CONVERT(NVARCHAR(MAX), d.statut_cde), 255), LEFT(CONVERT(NVARCHAR(MAX), i.statut_cde), 255)),
                ('bc_envoyer_fournisseur', CONVERT(NVARCHAR(255), d.bc_envoyer_fournisseur), CONVERT(NVARCHAR(255), i.bc_envoyer_fournisseur)),
                ('numero_version_or_maj_statut', CONVERT(NVARCHAR(255), d.numero_version_or_maj_statut), CONVERT(NVARCHAR(255), i.numero_version_or_maj_statut)),
                ('est_facture_bl_soumis', CONVERT(NVARCHAR(255), d.est_facture_bl_soumis), CONVERT(NVARCHAR(255), i.est_facture_bl_soumis)),
                ('date_maj_statut_or', CONVERT(NVARCHAR(255), d.date_maj_statut_or, 121), CONVERT(NVARCHAR(255), i.date_maj_statut_or, 121))
            ) v (colonne, ancienne, nouvelle)
        WHERE NOT (v.ancienne = v.nouvelle OR (v.ancienne IS NULL AND v.nouvelle IS NULL));
    END TRY
    BEGIN CATCH
        IF XACT_STATE() = -1
            THROW;
    END CATCH
END;
GO

-- Consultation : historique d'une DA
-- SELECT * FROM audit_modification_bdd WHERE nom_table = 'da_afficher' AND reference = 'DAP26010001' ORDER BY date_modification;
