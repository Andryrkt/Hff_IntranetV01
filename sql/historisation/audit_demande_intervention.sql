-- Trace des modifications faites directement en base (moulinette externe, SSMS...) qui échappent au suivi des opérations de l'intranet.
-- Table commune : d'autres tables pourront y écrire avec leur propre trigger.
-- Script relançable : la table n'est créée qu'une fois (son contenu est conservé), le trigger est remplacé.
IF OBJECT_ID('dbo.audit_modification_bdd', 'U') IS NULL
BEGIN
CREATE TABLE audit_modification_bdd
(
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    nom_table VARCHAR(100) NOT NULL,
    id_ligne INT NOT NULL,
    reference VARCHAR(50) NULL,
    -- ex. numéro DIT, pour retrouver la ligne sans jointure
    colonne VARCHAR(100) NOT NULL,
    ancienne_valeur NVARCHAR(255) NULL,
    nouvelle_valeur NVARCHAR(255) NULL,
    date_modification DATETIME2(3) NOT NULL DEFAULT SYSDATETIME(),
    login_sql NVARCHAR(128) NOT NULL DEFAULT SUSER_SNAME(),
    -- poste et programme à l'origine de la requête : distinguent la moulinette de l'intranet
    poste NVARCHAR(128) NULL DEFAULT HOST_NAME(),
    programme NVARCHAR(128) NULL DEFAULT APP_NAME()
);

CREATE INDEX ix_audit_modification_bdd_ligne ON audit_modification_bdd (nom_table, id_ligne, date_modification);
CREATE INDEX ix_audit_modification_bdd_reference ON audit_modification_bdd (reference);
END;
GO

IF OBJECT_ID('dbo.trg_audit_demande_intervention', 'TR') IS NOT NULL
    DROP TRIGGER dbo.trg_audit_demande_intervention;
GO

CREATE TRIGGER trg_audit_demande_intervention
ON demande_intervention
AFTER UPDATE
AS
BEGIN
    -- ne pas renvoyer de "n lignes affectées" en plus : la moulinette pourrait s'appuyer sur ce compte
    SET NOCOUNT ON;
    -- l'audit ne doit jamais annuler l'UPDATE d'origine : sans XACT_ABORT OFF, toute erreur du trigger annulerait la transaction
    SET XACT_ABORT OFF;

    -- table d'audit absente (supprimée, renommée) : on ne trace pas, l'UPDATE passe.
    -- Le TRY/CATCH ne suffirait pas : une table inexistante n'est pas interceptée dans le même bloc.
    IF OBJECT_ID('dbo.audit_modification_bdd', 'U') IS NULL
        RETURN;

    BEGIN TRY
        -- valeurs coupées à 255 (LEFT) : un texte trop long ferait échouer l'INSERT
        IF UPDATE(id_statut_demande)
            INSERT INTO dbo.audit_modification_bdd (nom_table, id_ligne, reference, colonne, ancienne_valeur, nouvelle_valeur)
            SELECT 'demande_intervention', i.id, LEFT(i.numero_demande_dit, 50), 'id_statut_demande',
                CONVERT(NVARCHAR(255), d.id_statut_demande), CONVERT(NVARCHAR(255), i.id_statut_demande)
            FROM inserted i
                JOIN deleted d ON d.id = i.id
            WHERE ISNULL(i.id_statut_demande, -1) <> ISNULL(d.id_statut_demande, -1);

        IF UPDATE(numero_or)
            INSERT INTO dbo.audit_modification_bdd (nom_table, id_ligne, reference, colonne, ancienne_valeur, nouvelle_valeur)
            SELECT 'demande_intervention', i.id, LEFT(i.numero_demande_dit, 50), 'numero_or',
                LEFT(CONVERT(NVARCHAR(MAX), d.numero_or), 255), LEFT(CONVERT(NVARCHAR(MAX), i.numero_or), 255)
            FROM inserted i
                JOIN deleted d ON d.id = i.id
            WHERE ISNULL(CONVERT(NVARCHAR(MAX), i.numero_or), '') <> ISNULL(CONVERT(NVARCHAR(MAX), d.numero_or), '');

        IF UPDATE(statut_or)
            INSERT INTO dbo.audit_modification_bdd (nom_table, id_ligne, reference, colonne, ancienne_valeur, nouvelle_valeur)
            SELECT 'demande_intervention', i.id, LEFT(i.numero_demande_dit, 50), 'statut_or',
                LEFT(CONVERT(NVARCHAR(MAX), d.statut_or), 255), LEFT(CONVERT(NVARCHAR(MAX), i.statut_or), 255)
            FROM inserted i
                JOIN deleted d ON d.id = i.id
            WHERE ISNULL(CONVERT(NVARCHAR(MAX), i.statut_or), '') <> ISNULL(CONVERT(NVARCHAR(MAX), d.statut_or), '');
    END TRY
    BEGIN CATCH
        -- erreur d'audit avalée : la modification d'origine est conservée, seule la trace est perdue.
        -- Si l'erreur a rendu la transaction impossible à valider (cas rare), SQL Server l'annule quand même.
        IF XACT_STATE() = -1
            THROW;
    END CATCH
END;
GO

-- Consultation : historique d'une DIT
-- SELECT * FROM audit_modification_bdd WHERE reference = 'DIT26109299' ORDER BY date_modification;
