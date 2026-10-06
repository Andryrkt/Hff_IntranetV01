-- pour voir le detail de la table
SELECT *
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_NAME = '<table>';

-- rendre une colonne en contraint UNIQUE
ALTER TABLE <
table >
ADD CONSTRAINT UQ_numero_demande_dit UNIQUE (< colonne >);

-- Ajouter une colonne à une table
ALTER TABLE <
table >
ADD < nouveau_colonne > < type >
--copier une table dans une base de donné à une autre /// executé la requête dans le base de donnée ou l'on crée le nouveau table
SELECT * INTO dbo.demande_intervention_migration
FROM HFF_INTRANET_TEST.dbo.demande_intervention_migration;

# duplique une tabel dans sqlServer

-- Étape 1 : Dupliquer la structure sans données
SELECT \* INTO NouvelleTable FROM AncienneTable WHERE 1 = 0;

-- Étape 2 : Copier les données
INSERT INTO NouvelleTable SELECT \* FROM AncienneTable;

--change le nom dans la base de donée
-- Renommez la colonne dans la table
EXEC sp_rename 'historique_operation_ditors.votreAncienneNomColonne',
'nouveaunomColonne',
'COLUMN';

-- supprimer un clé etrangère
SELECT CONSTRAINT_NAME
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE
    TABLE_NAME = '<nom_du_table>'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY';

ALTER TABLE < nom_du_table > DROP CONSTRAINT < nom_constraint >;



-- Supprimer la table si elle existe
IF OBJECT_ID('[HFF_INTRANET_MAQUETTE].[dbo].[Personnel]', 'U') IS NOT NULL
    DROP TABLE [HFF_INTRANET_MAQUETTE].[dbo].[Personnel];

-- Créer la table manuellement (exemple de structure)
CREATE TABLE [HFF_INTRANET_MAQUETTE].[dbo].[Personnel] (
    id int IDENTITY(1,1) NOT NULL,
    Matricule varchar(4) COLLATE French_CI_AS NOT NULL,
    Nom varchar(200) COLLATE French_CI_AS NULL,
    Code_AgenceService_Sage varchar(4) COLLATE French_CI_AS NOT NULL,
    Numero_Compte_Bancaire varchar(26) COLLATE French_CI_AS NULL,
    Prenoms varchar(100) COLLATE French_CI_AS NULL,
    Qualification varchar(10) COLLATE French_CI_AS NULL,
    agence_service_irium_id int NULL,
    societe varchar(10) COLLATE French_CI_AS NULL,
    group_direction bit DEFAULT 0
);

-- Copier les données avec IDENTITY_INSERT
SET IDENTITY_INSERT [HFF_INTRANET_MAQUETTE].[dbo].[Personnel] ON;

INSERT INTO [HFF_INTRANET_MAQUETTE].[dbo].[Personnel] (id, Matricule, Nom, Code_AgenceService_Sage, Numero_Compte_Bancaire , Prenoms , Qualification , agence_service_irium_id , societe, group_direction )
SELECT id, Matricule, Nom, Code_AgenceService_Sage, Numero_Compte_Bancaire , Prenoms , Qualification , agence_service_irium_id , societe, group_direction 
FROM [HFF_INTRANET].[dbo].[Personnel];

SET IDENTITY_INSERT [HFF_INTRANET_MAQUETTE].[dbo].[Personnel] OFF;
--- transfert des donner d'une base à autre