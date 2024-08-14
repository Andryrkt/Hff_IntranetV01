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