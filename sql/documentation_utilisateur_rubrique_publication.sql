-- ============================================================
-- documentation_utilisateur : rubrique du sommaire, ordre dans la
-- rubrique et statut publié (brouillon = visible des admins seulement).
-- À exécuter après documentation_utilisateur.sql. Rejouable.
-- ============================================================
IF COL_LENGTH ('documentation_utilisateur', 'rubrique') IS NULL
    ALTER TABLE documentation_utilisateur ADD rubrique NVARCHAR(50) NULL;

IF COL_LENGTH ('documentation_utilisateur', 'ordre') IS NULL
    ALTER TABLE documentation_utilisateur ADD ordre INT NOT NULL CONSTRAINT DF_documentation_utilisateur_ordre DEFAULT 0;

IF COL_LENGTH ('documentation_utilisateur', 'publie') IS NULL
    ALTER TABLE documentation_utilisateur ADD publie BIT NOT NULL CONSTRAINT DF_documentation_utilisateur_publie DEFAULT 0;
GO

-- Guide déjà relu : publié, rubrique Compta (menu Compta > Demande de paiement)
UPDATE documentation_utilisateur
SET rubrique = 'Compta', ordre = 1, publie = 1
WHERE slug = 'ddp-magasin' AND rubrique IS NULL;
